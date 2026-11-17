<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    /**
     * Call Google Gemini REST API to generate a 2-paragraph personalized wellbeing summary.
     */
    public function generateSummary(
        string $quizTitle,
        int $totalScore,
        int $maxScore,
        string $distressLevel,
        array $answeredBreakdown = []
    ): string {
        $fallbackSummary = "Based on your responses to the {$quizTitle}, your score of {$totalScore} out of {$maxScore} points indicates a " . strtolower($distressLevel) . " level of emotional distress. Taking time to pace your academic workload, practicing daily grounding routines, and reaching out to campus counselling services when needed can help maintain healthy emotional balance.";

        $apiKey = trim((string) (config('services.gemini.key') ?: env('GEMINI_API_KEY')));
        $preferredModel = trim((string) (config('services.gemini.model') ?: env('GEMINI_MODEL', '')));

        if (empty($apiKey)) {
            Log::warning('GeminiService: GEMINI_API_KEY is empty.');
            return $fallbackSummary;
        }

        $answersContext = !empty($answeredBreakdown)
            ? "\nStudent's exact responses:\n" . implode("\n", $answeredBreakdown)
            : '';

        $prompt = "You are an empathetic university wellbeing advisor on the MindLink platform. "
            . "A student just completed the '{$quizTitle}' assessment and scored {$totalScore} out of {$maxScore} points "
            . "(classified as a {$distressLevel} level of stress)."
            . $answersContext . "\n\n"
            . "Write a warm, personalized, and complete 2-paragraph reflection directly to the student ('you'). "
            . "Requirements:\n"
            . "1. Paragraph 1 (3 to 4 full sentences): Reflect on what their specific score ({$totalScore}/{$maxScore}) and response patterns suggest about how they are currently handling academic or emotional pressure.\n"
            . "2. Paragraph 2 (3 to 4 full sentences): Offer practical, student-friendly coping strategies tailored to a {$distressLevel} stress level, and gently mention booking a MindLink campus consultation if they would like extra support.\n"
            . "3. Separate the two paragraphs with a blank line. Do NOT write a single-sentence summary, and do NOT use markdown, asterisks, bullet points, or headings.";

        $modelsToTry = [];
        if (!empty($preferredModel)) {
            $modelsToTry[] = $preferredModel;
        }

        $discoveredModels = $this->discoverAvailableFlashModels($apiKey);
        foreach ($discoveredModels as $discovered) {
            if (!in_array($discovered, $modelsToTry, true)) {
                $modelsToTry[] = $discovered;
            }
        }

        foreach ($modelsToTry as $model) {
            try {
                $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

                $response = Http::withoutVerifying()
                    ->timeout(20)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                    ])
                    ->post($endpoint, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                ],
                            ],
                        ],
                        'generationConfig' => [
                            'temperature'     => 0.8,
                            'maxOutputTokens' => 2048,
                        ],
                    ]);

                if ($response->successful()) {
                    $parts = data_get($response->json(), 'candidates.0.content.parts', []);
                    $textChunks = [];

                    foreach ($parts as $part) {
                        // Skip internal thought parts if returned separately
                        if (!empty($part['thought'])) {
                            continue;
                        }
                        if (!empty($part['text'])) {
                            $textChunks[] = $part['text'];
                        }
                    }

                    $aiText = trim(implode("\n\n", $textChunks));

                    if (!empty($aiText)) {
                        Log::info("GeminiService succeeded using model [{$model}].");
                        return trim(str_replace(['**', '*', '#'], '', $aiText));
                    }
                }

                Log::warning("Gemini API attempt with model [{$model}] failed", [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            } catch (\Throwable $e) {
                Log::error("GeminiService Exception on model [{$model}]: " . $e->getMessage());
            }
        }

        return $fallbackSummary;
    }

    /**
     * Query Google's models endpoint to get active text-generation Gemini models enabled for this key.
     */
    private function discoverAvailableFlashModels(string $apiKey): array
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->get("https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}");

            if (! $response->successful()) {
                return ['gemini-flash-lite-latest', 'gemini-flash-latest'];
            }

            $models = data_get($response->json(), 'models', []);
            $liteModels = [];
            $flashModels = [];

            foreach ($models as $item) {
                $rawName = $item['name'] ?? '';
                $methods = $item['supportedGenerationMethods'] ?? [];

                if (!in_array('generateContent', $methods, true)) {
                    continue;
                }

                $slug = str_replace('models/', '', $rawName);

                if (
                    !str_contains($slug, 'gemini') ||
                    str_contains($slug, 'embedding') ||
                    str_contains($slug, 'image') ||
                    str_contains($slug, 'audio') ||
                    str_contains($slug, 'tts') ||
                    str_contains($slug, 'robotics') ||
                    str_contains($slug, 'computer-use')
                ) {
                    continue;
                }

                if (str_contains($slug, 'flash-lite')) {
                    $liteModels[] = $slug;
                } elseif (str_contains($slug, 'flash')) {
                    $flashModels[] = $slug;
                }
            }

            $ordered = array_values(array_unique(array_merge($liteModels, $flashModels)));

            return !empty($ordered)
                ? array_slice($ordered, 0, 4)
                : ['gemini-flash-lite-latest', 'gemini-flash-latest'];
        } catch (\Throwable $e) {
            Log::error('GeminiService model discovery exception: ' . $e->getMessage());
            return ['gemini-flash-lite-latest', 'gemini-flash-latest'];
        }
    }
}