<?php

namespace App\Http\Controllers;

use App\Models\{Appointment, Availability, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function page() { return view('student.appointments'); }

    public function counsellors()
    {
        return User::where('role', 'counsellor')->get(['id', 'name']);
    }

    // Free slots = opened by counsellor minus already booked (PBI #14 reads this)
    public function slots(Request $r)
    {
        $r->validate(['counsellor_id' => 'required|exists:users,id', 'date' => 'required|date']);
        return $this->freeSlots($r->counsellor_id, $r->date, $r->ignore);
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
            return Appointment::create($data + ['student_id' => auth()->id()]);
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
}