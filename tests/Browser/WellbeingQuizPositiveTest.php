<?php

namespace Tests\Browser;

use App\Models\Quiz_Catalog;
use App\Models\Quiz_Student_Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class WellbeingQuizPositiveTest extends DuskTestCase
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
     * Student navigates from /wellbeing -> PDD quiz -> completes all questions -> views result.
     */
    public function test_student_can_complete_pdd_quiz_and_reach_result_page(): void
    {
        $quiz = $this->ensurePddQuizExists();
        $beforeCount = Quiz_Student_Result::where('quiz_id', $quiz->id)->count();

        $this->browse(function (Browser $browser) use ($quiz) {
            // Step 1: User is on the Wellbeing self check-in homepage
            $browser->visit('/wellbeing')
                ->assertPathIs('/wellbeing')
                ->assertSee('Wellbeing self check-in');

            // Step 2: Open the PDD quiz detail page directly (bypassing JS tabs/modals)
            $browser->visit("/wellbeing/{$quiz->id}")
                ->assertPathIs("/wellbeing/{$quiz->id}")
                ->assertSee($quiz->title);

            // Click "Start The Test ->" if an intro screen is present
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

            // Step 3: Go through and answer all questions of the PDD quiz
            foreach ($quiz->questions as $question) {
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

            // Step 4: Submit the quiz to view the Test/Result page
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

            // Step 5: Observe and verify the newly completed quiz session on /wellbeing/results/{id}
            $latestResult = Quiz_Student_Result::where('quiz_id', $quiz->id)->latest('id')->first();

            $browser->assertPathIs('/wellbeing/results/' . $latestResult->id)
                ->assertSee($quiz->title)
                ->assertSee(Str::limit(trim($latestResult->ai_summary), 35, ''))
                ->screenshot('TC_wellbeing_quiz_001_positive');
        });

        $afterCount = Quiz_Student_Result::where('quiz_id', $quiz->id)->count();
        $latestResult = Quiz_Student_Result::where('quiz_id', $quiz->id)->latest('id')->first();

        // Explicit PHPUnit assertions to prevent "Risky" test warning
        $this->assertNotNull($latestResult, 'Expected a new PDD quiz result record to be stored.');
        $this->assertGreaterThan($beforeCount, $afterCount, 'Result count should increase by 1 after submission.');
        $this->assertEquals($quiz->id, $latestResult->quiz_id);
    }
}