<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Counsellor;
use App\Models\CounsellingRequest;

class CounsellingController extends Controller
{
    public function create()
    {
        $counsellors = Counsellor::all();

        // Fetch all active bookings to grey out taken time slots dynamically
        $bookedSlots = CounsellingRequest::whereIn('status', ['Under Review', 'Approved'])
            ->get(['counsellor_name', 'appointment_date', 'appointment_time']);

        return view('counselling.request', compact('counsellors', 'bookedSlots'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'concern' => 'required|string|min:10|max:1000',
            'goals' => 'nullable|string|max:500',
            'consent_agreed' => 'accepted',
            'counsellor_id' => 'required|exists:counsellors,counsellor_id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|in:09:00,10:00,13:30,15:00',
            'appointment_mode' => 'required|in:In person,Online Meeting',
        ], [
            'concern.required' => 'Please describe your concern before submitting.',
            'concern.min' => 'Your concern description must be at least 10 characters.',
            'concern.max' => 'Your concern description cannot exceed 1000 characters.',
            'goals.max' => 'Your goals cannot exceed 500 characters.',
            'consent_agreed.accepted' => 'You must accept the Informed Consent Agreement (UU PDP) to proceed.',
            'counsellor_id.exists' => 'The selected counsellor does not exist.',
            'appointment_date.after_or_equal' => 'Appointment date cannot be in the past.',
            'appointment_time.in' => 'Please select a valid available time slot.',
            'appointment_mode.in' => 'Session format must be either In person or Online Meeting.',
        ]);

        $counsellor = Counsellor::findOrFail($validated['counsellor_id']);

        // Prevent double-booking on the backend
        $alreadyBooked = CounsellingRequest::where('counsellor_name', $counsellor->name)
            ->where('appointment_date', $validated['appointment_date'])
            ->where('appointment_time', $validated['appointment_time'])
            ->whereIn('status', ['Under Review', 'Approved'])
            ->exists();

        if ($alreadyBooked) {
            return back()->withInput()->withErrors([
                'appointment_time' => "The {$validated['appointment_time']} slot with {$counsellor->name} on {$validated['appointment_date']} is already booked."
            ]);
        }

        CounsellingRequest::create([
            'student_id' => 1,
            'counsellor_name' => $counsellor->name,
            'concern' => $validated['concern'],
            'goals' => $validated['goals'] ?? '',
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'appointment_mode' => $validated['appointment_mode'],
            'status' => 'Under Review',
        ]);

        return redirect()->route('counselling.request')
            ->with('success', 'Counselling request successfully sent!');
    }
}