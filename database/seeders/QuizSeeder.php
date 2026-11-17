<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        
        $quizId = DB::table('quiz_catalog')->insertGetId([
            'title' => 'Distress Tolerance Scale (DTS)',
            'slug' => 'distress-tolerance-scale',
            'description' => 'A standardized assessment evaluating your ability to experience and withstand negative psychological stress.',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

       
        $questions = [
            'Feeling distressed or upset is unbearable to me.',
            'When I feel overwhelmed by academic pressure, I cannot function normally.',
            'I can tolerate being distressed or upset as well as most people.',
            'There is nothing worse than feeling completely out of control emotionally.',
        ];

        
        $optionScale = [
            ['text' => 'Strongly Disagree', 'points' => 0],
            ['text' => 'Disagree',          'points' => 1],
            ['text' => 'Agree',             'points' => 2],
            ['text' => 'Strongly Agree',    'points' => 3],
        ];

       
        foreach ($questions as $index => $questionText) {
            $questionId = DB::table('quiz_questions')->insertGetId([
                'quiz_id' => $quizId,
                'question_text' => $questionText,
                'order' => $index + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($optionScale as $option) {
                DB::table('quiz_options')->insert([
                    'question_id' => $questionId,
                    'option_text' => $option['text'],
                    'points' => $option['points'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}