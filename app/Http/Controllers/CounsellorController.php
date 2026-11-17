<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\CounsellingRequest;
use Illuminate\Http\Request;

class CounsellorController extends Controller
{
    public function index()
    {
        $requests = CounsellingRequest::orderBy('created_at', 'desc')->get();

        $appointments = auth()->check()
            ? Appointment::with('student:id,name,email')
                ->where('counsellor_id', auth()->id())
                ->orderBy('date')
                ->orderBy('time')
                ->get()
            : collect();

        $availabilities = auth()->check()
            ? Availability::where('counsellor_id', auth()->id())
                ->orderBy('date')
                ->orderBy('time')
                ->get()
            : collect();

        return view('counsellor.dashboard', compact('requests', 'appointments', 'availabilities'));
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Under Review,Approved,Declined,Completed',
            'counsellor_notes' => 'required_if:status,Declined|nullable|string|max:1000',
        ], [
            'status.required' => 'Action status is required.',
            'status.in' => 'Invalid status action selected.',
            'counsellor_notes.required_if' => 'Assessment / referral notes are required when declining a request.',
            'counsellor_notes.max' => 'Assessment notes cannot exceed 1000 characters.',
        ]);

        $counsellingRequest = CounsellingRequest::where('id', $id)
            ->orWhere('request_id', $id)
            ->firstOrFail();

        if (in_array($counsellingRequest->status, ['Approved', 'Declined'])) {
            return back()->withErrors([
                'status' => "Request #{$id} has already been finalized ({$counsellingRequest->status}) and cannot be modified."
            ]);
        }

        $counsellingRequest->update([
            'status' => $validated['status'],
            'counsellor_notes' => $validated['counsellor_notes'] ?? null,
        ]);

        return redirect()->route('counsellor.dashboard')
            ->with('success', "Request #{$id} updated to {$validated['status']}.");
    }
}