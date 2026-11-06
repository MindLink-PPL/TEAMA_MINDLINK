<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CounsellingRequest;
use App\Models\WellbeingCheckin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentPortalController extends Controller
{
    /**
     * Get the 10-question wellbeing reflection quiz (PG-03).
     */
    public static function getQuizQuestions(): array
    {
        return [
            1 => [
                'number' => 1,
                'prompt' => 'Over the past two weeks',
                'question' => 'How often have you felt little interest or pleasure in doing things you usually enjoy?',
            ],
            2 => [
                'number' => 2,
                'prompt' => 'Over the past two weeks',
                'question' => 'How often have you felt overwhelmed by your academic workload or deadlines?',
            ],
            3 => [
                'number' => 3,
                'prompt' => 'Over the past two weeks',
                'question' => 'How often have you had trouble falling or staying asleep, or sleeping too much?',
            ],
            4 => [
                'number' => 4,
                'prompt' => 'Over the past two weeks',
                'question' => 'How often have you felt tired or had little energy during your classes?',
            ],
            5 => [
                'number' => 5,
                'prompt' => 'Over the past two weeks',
                'question' => 'How often have you felt nervous, anxious, or on edge about university life?',
            ],
            6 => [
                'number' => 6,
                'prompt' => 'Over the past two weeks',
                'question' => 'How often have you found it difficult to concentrate on study or daily activities?',
            ],
            7 => [
                'number' => 7,
                'prompt' => 'Over the past two weeks',
                'question' => 'How often have you been unable to stop or control worrying thoughts?',
            ],
            8 => [
                'number' => 8,
                'prompt' => 'Over the past two weeks',
                'question' => 'How often have you had difficulty relaxing after completing study sessions?',
            ],
            9 => [
                'number' => 9,
                'prompt' => 'Over the past two weeks',
                'question' => 'How often have you felt disconnected from peers, friends, or campus support?',
            ],
            10 => [
                'number' => 10,
                'prompt' => 'Over the past two weeks',
                'question' => 'How often have you felt that speaking with a counsellor could help you manage current pressures?',
            ],
        ];
    }

    /**
     * Get the educational mental health literacy articles (PG-02).
     */
    public static function getArticles(): array
    {
        return [
            [
                'id' => 1,
                'initial' => 'A',
                'banner_bg' => 'bg-[#e6f5f0]',
                'topic' => 'Anxiety',
                'category_label' => 'ANXIETY',
                'title' => 'Understanding anxious thoughts',
                'summary' => 'How anxiety may affect thoughts, sleep, and concentration, and when to seek support.',
                'read_time' => '6 min read',
                'content' => 'Anxious thoughts are a common response to uncertainty and academic pressure. Grounding techniques—such as paced breathing, naming five things in your environment, and separating facts from assumptions—can help reduce immediate tension. If anxious thoughts persist for more than two weeks or interfere with daily study and sleep, consider booking a private session with a campus counsellor.',
            ],
            [
                'id' => 2,
                'initial' => 'A',
                'banner_bg' => 'bg-[#f1f5f9]',
                'topic' => 'Academic stress',
                'category_label' => 'ACADEMIC STRESS',
                'title' => 'Planning during a difficult week',
                'summary' => 'Set priorities, ask for help, and reduce avoidable pressure during a difficult week.',
                'read_time' => '5 min read',
                'content' => 'When multiple assignments and exams converge in the same week, breaking tasks into 25-minute focused blocks and identifying the single highest-priority deliverable each morning can prevent burnout. Communicate early with tutors if you need extensions, and protect at least 30 minutes daily for rest and recovery.',
            ],
            [
                'id' => 3,
                'initial' => 'S',
                'banner_bg' => 'bg-[#fff3e6]',
                'topic' => 'Sleep',
                'category_label' => 'SLEEP',
                'title' => 'A practical sleep routine',
                'summary' => 'Simple steps that may support a more consistent sleep routine.',
                'read_time' => '4 min read',
                'content' => 'Consistent wake times, dimming screens 45 minutes before bed, and keeping study materials away from your sleeping area help signal to your brain that it is time to rest. Avoid caffeine in the late afternoon and write down tomorrow’s to-do list before lying down so your mind does not race overnight.',
            ],
            [
                'id' => 4,
                'initial' => 'R',
                'banner_bg' => 'bg-[#e6f5f0]',
                'topic' => 'Relationships',
                'category_label' => 'RELATIONSHIPS',
                'title' => 'Healthy boundaries and peer support',
                'summary' => 'Communicating personal boundaries clearly while maintaining supportive friendships at university.',
                'read_time' => '5 min read',
                'content' => 'Balancing social commitments with personal wellbeing requires clear, respectful communication. Let friends and group members know your availability ahead of busy periods, and lean on trusted peers or student support services when you feel isolated.',
            ],
        ];
    }

    /**
     * Display PG-02: Mental Health Literacy page.
     */
    public function literacy(Request $request): View
    {
        $topics = ['All topics', 'Anxiety', 'Sleep', 'Academic stress', 'Relationships'];
        $selectedTopic = $request->query('topic', 'All topics');
        $search = trim((string) $request->query('search', ''));

        $allArticles = self::getArticles();
        $articles = array_filter($allArticles, function (array $article) use ($selectedTopic, $search) {
            $matchesTopic = $selectedTopic === 'All topics' || strcasecmp($article['topic'], $selectedTopic) === 0;
            $matchesSearch = $search === ''
                || stripos($article['title'], $search) !== false
                || stripos($article['summary'], $search) !== false
                || stripos($article['topic'], $search) !== false
                || stripos($article['content'], $search) !== false;

            return $matchesTopic && $matchesSearch;
        });

        return view('student.literacy', [
            'topics' => $topics,
            'selectedTopic' => $selectedTopic,
            'search' => $search,
            'articles' => array_values($articles),
            'allArticles' => $allArticles,
        ]);
    }

    /**
     * Display PG-03: Wellbeing Check In (quiz-like reflection page).
     */
    public function wellbeing(Request $request): View
    {
        $questions = self::getQuizQuestions();
        $options = [
            0 => 'Not at all',
            1 => 'Several days',
            2 => 'More than half the days',
            3 => 'Nearly every day',
        ];

        $recentCheckins = $request->user()
            ->wellbeingCheckins()
            ->whereNotNull('answers')
            ->latest()
            ->take(5)
            ->get();

        return view('student.wellbeing', [
            'questions' => $questions,
            'options' => $options,
            'recentCheckins' => $recentCheckins,
        ]);
    }

    /**
     * Store a completed Wellbeing Check-In quiz response.
     */
    public function storeWellbeing(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*' => ['required', 'integer', 'min:0', 'max:3'],
            'mood' => ['nullable', 'string', 'max:50'],
        ]);

        $totalScore = array_sum($validated['answers']);

        if ($totalScore <= 7) {
            $guidance = 'Your reflection suggests manageable day-to-day balance. Continue maintaining a consistent sleep routine and healthy study boundaries.';
        } elseif ($totalScore <= 16) {
            $guidance = 'You have experienced moderate study or emotional pressure over the past two weeks. Exploring our Mental Health Literacy articles or scheduling a check-in with a counsellor may be helpful.';
        } else {
            $guidance = 'It looks like things have felt quite heavy recently. We encourage you to submit a private Counselling Request so an authorised counsellor can support you.';
        }

        WellbeingCheckin::create([
            'user_id' => $request->user()->id,
            'mood' => $validated['mood'] ?? null,
            'answers' => $validated['answers'],
            'total_score' => $totalScore,
            'guidance' => $guidance,
        ]);

        return redirect()
            ->route('student.wellbeing')
            ->with('success', 'Wellbeing check-in saved privately. ' . $guidance);
    }

    /**
     * Store a quick daily mood response from PG-01 Student Dashboard.
     */
    public function storeMood(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mood' => ['required', 'string', 'in:Very low,Low,Okay,Good,Great'],
        ]);

        WellbeingCheckin::create([
            'user_id' => $request->user()->id,
            'mood' => $validated['mood'],
            'answers' => null,
            'total_score' => 0,
            'guidance' => 'Daily mood check-in recorded privately.',
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', "Your mood response ({$validated['mood']}) has been saved privately.");
    }

    /**
     * Display PG-04: Counselling Request & Scheduling page.
     */
    public function counselling(Request $request): View
    {
        $counsellors = [
            'Alisa • Student wellbeing',
            'Sarah Connor • Academic & personal support',
        ];

        $timeSlots = ['09:00', '10:00', '13:30', '15:00'];

        $concerns = [
            'Academic pressure and anxiety',
            'Sleep difficulty and fatigue',
            'Personal or relationship stress',
            'Low mood or motivation',
            'General wellbeing check-in',
        ];

        $appointments = $request->user()
            ->counsellingRequests()
            ->latest()
            ->get();

        return view('student.counselling', [
            'counsellors' => $counsellors,
            'timeSlots' => $timeSlots,
            'concerns' => $concerns,
            'appointments' => $appointments,
        ]);
    }

    /**
     * Store a new Counselling Request / Appointment booking (PG-04).
     */
    public function storeCounselling(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'counsellor_name' => ['required', 'string', 'max:255'],
            'concern' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'scheduled_date' => ['required', 'string', 'max:255'],
            'scheduled_time' => ['required', 'string', 'max:50'],
            'session_mode' => ['nullable', 'string', 'in:In person,Online'],
            'consent' => ['nullable', 'boolean'],
        ]);

        CounsellingRequest::create([
            'user_id' => $request->user()->id,
            'counsellor_name' => $validated['counsellor_name'],
            'concern' => $validated['concern'],
            'notes' => $validated['notes'] ?? null,
            'consent_accepted_at' => now(),
            'scheduled_date' => $validated['scheduled_date'],
            'scheduled_time' => $validated['scheduled_time'],
            'session_mode' => $validated['session_mode'] ?? 'In person',
            'status' => 'Confirmed',
        ]);

        return redirect()
            ->route('student.counselling')
            ->with('success', "Your private counselling appointment with {$validated['counsellor_name']} on {$validated['scheduled_date']} at {$validated['scheduled_time']} has been scheduled and confirmed.");
    }

    /**
     * Display Student Notifications page.
     */
    public function notifications(Request $request): View
    {
        $appointments = $request->user()->counsellingRequests()->latest()->get();
        $checkins = $request->user()->wellbeingCheckins()->latest()->take(5)->get();

        return view('student.notifications', [
            'appointments' => $appointments,
            'checkins' => $checkins,
        ]);
    }
}

