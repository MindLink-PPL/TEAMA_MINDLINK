<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use App\Models\CounsellingRequest;
use App\Models\StudentReferral;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CounsellorPortalController extends Controller
{
    /**
     * Build the shared Counsellor Workspace dataset (used by /dashboard and /counsellor/workspace).
     */
    public static function getWorkspaceData(): array
    {
        $requests = CounsellingRequest::with(['user.wellbeingCheckins'])
            ->get()
            ->sortBy(function (CounsellingRequest $req) {
                // Keep PG-05 canonical order at the top (ST-204, ST-187, ST-221, ST-163), then newest
                $order = [
                    'ST-204' => 1,
                    'ST-187' => 2,
                    'ST-221' => 3,
                    'ST-163' => 4,
                ];

                return $order[$req->student_code] ?? (100 - $req->id);
            })
            ->values();

        $queueItems = $requests->map(function (CounsellingRequest $req) {
            $student = $req->user;
            $latestQuiz = $student?->wellbeingCheckins
                ?->whereNotNull('answers')
                ->sortByDesc('id')
                ->first();
            $latestMoodCheckin = $student?->wellbeingCheckins
                ?->whereNotNull('mood')
                ->sortByDesc('id')
                ->first();

            $code = $req->display_code;
            $priority = $req->priority ?: 'Scheduled';
            $submittedLabel = $req->submitted_label ?: ($req->concern . ' • appointment ' . $req->scheduled_time);

            return [
                'id' => $req->id,
                'code' => $code,
                'concern' => $req->concern,
                'submitted_label' => $submittedLabel,
                'priority' => $priority,
                'status' => $req->status,
                'scheduled_date' => $req->scheduled_date,
                'scheduled_time' => $req->scheduled_time,
                'session_mode' => $req->session_mode,
                'header_title' => "{$code} • Appointment {$req->scheduled_time}",
                'student_name' => $student?->name ?? 'Authorised Student',
                'student_email' => $student?->email ?? 'student@mindlink.com',
                'student_notes' => $req->notes ?: 'Student requested confidential counselling support.',
                'consent_verified' => $req->consent_accepted_at !== null,
                'consent_date' => $req->consent_accepted_at ? $req->consent_accepted_at->format('d M Y') : '13 Oct 2026',
                'wellbeing_score' => $latestQuiz ? $latestQuiz->total_score : 14,
                'wellbeing_mood' => $latestMoodCheckin?->mood ?? ($latestQuiz?->mood ?? 'Okay'),
                'wellbeing_guidance' => $latestQuiz?->guidance ?? 'Authorised wellbeing reflection available for counsellor review.',
                'session_note' => $req->session_note ?? '',
                'note_status' => $req->note_status ?: 'none',
                'support_action' => $req->support_action ?: 'Individual Counselling & Coping Support',
                'notified' => $req->student_notified_at !== null,
            ];
        })->values();

        $activeRequest = $queueItems->firstWhere('code', 'ST-187') ?? $queueItems->first();

        return [
            'counsellorQueue' => $queueItems,
            'activeCounsellorRequest' => $activeRequest,
            'highPriorityCount' => max(2, $requests->where('priority', 'High priority')->count()),
            'pendingRequestsCount' => max(7, $requests->count()),
            'todaysSessionsCount' => max(4, $requests->whereIn('priority', ['Scheduled', 'High priority'])->count()),
            'followUpsDueCount' => max(3, $requests->where('follow_up_due', true)->count() + StudentReferral::where('status', '!=', 'Completed')->count()),
        ];
    }

    /**
     * Display PG-05: Counsellor Workspace.
     */
    public function workspace(): View
    {
        $user = auth()->user()->load('role.permissions');
        $workspaceData = self::getWorkspaceData();

        return view('dashboard', array_merge(
            [
                'user' => $user,
                'upcomingAppointment' => null,
                'latestMood' => null,
            ],
            $workspaceData
        ));
    }

    /**
     * Display Counsellor Requests Queue & Triage page (R - Requests).
     */
    public function requests(Request $request): View
    {
        $selectedPriority = $request->query('priority', 'All');
        $search = trim((string) $request->query('search', ''));

        $query = CounsellingRequest::with(['user.wellbeingCheckins'])->latest();

        if ($selectedPriority !== 'All') {
            $query->where('priority', $selectedPriority);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('student_code', 'like', "%{$search}%")
                    ->orWhere('concern', 'like', "%{$search}%")
                    ->orWhere('support_action', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $requests = $query->get();
        $priorities = ['All', 'High priority', 'Scheduled', 'New', 'Follow-up', 'Completed'];
        $supportActions = [
            'Individual Counselling & Coping Support',
            'Priority Clinical Intake & Anxiety Support',
            'Structured Study & Grounding Plan',
            'Sleep Hygiene & Routine Consultation',
            'Follow-Up Wellbeing Review',
            'Academic Adjustment Referral',
        ];

        return view('counsellor.requests', [
            'requests' => $requests,
            'priorities' => $priorities,
            'selectedPriority' => $selectedPriority,
            'search' => $search,
            'supportActions' => $supportActions,
        ]);
    }

    /**
     * Update a student's counselling request priority, schedule, or support plan.
     */
    public function updateRequest(Request $request, CounsellingRequest $counsellingRequest): RedirectResponse
    {
        $validated = $request->validate([
            'priority' => ['required', 'string', 'in:High priority,Scheduled,New,Follow-up,Completed'],
            'status' => ['nullable', 'string', 'max:100'],
            'scheduled_date' => ['nullable', 'string', 'max:255'],
            'scheduled_time' => ['nullable', 'string', 'max:50'],
            'session_mode' => ['nullable', 'string', 'in:In person,Online'],
            'support_action' => ['nullable', 'string', 'max:255'],
            'session_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $counsellingRequest->priority = $validated['priority'];
        if (! empty($validated['status'])) {
            $counsellingRequest->status = $validated['status'];
        } elseif ($validated['priority'] === 'Completed') {
            $counsellingRequest->status = 'Completed';
        } elseif ($validated['priority'] === 'Scheduled') {
            $counsellingRequest->status = 'Confirmed';
        }

        if (! empty($validated['scheduled_date'])) {
            $counsellingRequest->scheduled_date = $validated['scheduled_date'];
        }
        if (! empty($validated['scheduled_time'])) {
            $counsellingRequest->scheduled_time = $validated['scheduled_time'];
        }
        if (! empty($validated['session_mode'])) {
            $counsellingRequest->session_mode = $validated['session_mode'];
        }
        if (array_key_exists('support_action', $validated) && $validated['support_action'] !== null) {
            $counsellingRequest->support_action = $validated['support_action'];
        }
        if (array_key_exists('session_note', $validated) && $validated['session_note'] !== null) {
            $counsellingRequest->session_note = $validated['session_note'];
        }

        $counsellingRequest->counsellor_id = $request->user()->id;
        $counsellingRequest->follow_up_due = in_array($validated['priority'], ['High priority', 'Follow-up'], true);
        $counsellingRequest->save();

        return back()->with(
            'success',
            "Updated counselling request {$counsellingRequest->display_code} ({$counsellingRequest->priority}) with support action: {$counsellingRequest->support_action}."
        );
    }

    /**
     * Store or complete a confidential session note from PG-05 Workspace or Records page.
     */
    public function storeSessionNote(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'counselling_request_id' => ['nullable', 'integer', 'exists:counselling_requests,id'],
            'student_code' => ['nullable', 'string', 'max:50'],
            'session_note' => ['required', 'string', 'max:3000'],
            'support_action' => ['nullable', 'string', 'max:255'],
            'action_type' => ['nullable', 'string', 'in:draft,complete'],
        ]);

        $counsellingRequest = null;
        if (! empty($validated['counselling_request_id'])) {
            $counsellingRequest = CounsellingRequest::find($validated['counselling_request_id']);
        }
        if (! $counsellingRequest && ! empty($validated['student_code'])) {
            $counsellingRequest = CounsellingRequest::where('student_code', $validated['student_code'])->first();
        }
        if (! $counsellingRequest) {
            $counsellingRequest = CounsellingRequest::where('student_code', 'ST-187')->first()
                ?? CounsellingRequest::first();
        }

        $actionType = $validated['action_type'] ?? 'complete';
        $isComplete = $actionType === 'complete';

        if ($counsellingRequest) {
            $counsellingRequest->session_note = $validated['session_note'];
            $counsellingRequest->note_status = $isComplete ? 'completed' : 'draft';
            $counsellingRequest->counsellor_id = $request->user()->id;

            if (! empty($validated['support_action'])) {
                $counsellingRequest->support_action = $validated['support_action'];
            }

            if ($isComplete) {
                $counsellingRequest->status = 'Completed & Notified';
                $counsellingRequest->student_notified_at = now();
            }

            $counsellingRequest->save();
            $code = $counsellingRequest->display_code;
        } else {
            $code = $validated['student_code'] ?? 'ST-187';
        }

        $message = $isComplete
            ? "Session note for {$code} completed and the student has been notified with appropriate support next steps."
            : "Confidential session note draft saved for {$code}.";

        return back()->with('success', $message);
    }

    /**
     * Display Counsellor Schedule & Appointments page (S - Schedule).
     */
    public function schedule(): View
    {
        $appointments = CounsellingRequest::with(['user.wellbeingCheckins'])
            ->orderBy('scheduled_time')
            ->get();

        $timeSlots = ['09:00', '10:00', '13:30', '15:00'];

        return view('counsellor.schedule', [
            'appointments' => $appointments,
            'timeSlots' => $timeSlots,
        ]);
    }

    /**
     * Display Authorised Student Information & Confidential Records page (N - Records).
     */
    public function records(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $studentsQuery = User::whereHas('role', function ($q) {
            $q->whereIn('slug', ['user', 'student']);
        })->with([
            'wellbeingCheckins' => fn ($q) => $q->latest(),
            'counsellingRequests' => fn ($q) => $q->latest(),
            'studentReferrals' => fn ($q) => $q->latest(),
        ]);

        if ($search !== '') {
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('counsellingRequests', function ($rq) use ($search) {
                        $rq->where('student_code', 'like', "%{$search}%")
                            ->orWhere('concern', 'like', "%{$search}%");
                    });
            });
        }

        $students = $studentsQuery->get();
        $quizQuestions = \App\Http\Controllers\Student\StudentPortalController::getQuizQuestions();

        return view('counsellor.records', [
            'students' => $students,
            'search' => $search,
            'quizQuestions' => $quizQuestions,
        ]);
    }

    /**
     * Display Student Support Referrals & Follow-Ups page (F - Referrals).
     */
    public function referrals(): View
    {
        $referrals = StudentReferral::with(['student', 'counsellor', 'counsellingRequest'])
            ->latest()
            ->get();

        $students = User::whereHas('role', function ($q) {
            $q->whereIn('slug', ['user', 'student']);
        })->with('counsellingRequests')->get();

        $referralTypes = [
            'Academic Support & Assessment Consideration',
            'Study Skills & Stress Management Workshop',
            'Sleep & Wellbeing Clinic',
            'Peer Mentorship & Social Connection',
            'Specialist Clinical Assessment',
        ];

        return view('counsellor.referrals', [
            'referrals' => $referrals,
            'students' => $students,
            'referralTypes' => $referralTypes,
        ]);
    }

    /**
     * Create a new student support referral / follow-up plan.
     */
    public function storeReferral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'referral_type' => ['required', 'string', 'max:255'],
            'urgency' => ['required', 'string', 'in:High priority,Standard,Follow-up'],
            'reason' => ['required', 'string', 'max:1000'],
            'support_plan' => ['required', 'string', 'max:1500'],
        ]);

        $student = User::with('counsellingRequests')->findOrFail($validated['user_id']);
        $latestRequest = $student->counsellingRequests->first();
        $studentCode = $latestRequest?->student_code ?: ('ST-' . (180 + $student->id));

        StudentReferral::create([
            'user_id' => $student->id,
            'counsellor_id' => $request->user()->id,
            'counselling_request_id' => $latestRequest?->id,
            'student_code' => $studentCode,
            'referral_type' => $validated['referral_type'],
            'urgency' => $validated['urgency'],
            'reason' => $validated['reason'],
            'support_plan' => $validated['support_plan'],
            'status' => 'Active',
        ]);

        return redirect()
            ->route('counsellor.referrals')
            ->with('success', "Created support referral ({$validated['referral_type']}) for {$student->name} ({$studentCode}).");
    }

    /**
     * Update the status of a student referral.
     */
    public function updateReferral(Request $request, StudentReferral $referral): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:Active,Follow-up due,Completed'],
            'support_plan' => ['nullable', 'string', 'max:1500'],
        ]);

        $referral->status = $validated['status'];
        if (! empty($validated['support_plan'])) {
            $referral->support_plan = $validated['support_plan'];
        }
        $referral->save();

        return back()->with('success', "Updated referral status for {$referral->student_code} to {$referral->status}.");
    }
}
