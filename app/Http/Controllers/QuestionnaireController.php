<?php

namespace App\Http\Controllers;

use App\Models\Quiz_Catalog;
use App\Models\Quiz_Options;
use App\Models\Quiz_Student_Result;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionnaireController extends Controller
{
    protected GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

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
        $selectedOptions = Quiz_Options::whereIn('id', $selectedOptionIds)->get()->keyBy('id');
        $totalScore = $selectedOptions->sum('points');

        $maxScore = 0;
        $answeredBreakdown = [];

        foreach ($quiz->questions as $index => $question) {
            $maxScore += $question->options->max('points') ?? 3;
            $chosenOptionId = $validated['answers'][$question->id] ?? null;
            $chosenOption = $selectedOptions->get($chosenOptionId);

            if ($chosenOption) {
                $answeredBreakdown[] = ($index + 1) . ". {$question->question_text} -> Answered: \"{$chosenOption->option_text}\" ({$chosenOption->points} pts)";
            }
        }

        $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

        if ($percentage <= 33) {
            $distressLevel = 'Low';
        } elseif ($percentage <= 66) {
            $distressLevel = 'Moderate';
        } else {
            $distressLevel = 'High';
        }

        $aiSummary = $this->geminiService->generateSummary(
            $quiz->title,
            $totalScore,
            $maxScore,
            $distressLevel,
            $answeredBreakdown
        );

        $result = Quiz_Student_Result::create([
            'user_id'        => Auth::id() ?? 1,
            'quiz_id'        => $quiz->id,
            'total_score'    => $totalScore,
            'distress_level' => $distressLevel,
            'ai_summary'     => $aiSummary,
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

        // Only generate once if this legacy row had no ai_summary stored yet
        if (empty($result->ai_summary)) {
            $generated = $this->geminiService->generateSummary(
                $result->quiz->title,
                $result->total_score,
                $maxScore,
                $result->distress_level
            );

            $result->ai_summary = $generated;
            $result->save();
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