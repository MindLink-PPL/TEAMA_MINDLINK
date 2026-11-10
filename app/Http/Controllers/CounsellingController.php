<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CounsellingRequest;
use App\Models\Counsellor;

class CounsellingController extends Controller
{
    public function create()
    {
        $counsellors = Counsellor::all();
        return view('counselling.request', compact('counsellors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'concern' => 'required|string',
            'counsellor_id' => 'required|exists:counsellors,counsellor_id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|string',
            'appointment_mode' => 'required|string',
        ]);

        $counsellor = \App\Models\Counsellor::findOrFail($validated['counsellor_id']);

        \App\Models\CounsellingRequest::create([
            'student_id' => 1,
            'counsellor_name' => $counsellor->name,
            'concern' => $validated['concern'],
            'goals' => $request->input('goals', ''),
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'appointment_mode' => $validated['appointment_mode'],
            'status' => 'Pending Review'
        ]);

        return redirect()->route('counselling.request')
            ->with('success', 'Counselling request successfully saved to MySQL!');
    }
}