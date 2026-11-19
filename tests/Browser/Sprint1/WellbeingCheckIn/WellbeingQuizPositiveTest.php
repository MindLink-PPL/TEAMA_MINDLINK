<?php

namespace Tests\Browser\Sprint1\WellbeingCheckIn;

use App\Models\Quiz_Catalog;
use App\Models\Quiz_Student_Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class WellbeingQuizPositiveTest extends DuskTestCase
{
    private function ensurePddQuizExists(): Quiz_Catalog
    {
        $this->assertNotEquals(
            ':memory:',
            DB::connection()->getDatabaseName(),
            'Dusk is connected to an in-memory SQLite DB instead of MySQL.'
        );

        $quiz = Quiz_Catalog::with('questions.options')
            ->where('title', 'like', '%PDD%')
            ->first();

        if (! $quiz) {
            $quiz = new Quiz_Catalog();
            $quiz->forceFill([
                'title'       => 'Persistent Depressive Disorder (PDD)',
                'slug'        => 'pdd-' . Str::lower(Str::random(5)),
                'description' => 'Self check-in assessment to evaluate persistent depressive symptoms over time.',
                'is_active'   => true,
            ])->save();
        } else {
            $quiz->forceFill(['is_active' => true])->save();
        }

        if ($quiz->questions()->count() < 5) {
            for ($i = $quiz->questions()->count() + 1; $i <= 5; $i++) {
                $question = $quiz->questions()->create([
                    'question_text' => "PDD Assessment Question {$i}: How often have you experienced low energy this week?",
                    'order'         => $i,
                ]);

                foreach ([
                    ['option_text' => 'Never', 'points' => 0],
                    ['option_text' => 'Sometimes', 'points' => 1],
                    ['option_text' => 'Often', 'points' => 2],
                    ['option_text' => 'Almost Always', 'points' => 3],
                ] as $opt) {
                    $question->options()->create($opt);
                }
            }
        }

        return $quiz->fresh('questions.options');
    }

    /**
     * TC.wellbeing_quiz.001 (PBI #15 - Positive)
     */
    public function test_student_can_complete_pdd_quiz_and_reach_result_page(): void
    {
        $quiz = $this->ensurePddQuizExists();
        $beforeCount = Quiz_Student_Result::where('quiz_id', $quiz->id)->count();

        $this->browse(function (Browser $browser) use ($quiz) {
            // Step 1: Open /wellbeing and click #openCatalogBtn to view the Question Catalog
            $browser->visit('/wellbeing')
                ->assertPathIs('/wellbeing')
                ->assertSee('Wellbeing self check-in');

            $browser->script("document.getElementById('openCatalogBtn')?.click();");
            $browser->pause(500)
                ->assertSee($quiz->title);

            // Step 2: Open the PDD quiz detail page and click #startQuizBtn ("Start The Test ->")
            $browser->visit("/wellbeing/{$quiz->id}")
                ->assertPathIs("/wellbeing/{$quiz->id}")
                ->assertSee($quiz->title);

            $browser->script("document.getElementById('startQuizBtn')?.click();");
            $browser->pause(500);

            // Step 3: Answer all questions of the PDD quiz
            foreach ($quiz->questions as $question) {
                $firstOption = $question->options->first();
                $browser->script("
                    const radio = document.querySelector('input[name=\"answers[{$question->id}]\"][value=\"{$firstOption->id}\"]');
                    if (radio) {
                        radio.checked = true;
                        radio.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                ");
            }

            // Step 4: Submit the quiz form cleanly (setting isSubmitting = true to bypass beforeunload)
            $browser->script("
                if (typeof isSubmitting !== 'undefined') { isSubmitting = true; }
                const form = document.getElementById('quizForm') || document.querySelector('form:not([action*=\"logout\"])');
                if (form) { form.submit(); }
            ");
            $browser->pause(3000);

            // Step 5: Verify the newly completed quiz session on /wellbeing/results/{id}
            $latestResult = Quiz_Student_Result::where('quiz_id', $quiz->id)->latest('id')->first();

            $browser->assertPathIs('/wellbeing/results/' . $latestResult->id)
                ->assertSee($quiz->title)
                ->assertSee(Str::limit(trim($latestResult->ai_summary), 35, ''))
                ->screenshot('TC_wellbeing_quiz_001_positive');
        });

        $afterCount = Quiz_Student_Result::where('quiz_id', $quiz->id)->count();
        $latestResult = Quiz_Student_Result::where('quiz_id', $quiz->id)->latest('id')->first();

        $this->assertNotNull($latestResult, 'Expected a new PDD quiz result record to be stored.');
        $this->assertGreaterThan($beforeCount, $afterCount, 'Result count should increase by 1 after submission.');
        $this->assertEquals($quiz->id, $latestResult->quiz_id);
    }
}