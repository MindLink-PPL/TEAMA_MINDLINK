<?php

namespace Database\Seeders;

use App\Models\Quiz_Catalog;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Distress Tolerance Scale (DTS) - 4 Questions
        $dtsQuiz = Quiz_Catalog::firstOrCreate(
            ['slug' => 'distress-tolerance-scale'],
            [
                'title'       => 'Distress Tolerance Scale (DTS)',
                'description' => 'A standardized assessment evaluating your ability to experience and withstand negative psychological stress.',
                'is_active'   => true,
            ]
        );

        if ($dtsQuiz->questions()->count() === 0) {
            $dtsQuestions = [
                'Feeling distressed or upset is unbearable to me.',
                'When I feel overwhelmed by academic pressure, I cannot function normally.',
                'I can tolerate being distressed or upset as well as most people.',
                'There is nothing worse than feeling completely out of control emotionally.',
            ];

            $dtsOptions = [
                ['option_text' => 'Strongly Disagree', 'points' => 0],
                ['option_text' => 'Disagree',          'points' => 1],
                ['option_text' => 'Agree',             'points' => 2],
                ['option_text' => 'Strongly Agree',    'points' => 3],
            ];

            foreach ($dtsQuestions as $index => $questionText) {
                $question = $dtsQuiz->questions()->create([
                    'question_text' => $questionText,
                    'order'         => $index + 1,
                ]);

                foreach ($dtsOptions as $opt) {
                    $question->options()->create($opt);
                }
            }
        }

        // 2. Persistent Depressive Disorder (PDD) - 5 Questions
        $pddQuiz = Quiz_Catalog::firstOrCreate(
            ['slug' => 'persistent-depressive-disorder'],
            [
                'title'       => 'Persistent Depressive Disorder (PDD)',
                'description' => 'Self check-in assessment to evaluate persistent depressive symptoms and low energy over time.',
                'is_active'   => true,
            ]
        );

        if ($pddQuiz->questions()->count() === 0) {
            $pddQuestions = [
                'How often have you experienced low energy or fatigue this week?',
                'How often have you felt little interest or pleasure in doing things?',
                'How often have you had trouble concentrating on your coursework?',
                'How often have you felt down, depressed, or hopeless?',
                'How often have you experienced poor appetite or overeating?',
            ];

            $pddOptions = [
                ['option_text' => 'Never',         'points' => 0],
                ['option_text' => 'Sometimes',     'points' => 1],
                ['option_text' => 'Often',         'points' => 2],
                ['option_text' => 'Almost Always', 'points' => 3],
            ];

            foreach ($pddQuestions as $index => $questionText) {
                $question = $pddQuiz->questions()->create([
                    'question_text' => $questionText,
                    'order'         => $index + 1,
                ]);

                foreach ($pddOptions as $opt) {
                    $question->options()->create($opt);
                }
            }
        }
    }
}