<?php

namespace Tests\Browser\Sprint1\WellbeingCheckIn;

use App\Models\Quiz_Catalog;
use App\Models\Quiz_Student_Result;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AiBasedSummaryNegativeTest extends DuskTestCase
{
    private function ensureDtsQuizExists(): Quiz_Catalog
    {
        $this->assertNotEquals(
            ':memory:',
            DB::connection()->getDatabaseName(),
            'Dusk is connected to an in-memory SQLite DB instead of MySQL.'
        );

        $quiz = Quiz_Catalog::with('questions.options')
            ->where(function ($q) {
                $q->where('title', 'like', '%DTS%')
                  ->orWhere('title', 'like', '%Distress Tolerance%');
            })
            ->first();

        if (! $quiz) {
            $quiz = new Quiz_Catalog();
            $quiz->forceFill([
                'title'       => 'Distress Tolerance Scale (DTS)',
                'slug'        => 'dts-' . Str::lower(Str::random(5)),
                'description' => 'Measures a student ability to experience and withstand negative emotional states.',
                'is_active'   => true,
            ])->save();
        } else {
            $quiz->forceFill(['is_active' => true])->save();
        }

        if ($quiz->questions()->count() === 0) {
            for ($i = 1; $i <= 4; $i++) {
                $question = $quiz->questions()->create([
                    'question_text' => "DTS Question {$i}: Feeling distressed or upset is unbearable to me.",
                    'order'         => $i,
                ]);

                foreach ([
                    ['option_text' => 'Strongly Disagree', 'points' => 0],
                    ['option_text' => 'Mildly Disagree', 'points' => 1],
                    ['option_text' => 'Mildly Agree', 'points' => 2],
                    ['option_text' => 'Strongly Agree', 'points' => 3],
                ] as $opt) {
                    $question->options()->create($opt);
                }
            }
        }

        return $quiz->fresh('questions.options');
    }

    /**
     * TC.AI_based_summary.002 (PBI #16 - Negative)
     */
    public function test_ai_summary_does_not_change_when_result_page_is_reloaded(): void
    {
        $quiz = $this->ensureDtsQuizExists();
        $student = User::find(1) ?? User::where('email', 'student@gmail.com')->firstOrFail();
        $initialSummary = null;
        $summaryAfterReload = null;

        $this->browse(function (Browser $browser) use ($quiz, $student, &$initialSummary, &$summaryAfterReload) {
            $browser->loginAs($student)
                ->visit('/wellbeing')
                ->assertPathIs('/wellbeing');

            $browser->script("document.getElementById('openCatalogBtn')?.click();");
            $browser->pause(500)
                ->assertSee($quiz->title);

            $browser->visit("/wellbeing/{$quiz->id}")
                ->assertPathIs("/wellbeing/{$quiz->id}")
                ->assertSee($quiz->title);

            $browser->script("document.getElementById('startQuizBtn')?.click();");
            $browser->pause(500);

            foreach ($quiz->questions as $question) {
                $option = $question->options->last();
                $browser->script("
                    const radio = document.querySelector('input[name=\"answers[{$question->id}]\"][value=\"{$option->id}\"]');
                    if (radio) {
                        radio.checked = true;
                        radio.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                ");
            }

            $browser->script("
                if (typeof isSubmitting !== 'undefined') { isSubmitting = true; }
                const form = document.getElementById('quizForm') || document.querySelector('form:not([action*=\"logout\"])');
                if (form) { form.submit(); }
            ");
            $browser->pause(3000);

            $result = Quiz_Student_Result::where('quiz_id', $quiz->id)->latest('id')->first();
            $initialSummary = $result->ai_summary;
            $snippet = Str::limit(trim($initialSummary), 35, '');

            $browser->assertPathIs('/wellbeing/results/' . $result->id)
                ->assertSee($snippet);

            $browser->refresh()
                ->pause(1000)
                ->assertPathIs('/wellbeing/results/' . $result->id)
                ->assertSee($snippet)
                ->screenshot('TC_AI_based_summary_002_negative');

            $summaryAfterReload = $result->fresh()->ai_summary;
        });

        $this->assertNotEmpty($initialSummary, 'Initial AI summary must be generated upon completion.');
        $this->assertSame($initialSummary, $summaryAfterReload, 'AI summary must remain identical after reloading the result page.');
    }
}