<?php

namespace Tests\Browser;

use App\Models\Quiz_Catalog;
use App\Models\Quiz_Student_Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ViewQuizHistoryNegativeTest extends DuskTestCase
{
    /**
     * Ensure an active DTS quiz and a prior completed student result exist before Admin edits the quiz.
     */
    private function prepareDtsQuizAndHistoricalResult(): array
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
                $q = $quiz->questions()->create([
                    'question_text' => "DTS Question {$i}: Feeling distressed or upset is unbearable to me.",
                    'order'         => $i,
                ]);
                foreach ([
                    ['option_text' => 'Strongly Disagree', 'points' => 0],
                    ['option_text' => 'Mildly Disagree', 'points' => 1],
                    ['option_text' => 'Mildly Agree', 'points' => 2],
                    ['option_text' => 'Strongly Agree', 'points' => 3],
                ] as $opt) {
                    $q->options()->create($opt);
                }
            }
        }

        $quiz = $quiz->fresh('questions.options');

        $originalMaxScore = 0;
        foreach ($quiz->questions as $question) {
            $originalMaxScore += $question->options->max('points') ?? 3;
        }

        $historicalResult = Quiz_Student_Result::create([
            'user_id'        => 1,
            'quiz_id'        => $quiz->id,
            'total_score'    => 9,
            'max_score'      => $originalMaxScore,
            'distress_level' => 'High',
            'ai_summary'     => 'Historical DTS AI Summary: Your score of 9 out of ' . $originalMaxScore . ' reflects high distress at the time of taking this test.',
        ]);

        return [$quiz, $historicalResult, $originalMaxScore];
    }

    /**
     * TC.view_quiz_history.002 (PBI #17 - Negative)
     * Verify that when Admin adds a new question with 4 options to DTS, a student's historical DTS result
     * in /wellbeing/results/{id} preserves its original total_score, max_score, distress_level, and ai_summary.
     */
    public function test_historical_dts_score_and_summary_do_not_change_after_admin_adds_new_question(): void
    {
        [$quiz, $historicalResult, $originalMaxScore] = $this->prepareDtsQuizAndHistoricalResult();

        $originalQuestionSnapshot = $quiz->questions->map(function ($q) {
            return [
                'question_text' => $q->question_text,
                'order'         => $q->order,
                'options'       => $q->options->map(fn ($o) => [
                    'option_text' => $o->option_text,
                    'points'      => $o->points,
                ])->toArray(),
            ];
        })->toArray();

        $originalScore   = $historicalResult->total_score;
        $originalSummary = $historicalResult->ai_summary;
        $originalLevel   = $historicalResult->distress_level;

        try {
            $this->browse(function (Browser $browser) use ($quiz, $historicalResult, $originalScore, $originalMaxScore, $originalSummary) {
                // Step 1: Go to /admin/wellbeing to enter the Admin Quiz CRUD dashboard
                $browser->visit('/admin/wellbeing')
                    ->assertPathIs('/admin/wellbeing')
                    ->assertSee($quiz->title);

                // Step 2: Choose the DTS test via the detail page and open Edit
                $browser->visit("/admin/wellbeing/{$quiz->id}")
                    ->assertPathIs("/admin/wellbeing/{$quiz->id}")
                    ->assertSee($quiz->title);

                $browser->visit("/admin/wellbeing/{$quiz->id}/edit")
                    ->assertPathIs("/admin/wellbeing/{$quiz->id}/edit")
                    ->assertSee($quiz->title);

                // Step 3: Add 1 additional question with 4 options dynamically into the Admin Edit form
                $newIndex = $quiz->questions->count() + 50;
                $newOrder = $quiz->questions->count() + 1;
                $browser->script("
                    const form = document.querySelector('form[action*=\"/admin/wellbeing/{$quiz->id}\"]')
                        || document.querySelector('form:not([action*=\"logout\"])');
                    if (form) {
                        const wrapper = document.createElement('div');
                        wrapper.innerHTML = `
                            <input type=\"hidden\" name=\"questions[{$newIndex}][question_text]\" value=\"Added Admin Question: Do you feel overwhelmed by sudden changes?\">
                            <input type=\"hidden\" name=\"questions[{$newIndex}][order]\" value=\"{$newOrder}\">
                            <input type=\"hidden\" name=\"questions[{$newIndex}][options][0][option_text]\" value=\"Never\">
                            <input type=\"hidden\" name=\"questions[{$newIndex}][options][0][points]\" value=\"0\">
                            <input type=\"hidden\" name=\"questions[{$newIndex}][options][1][option_text]\" value=\"Rarely\">
                            <input type=\"hidden\" name=\"questions[{$newIndex}][options][1][points]\" value=\"1\">
                            <input type=\"hidden\" name=\"questions[{$newIndex}][options][2][option_text]\" value=\"Often\">
                            <input type=\"hidden\" name=\"questions[{$newIndex}][options][2][points]\" value=\"2\">
                            <input type=\"hidden\" name=\"questions[{$newIndex}][options][3][option_text]\" value=\"Always\">
                            <input type=\"hidden\" name=\"questions[{$newIndex}][options][3][points]\" value=\"3\">
                        `;
                        form.appendChild(wrapper);
                    }
                ");

                // Step 4: Submit the updated DTS quiz
                $browser->script("
                    const form = document.querySelector('form[action*=\"/admin/wellbeing/{$quiz->id}\"]')
                        || document.querySelector('form:not([action*=\"logout\"])');
                    if (form) {
                        const submitBtn = form.querySelector('button[type=\"submit\"], input[type=\"submit\"]');
                        if (submitBtn) {
                            submitBtn.click();
                        } else {
                            form.submit();
                        }
                    }
                ");
                $browser->pause(1500);

                // Verify the Admin update succeeded
                $browser->assertPathIs("/admin/wellbeing/{$quiz->id}")
                    ->assertSee('Added Admin Question: Do you feel overwhelmed by sudden changes?');

                // Step 5: Switch to student view and go to /wellbeing/history
                $browser->visit('/wellbeing/history')
                    ->assertPathIs('/wellbeing/history')
                    ->assertSee($quiz->title);

                // Step 6: Open the previously completed DTS quiz session (/wellbeing/results/{id})
                $browser->visit("/wellbeing/results/{$historicalResult->id}")
                    ->assertPathIs("/wellbeing/results/{$historicalResult->id}")
                    ->assertSee($quiz->title);

                // Step 7: Observe that historical score, max_score, and AI summary did NOT change
                $browser->assertSee("{$originalScore} points")
                    ->assertSee("Max {$originalMaxScore} points")
                    ->assertSee(Str::limit(trim($originalSummary), 35, ''))
                    ->screenshot('TC_view_quiz_history_002_negative');
            });

            $updatedQuizCount = $quiz->fresh()->questions()->count();
            $freshHistoricalResult = $historicalResult->fresh();

            // Explicit PHPUnit assertions to prevent "Risky" test warning
            $this->assertEquals(count($originalQuestionSnapshot) + 1, $updatedQuizCount, 'DTS quiz should have 1 additional question after Admin update.');
            $this->assertSame($originalScore, $freshHistoricalResult->total_score, 'Historical total_score must not change.');
            $this->assertSame($originalMaxScore, $freshHistoricalResult->max_score, 'Historical max_score snapshot must remain locked at original value.');
            $this->assertSame($originalLevel, $freshHistoricalResult->distress_level, 'Historical distress_level must not change.');
            $this->assertSame($originalSummary, $freshHistoricalResult->ai_summary, 'Historical AI summary must not change.');
        } finally {
            // Restore original DTS questions so repeated Dusk runs stay clean
            foreach ($quiz->fresh()->questions as $q) {
                $q->options()->delete();
            }
            $quiz->questions()->delete();

            foreach ($originalQuestionSnapshot as $qData) {
                $restoredQ = $quiz->questions()->create([
                    'question_text' => $qData['question_text'],
                    'order'         => $qData['order'],
                ]);
                foreach ($qData['options'] as $optData) {
                    $restoredQ->options()->create($optData);
                }
            }
        }
    }
}