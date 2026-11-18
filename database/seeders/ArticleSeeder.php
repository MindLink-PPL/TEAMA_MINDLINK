<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $anxiety = ArticleCategory::firstOrCreate(['category_name' => 'Anxiety']);
        $sleep = ArticleCategory::firstOrCreate(['category_name' => 'Sleep']);
        $stress = ArticleCategory::firstOrCreate(['category_name' => 'Academic stress']);
        $relationships = ArticleCategory::firstOrCreate(['category_name' => 'Relationships']);

        Article::firstOrCreate(
            ['title' => 'Understanding anxious thoughts'],
            [
                'category_id' => $anxiety->id,
                'content'     => "Anxiety is a natural stress response, but persistent worry can affect your focus, sleep, and daily routine.\n\nStart by naming the specific worry, grounding yourself with slow breathing (4 seconds in, 6 seconds out), and breaking overwhelming tasks into one small immediate action. If anxious thoughts interfere with your studies for more than two weeks, consider reaching out to a university counsellor.",
                'source'      => 'MindLink Editorial & I-NAMHS Mental Health Literacy Guide',
            ]
        );

        Article::firstOrCreate(
            ['title' => 'Planning during a difficult week'],
            [
                'category_id' => $stress->id,
                'content'     => "Heavy assignment deadlines and exam weeks can quickly trigger burnout when everything feels urgent at once.\n\nList your tasks, separate what is due within 48 hours from what can wait, and schedule short 10-minute breaks away from screens. Asking lecturers or peers for clarification early reduces avoidable academic pressure.",
                'source'      => 'Telkom University Student Wellbeing Unit',
            ]
        );

        Article::firstOrCreate(
            ['title' => 'A practical sleep routine'],
            [
                'category_id' => $sleep->id,
                'content'     => "Consistent wake times and consistent sleep anchor both emotional regulation and memory retention.\n\nAim to wake up at the same time every morning, dim bright screens 30 minutes before bed, and avoid caffeine after late afternoon. Even small improvements in sleep regularity help stabilize mood during busy semesters.",
                'source'      => 'MindLink Sleep Hygiene Resource',
            ]
        );

        Article::firstOrCreate(
            ['title' => 'Healthy communication with peers and family'],
            [
                'category_id' => $relationships->id,
                'content'     => "Navigating misunderstandings with friends, roommates, or family is a common source of stress in university life.\n\nUse calm 'I feel' statements instead of blame, set clear boundaries around study and rest time, and seek supportive campus communities when feeling isolated.",
                'source'      => 'MindLink Peer Support Guide',
            ]
        );
    }
}
