<?php

namespace App\Http\Controllers;

use App\Models\Quiz_Catalog;
use App\Models\Quiz_Options;
use App\Models\Quiz_Student_Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionnaireController extends Controller
{
    public function index()
    {
        $quizzes = Quiz_Catalog::withCount('questions')
            ->where('is_active', true)
            ->get();

        return view('quiz.index', compact('quizzes'));
    }

    public function show($id)
    {
        $quiz = Quiz_Catalog::with('questions.options')
            ->where('is_active', true)
            ->findOrFail($id);

        return view('quiz.show', compact('quiz'));
    }

    public function store(Request $request, $id)
    {
        $quiz = Quiz_Catalog::with('questions.options')->findOrFail($id);

        $rules = [];
        foreach ($quiz->questions as $question) {
            $rules["answers.{$question->id}"] = 'required|exists:quiz_options,id';
        }

        $validated = $request->validate($rules, [
            'answers.*.required' => 'Please answer all questions before submitting.',
        ]);

        $selectedOptionIds = array_values($validated['answers']);
        $totalScore = Quiz_Options::whereIn('id', $selectedOptionIds)->sum('points');

        $maxScore = 0;
        foreach ($quiz->questions as $question) {
            $maxScore += $question->options->max('points') ?? 3;
        }

        $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

        if ($percentage <= 33) {
            $distressLevel = 'Low';
        } elseif ($percentage <= 66) {
            $distressLevel = 'Moderate';
        } else {
            $distressLevel = 'High';
        }

        $result = Quiz_Student_Result::create([
            'user_id'        => Auth::id() ?? 1,
            'quiz_id'        => $quiz->id,
            'total_score'    => $totalScore,
            'distress_level' => $distressLevel,
            'ai_summary'     => null,
        ]);

        return redirect()->route('quiz.result', $result->id);
    }

    public function result($id)
    {
        $userId = Auth::id() ?? 1;

        $result = Quiz_Student_Result::with('quiz.questions.options')->findOrFail($id);

        if ($result->user_id !== $userId) {
            abort(403, 'Unauthorized access to this wellbeing result.');
        }

        $maxScore = 0;
        foreach ($result->quiz->questions as $question) {
            $maxScore += $question->options->max('points') ?? 3;
        }

        return view('quiz.result', compact('result', 'maxScore'));
    }

    public function history()
    {
        $userId = Auth::id() ?? 1;

        $pastResults = Quiz_Student_Result::with('quiz')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return view('quiz.history', compact('pastResults'));
    }

}