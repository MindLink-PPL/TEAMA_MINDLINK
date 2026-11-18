<?php

namespace App\Http\Controllers;

use App\Models\{Appointment, Availability, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function page()
    {
        return app(CounsellingController::class)->create();
    }

    public function counsellors()
    {
        return User::where('role', 'counsellor')->get(['id', 'name']);
    }

    public function counsellorDashboard()
{
    $appointments = Appointment::with('student:id,name,email')
        ->where('counsellor_id', auth()->id())
        ->orderBy('date')
        ->orderBy('time')
        ->get();

    $availabilities = Availability::where(
        'counsellor_id',
        auth()->id()
    )
        ->orderBy('date')
        ->orderBy('time')
        ->get();

    return view(
        'counsellor.dashboard',
        compact('appointments', 'availabilities')
    );
}
    // Free slots = opened by counsellor minus already booked (PBI #14 reads this)
public function slots(Request $r)
{
    \Log::info('SLOTS API CALLED', [
        'counsellor_id' => $r->counsellor_id,
        'date' => $r->date,
        'user_id' => auth()->id(),
    ]);

    $r->validate([
        'counsellor_id' => 'required|exists:users,id',
        'date' => 'required|date'
    ]);

    $slots = $this->freeSlots(
        $r->counsellor_id,
        $r->date,
        $r->ignore
    );

    \Log::info('SLOTS API RESULT', [
        'slots' => $slots->all(),
    ]);

    return response()->json($slots);
}

    public function mine()
    {
        return Appointment::with('counsellor:id,name')
            ->where('student_id', auth()->id())->where('status', 'confirmed')
            ->orderBy('date')->orderBy('time')->get();
    }

    public function store(Request $r)
    {
        $data = $this->validated($r);
        return DB::transaction(function () use ($data) {
            abort_unless($this->isFree($data['counsellor_id'], $data['date'], $data['time']), 409, 'Slot no longer available');
            return Appointment::create($data + ['student_id' => auth()->id(), 'status' => 'confirmed']);
        });
    }

    public function update(Request $r, Appointment $appointment)   // reschedule
    {
        abort_unless($appointment->student_id === auth()->id(), 403);
        $data = $this->validated($r);
        return DB::transaction(function () use ($data, $appointment) {
            abort_unless($this->isFree($data['counsellor_id'], $data['date'], $data['time'], $appointment->id), 409, 'Slot no longer available');
            $appointment->update($data);
            return $appointment;
        });
    }

    public function destroy(Appointment $appointment)              // cancel
    {
        abort_unless($appointment->student_id === auth()->id(), 403);
        $appointment->update(['status' => 'cancelled']);
        return response()->noContent();
    }

    private function validated(Request $r): array
    {
        return $r->validate([
            'counsellor_id' => 'required|exists:users,id',
            'date' => 'required|date|after:today',
            'time' => 'required|string|size:5',
            'mode' => 'required|in:In person,Online',
        ]);
    }

    private function isFree($cid, $date, $time, $ignoreId = null): bool
    {
        $open = Availability::where(compact('date', 'time'))->where('counsellor_id', $cid)->exists();
        $booked = Appointment::where('counsellor_id', $cid)->where('date', $date)->where('time', $time)
            ->where('status', 'confirmed')->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists();
        return $open && !$booked;
    }

    private function freeSlots($cid, $date, $ignoreId = null)
    {
        return Availability::where('counsellor_id', $cid)->where('date', $date)->orderBy('time')->pluck('time')
            ->filter(fn($t) => $this->isFree($cid, $date, $t, $ignoreId))->values();
    }
    
public function storeAvailability(Request $request)
{
    abort_unless(
        auth()->user()->verification_status === 'verified',
        403,
        'Your counsellor account must be verified first.'
    );

    $validated = $request->validate([
        'date' => ['required', 'date', 'after_or_equal:today'],
        'start_time' => ['required', 'date_format:H:i'],
        'end_time' => ['required', 'date_format:H:i'],
    ]);

    $start = Carbon::createFromFormat(
        'H:i',
        $validated['start_time']
    );

    $end = Carbon::createFromFormat(
        'H:i',
        $validated['end_time']
    );

    if ($end->lte($start)) {
        return back()->withErrors([
            'end_time' => 'End time must be later than start time.'
        ]);
    }

    // Prevent adding slots that have already passed today.
    $firstSlot = Carbon::parse(
        $validated['date'] . ' ' . $validated['start_time']
    );

    if ($firstSlot->isPast()) {
        return back()->withErrors([
            'start_time' => 'Please select a future time.'
        ]);
    }

    $created = 0;

    // Generate one-hour appointment slots.
    while ($start->lt($end)) {

        $time = $start->format('H:i');

        $availability = Availability::firstOrCreate([
            'counsellor_id' => auth()->id(),
            'date' => $validated['date'],
            'time' => $time,
        ]);

        if ($availability->wasRecentlyCreated) {
            $created++;
        }

        $start->addHour();
    }

    return back()->with(
        'success',
        $created . ' availability slot(s) added successfully!'
    );
}


public function destroyAvailability(Availability $availability)
{
    abort_unless(
        auth()->user()->verification_status === 'verified',
        403,
        'Your counsellor account must be verified first.'
    );

    // Make sure the slot belongs to the logged-in counsellor.
    abort_unless(
        $availability->counsellor_id === auth()->id(),
        403
    );

    // Check whether this slot already has a confirmed appointment.
    $isBooked = Appointment::where(
        'counsellor_id',
        auth()->id()
    )
        ->whereDate('date', $availability->date)
        ->where('time', $availability->time)
        ->where('status', 'confirmed')
        ->exists();

    if ($isBooked) {
        return back()->withErrors([
            'availability' => 'This slot already has a confirmed appointment and cannot be removed.'
        ]);
    }

    $availability->delete();

    return back()->with(
        'success',
        'Availability slot removed successfully!'
    );
}

}
