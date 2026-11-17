<?php

namespace Tests\Browser;

use App\Models\Quiz_Catalog;
use App\Models\Quiz_Student_Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AiBasedSummaryPositiveTest extends DuskTestCase
{
    /**
     * Ensure an active DTS quiz exists in MySQL.
     */
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

        if (!$quiz) {
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
     * TC.AI_based_summary.001 (PBI #16 - Positive)
     * Student completes the DTS test and observes the AI paragraph summary on /wellbeing/results/{id}.
     */
    public function test_student_receives_ai_paragraph_summary_upon_completing_dts_quiz(): void
    {
        $quiz = $this->ensureDtsQuizExists();

        $this->browse(function (Browser $browser) use ($quiz) {
            // Step 1: User is on the Wellbeing self check-in homepage
            $browser->visit('/wellbeing')
                ->assertPathIs('/wellbeing');

            // Step 2: View DTS test details directly and proceed with the DTS test
            $browser->visit("/wellbeing/{$quiz->id}")
                ->assertPathIs("/wellbeing/{$quiz->id}")
                ->assertSee($quiz->title);

            $browser->script("
                if (document.querySelectorAll('input[type=\"radio\"]').length === 0) {
                    const startBtn = Array.from(document.querySelectorAll('a[href], button, [onclick]'))
                        .reverse()
                        .find(el => /start the test|start|begin|take|proceed|mulai/i.test(el.textContent.replace(/\\s+/g, ' ')));
                    if (startBtn) {
                        const clickable = startBtn.closest('a, button, [onclick]') || startBtn;
                        clickable.click();
                    }
                }
            ");
            $browser->pause(1200);

            // Step 3: Student goes through all questions of the DTS test
            foreach ($quiz->questions as $question) {
                $option = $question->options->skip(1)->first() ?? $question->options->first();
                $browser->script("
                    const radio = document.querySelector('input[name=\"answers[{$question->id}]\"][value=\"{$option->id}\"]');
                    if (radio) {
                        radio.checked = true;
                        radio.dispatchEvent(new Event('change', { bubbles: true }));
                        if (radio.closest('label')) {
                            radio.closest('label').click();
                        }
                    }
                ");
            }

            $browser->script("
                const form = document.querySelector('form:not([action*=\"logout\"])');
                const submitBtn = form ? form.querySelector('button[type=\"submit\"], input[type=\"submit\"]') : null;
                if (submitBtn) {
                    submitBtn.click();
                } else if (form) {
                    form.submit();
                }
            ");
            $browser->pause(3000);

            // Step 4: Observe and verify that the AI summary paragraph is present on /wellbeing/results/{id}
            $latestResult = Quiz_Student_Result::where('quiz_id', $quiz->id)->latest('id')->first();

            $browser->assertPathIs('/wellbeing/results/' . $latestResult->id)
                ->assertSee('AI Summary:')
                ->assertSee(Str::limit(trim($latestResult->ai_summary), 35, ''))
                ->screenshot('TC_AI_based_summary_001_positive');
        });

        $latestResult = Quiz_Student_Result::where('quiz_id', $quiz->id)->latest('id')->first();

        // Explicit PHPUnit assertions to prevent "Risky" test warning
        $this->assertNotNull($latestResult, 'DTS quiz result should exist in the database.');
        $this->assertNotEmpty($latestResult->ai_summary, 'AI summary paragraph must not be empty.');
        $this->assertGreaterThan(20, strlen(trim($latestResult->ai_summary)), 'AI summary should be a meaningful paragraph.');
    }
}