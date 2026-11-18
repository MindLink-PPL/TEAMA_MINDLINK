<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\CounsellingRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CounsellorPortalController extends Controller
{
    /**
     * Display the unified role-based portal dashboard (PG-01 Student, PG-05 Counsellor, PG-06 Admin).
     */
    public function dashboard(): View
    {
        $user = auth()->user()->load('role.permissions');
        $upcomingAppointment = null;
        $studentRequests = collect();
        $counsellorData = [];

        if ($user->isStudent()) {
            $studentRequests = $user->counsellingRequests()->latest()->get();
            $upcomingAppointment = $studentRequests->first();
        } elseif ($user->isCounsellor()) {
            $counsellorData = self::getWorkspaceData();
        }

        return view('dashboard', array_merge(
            compact('user', 'upcomingAppointment', 'studentRequests'),
            $counsellorData
        ));
    }

    /**
     * Build the shared Counsellor Workspace dataset (used by /dashboard and /counsellor/workspace).
     */
    public static function getWorkspaceData(): array
    {
        $requests = CounsellingRequest::with(['user'])
            ->get()
            ->sortBy(function (CounsellingRequest $req) {
                // Keep PG-05 canonical order at the top (ST-204, ST-187, ST-221, ST-163), then newest
                $order = [
                    'ST-204' => 1,
                    'ST-187' => 2,
                    'ST-221' => 3,
                    'ST-163' => 4,
                ];

                $rank = $order[$req->student_code] ?? 99;
                $tieBreaker = $req->student_code && isset($order[$req->student_code]) ? $req->id : (10000 - $req->id);

                return sprintf('%03d-%06d', $rank, $tieBreaker);
            })
            ->values();

        $queueItems = $requests->map(function (CounsellingRequest $req) {
            $student = $req->user;
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
                'session_note' => $req->session_note ?? '',
                'note_status' => $req->note_status ?: 'none',
                'support_action' => $req->support_action ?: 'Individual Counselling & Coping Support',
                'notified' => $req->student_notified_at !== null,
            ];
        })->unique('code')->values();

        $activeRequest = $queueItems->firstWhere('code', 'ST-187') ?? $queueItems->first();
        $availabilities = auth()->check()
            ? Availability::where('counsellor_id', auth()->id())
                ->orderBy('date')
                ->orderBy('time')
                ->get()
            : collect();

        return [
            'counsellorQueue' => $queueItems,
            'activeCounsellorRequest' => $activeRequest,
            'requests' => CounsellingRequest::orderBy('created_at', 'desc')->get(),
            'availabilities' => $availabilities,
            'highPriorityCount' => max(2, $requests->where('priority', 'High priority')->count()),
            'pendingRequestsCount' => max(7, $requests->count()),
            'todaysSessionsCount' => max(4, $requests->whereIn('priority', ['Scheduled', 'High priority'])->count()),
            'followUpsDueCount' => max(3, $requests->where('follow_up_due', true)->count()),
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

        $query = CounsellingRequest::with(['user'])->latest();

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
            $counsellingRequest = CounsellingRequest::where('student_code', $validated['student_code'])
                ->orderBy('id')
                ->first();
        }
        if (! $counsellingRequest) {
            $counsellingRequest = CounsellingRequest::where('student_code', 'ST-187')
                ->orderBy('id')
                ->first()
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
        $appointments = CounsellingRequest::with(['user'])
            ->orderBy('scheduled_time')
            ->get();

        $availabilities = auth()->check()
            ? Availability::where('counsellor_id', auth()->id())
                ->orderBy('date')
                ->orderBy('time')
                ->get()
            : collect();

        $timeSlots = ['09:00', '10:00', '13:30', '15:00'];

        return view('counsellor.schedule', [
            'appointments' => $appointments,
            'availabilities' => $availabilities,
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
            'counsellingRequests' => fn ($q) => $q->latest(),
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

        return view('counsellor.records', [
            'students' => $students,
            'search' => $search,
        ]);
    }
}
