<?php

namespace Tests\Browser;

use App\Models\Quiz_Catalog;
use App\Models\Quiz_Student_Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ViewQuizHistoryPositiveTest extends DuskTestCase
{
    /**
     * Ensure at least one completed quiz result exists for the student (user_id = 1).
     */
    private function ensureCompletedSessionExists(): Quiz_Student_Result
    {
        $this->assertNotEquals(
            ':memory:',
            DB::connection()->getDatabaseName(),
            'Dusk is connected to an in-memory SQLite DB instead of MySQL.'
        );

        $quiz = Quiz_Catalog::where('is_active', true)->first();

        if (!$quiz) {
            $quiz = new Quiz_Catalog();
            $quiz->forceFill([
                'title'       => 'Distress Tolerance Scale (DTS)',
                'slug'        => 'dts-' . Str::lower(Str::random(5)),
                'description' => 'Measures a student ability to withstand emotional distress.',
                'is_active'   => true,
            ])->save();
        }

        $existing = Quiz_Student_Result::with('quiz')
            ->where('user_id', 1)
            ->latest('id')
            ->first();

        if ($existing && $existing->quiz && !empty($existing->ai_summary)) {
            return $existing;
        }

        return Quiz_Student_Result::create([
            'user_id'        => 1,
            'quiz_id'        => $quiz->id,
            'total_score'    => 6,
            'max_score'      => 12,
            'distress_level' => 'Moderate',
            'ai_summary'     => 'Your assessment indicates a moderate level of stress. Consider taking structured breaks and reaching out to campus counseling if needed.',
        ]);
    }

    /**
     * TC.view_quiz_history.001 (PBI #17 - Positive)
     * Student navigates from /wellbeing -> /wellbeing/history -> opens /wellbeing/results/{id}.
     */
    public function test_student_can_view_completed_quiz_list_and_open_latest_result_detail(): void
    {
        $latestSession = $this->ensureCompletedSessionExists();

        $this->browse(function (Browser $browser) use ($latestSession) {
            // Pre-condition: User starts at the Wellbeing self check-in homepage
            $browser->visit('/wellbeing')
                ->assertPathIs('/wellbeing')
                ->assertSee('View your completed quiz/test');

            // Step 1: Go to the history page of completed quizzes
            $browser->visit('/wellbeing/history')
                ->assertPathIs('/wellbeing/history')
                ->assertSee($latestSession->quiz->title);

            // Step 2: Open the detail of the latest completed quiz session (/wellbeing/results/{id})
            $resultUrl = "/wellbeing/results/{$latestSession->id}";
            $browser->visit($resultUrl)
                ->assertPathIs($resultUrl);

            // Step 3: Observe the score, quiz title, and AI summary of the completed quiz session
            $browser->assertSee($latestSession->quiz->title)
                ->assertSee("{$latestSession->total_score} points")
                ->assertSee(Str::limit(trim($latestSession->ai_summary), 35, ''))
                ->screenshot('TC_view_quiz_history_001_positive');
        });

        // Explicit PHPUnit assertions to prevent "Risky" test warning
        $this->assertNotNull($latestSession->id);
        $this->assertEquals(1, $latestSession->user_id);
        $this->assertNotEmpty($latestSession->ai_summary);
    }
}