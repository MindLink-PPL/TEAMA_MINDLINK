<?php

namespace App\Http\Controllers;

use App\Models\Quiz_Catalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminQuestionnaireController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $quizzes = Quiz_Catalog::with('questions.options')
            ->withCount('questions')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->latest()
            ->get();

        return view('admin.quiz.index', compact('quizzes', 'search'));
    }

    public function create()
    {
        return view('admin.quiz.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'                             => 'required|string|max:255',
            'description'                       => 'required|string',
            'is_active'                         => 'nullable|boolean',
            'questions'                         => 'required|array|min:1',
            'questions.*.question_text'         => 'required|string',
            'questions.*.options'               => 'required|array|min:2',
            'questions.*.options.*.option_text' => 'required|string',
            'questions.*.options.*.points'      => 'required|integer|min:0',
        ]);

        $quiz = DB::transaction(function () use ($validated, $request) {
            $quiz = Quiz_Catalog::create([
                'title'       => $validated['title'],
                'slug'        => $this->generateUniqueSlug($validated['title']),
                'description' => $validated['description'],
                'is_active'   => $request->boolean('is_active'),
            ]);

            foreach (array_values($validated['questions']) as $index => $qData) {
                $question = $quiz->questions()->create([
                    'question_text' => $qData['question_text'],
                    'order'         => $index + 1,
                ]);

                foreach ($qData['options'] as $optData) {
                    $question->options()->create([
                        'option_text' => $optData['option_text'],
                        'points'      => (int) $optData['points'],
                    ]);
                }
            }

            return $quiz;
        });

        return redirect()
            ->route('admin.quiz.show', $quiz->id)
            ->with('success', 'Wellbeing questionnaire created successfully.');
    }

    public function show($id)
    {
        $quiz = Quiz_Catalog::with('questions.options')->findOrFail($id);

        $maxScore = 0;
        foreach ($quiz->questions as $question) {
            $maxScore += $question->options->max('points') ?? 0;
        }

        return view('admin.quiz.show', compact('quiz', 'maxScore'));
    }

    public function edit($id)
    {
        $quiz = Quiz_Catalog::with('questions.options')->findOrFail($id);

        return view('admin.quiz.edit', compact('quiz'));
    }

    public function update(Request $request, $id)
    {
        $quiz = Quiz_Catalog::with('questions.options')->findOrFail($id);

        $validated = $request->validate([
            'title'                             => 'required|string|max:255',
            'description'                       => 'required|string',
            'is_active'                         => 'nullable|boolean',
            'questions'                         => 'required|array|min:1',
            'questions.*.question_text'         => 'required|string',
            'questions.*.options'               => 'required|array|min:2',
            'questions.*.options.*.option_text' => 'required|string',
            'questions.*.options.*.points'      => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($quiz, $validated, $request) {
            $quiz->update([
                'title'       => $validated['title'],
                'slug'        => $this->generateUniqueSlug($validated['title'], $quiz->id),
                'description' => $validated['description'],
                'is_active'   => $request->boolean('is_active'),
            ]);

            foreach ($quiz->questions as $oldQuestion) {
                $oldQuestion->options()->delete();
            }
            $quiz->questions()->delete();

            foreach (array_values($validated['questions']) as $index => $qData) {
                $question = $quiz->questions()->create([
                    'question_text' => $qData['question_text'],
                    'order'         => $index + 1,
                ]);

                foreach ($qData['options'] as $optData) {
                    $question->options()->create([
                        'option_text' => $optData['option_text'],
                        'points'      => (int) $optData['points'],
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.quiz.show', $quiz->id)
            ->with('success', 'Wellbeing questionnaire and scoring updated successfully.');
    }

    public function destroy($id)
    {
        $quiz = Quiz_Catalog::with('questions.options')->findOrFail($id);

        DB::transaction(function () use ($quiz) {
            foreach ($quiz->questions as $question) {
                $question->options()->delete();
            }
            $quiz->questions()->delete();
            $quiz->delete();
        });

        return redirect()
            ->route('admin.quiz.index')
            ->with('success', 'Wellbeing questionnaire deleted successfully.');
    }

    /**
     * Generate a unique URL-friendly slug from the quiz title.
     */
    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        if ($baseSlug === '') {
            $baseSlug = 'quiz';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            Quiz_Catalog::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}