<?php

namespace Tests\Browser;

use App\Models\Quiz_Catalog;
use App\Models\Quiz_Student_Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class WellbeingQuizNegativeTest extends DuskTestCase
{
    /**
     * Ensure an active PDD quiz with at least 5 questions exists in MySQL.
     */
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

        if (!$quiz) {
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
                    'question_text' => "PDD Assessment Question {$i}: How often have you felt hopeless this week?",
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
     * TC.wellbeing_quiz.002 (PBI #15 - Negative)
     * Student skips questions 4 and 5 in the PDD quiz and attempts to brute-force submit.
     */
    public function test_student_cannot_submit_pdd_quiz_when_skipping_questions_four_and_five(): void
    {
        $quiz = $this->ensurePddQuizExists();
        $beforeCount = Quiz_Student_Result::where('quiz_id', $quiz->id)->count();
        $finalUrl = '';

        $this->browse(function (Browser $browser) use ($quiz, &$finalUrl) {
            // Step 1: User is on the Wellbeing self check-in homepage
            $browser->visit('/wellbeing')
                ->assertPathIs('/wellbeing')
                ->assertSee('Wellbeing self check-in');

            // Step 2: Open the PDD test/quiz detail page directly (bypassing JS tabs/modals)
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
            $browser->pause(1200)
                ->assertSee($quiz->title);

            // Step 3: Answer only the first 3 questions, intentionally skipping Question 4 and Question 5
            $questionsToAnswer = $quiz->questions->take(3);
            foreach ($questionsToAnswer as $question) {
                $firstOption = $question->options->first();
                $browser->script("
                    const radio = document.querySelector('input[name=\"answers[{$question->id}]\"][value=\"{$firstOption->id}\"]');
                    if (radio) {
                        radio.checked = true;
                        radio.dispatchEvent(new Event('change', { bubbles: true }));
                        if (radio.closest('label')) {
                            radio.closest('label').click();
                        }
                    }
                ");
            }

            // Brute-force bypass any HTML5 'required' attributes to test backend validation protection
            $browser->script("
                const form = document.querySelector('form:not([action*=\"logout\"])');
                if (form) {
                    form.querySelectorAll('[required]').forEach(el => el.removeAttribute('required'));
                    form.setAttribute('novalidate', 'novalidate');
                    const submitBtn = form.querySelector('button[type=\"submit\"], input[type=\"submit\"]');
                    if (submitBtn) {
                        submitBtn.click();
                    } else {
                        form.submit();
                    }
                }
            ");
            $browser->pause(1200);

            $finalUrl = $browser->driver->getCurrentURL();

            // Verify user is blocked from reaching the result page and sees validation message
            $browser->assertSee('Please answer all questions before submitting.')
                ->screenshot('TC_wellbeing_quiz_002_negative');
        });

        $afterCount = Quiz_Student_Result::where('quiz_id', $quiz->id)->count();

        // Explicit PHPUnit assertions to prevent "Risky" test warning
        $this->assertStringNotContainsString('/wellbeing/results/', $finalUrl, 'Student must not reach the results page when skipping questions.');
        $this->assertSame($beforeCount, $afterCount, 'No quiz result should be created when questions 4 and 5 are skipped.');
        $this->assertTrue($quiz->questions->count() >= 5, 'PDD quiz must have at least 5 questions for this test case.');
    }
}