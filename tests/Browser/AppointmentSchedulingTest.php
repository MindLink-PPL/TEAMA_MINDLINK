<?php

namespace Tests\Browser;

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AppointmentSchedulingTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Appointment::query()->delete();
        Availability::query()->delete();
    }

    private function createStudent(string $email = 'student@test.com'): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Test Student',
                'password' => Hash::make('password123'),
                'role' => 'student',
            ]
        );
    }

    private function createCounsellor(): User
    {
        return User::updateOrCreate(
            ['email' => 'counsellor@test.com'],
            [
                'name' => 'Test Counsellor',
                'password' => Hash::make('password123'),
                'role' => 'counsellor',
                'verification_status' => 'verified',
            ]
        );
    }

    private function addAvailability(
        User $counsellor,
        string $date,
        string $time
    ): void {
        Availability::firstOrCreate([
            'counsellor_id' => $counsellor->id,
            'date' => $date,
            'time' => $time,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TC-001: POSITIVE TEST
    |--------------------------------------------------------------------------
    | Student can book, reschedule and cancel an appointment.
    */

    public function test_TC001_student_can_manage_appointments(): void
    {
        $student = $this->createStudent();
        $counsellor = $this->createCounsellor();

        $date = Carbon::today()->addDays(7)->format('Y-m-d');
        $newDate = Carbon::today()->addDays(8)->format('Y-m-d');

        $this->addAvailability($counsellor, $date, '09:00');
        $this->addAvailability($counsellor, $newDate, '10:00');

        $this->browse(function (Browser $browser) use (
            $student,
            $counsellor,
            $date,
            $newDate
        ) {
            // BOOK APPOINTMENT
            $browser->loginAs($student)
                ->visit('/student/appointments')
                ->assertPathIs('/student/appointments')
                ->waitUntil(
                    "document.querySelector('#counsellor option[value=\"{$counsellor->id}\"]') !== null",
                    10
                )
                ->select('#counsellor', (string) $counsellor->id)
                ->script("
                    const dateInput = document.querySelector('#date');
                    dateInput.value = '{$date}';
                    dateInput.dispatchEvent(new Event('input', { bubbles: true }));
                    dateInput.dispatchEvent(new Event('change', { bubbles: true }));
                ");

            $browser->waitUntil(
                "document.querySelector('#time option[value=\"09:00\"]') !== null",
                10
            )
                ->select('#time', '09:00')
                ->select('#mode', 'Online')
                ->press('Confirm Booking')
                ->waitForText('Appointment booked successfully!')
                ->screenshot('TC-001-booking');

            // RESCHEDULE APPOINTMENT
            $browser->waitFor('#appointmentList button')
                ->click('#appointmentList button')
                ->waitForDialog(5)
                ->typeInDialog($newDate)
                ->acceptDialog()
                ->waitForDialog(5)
                ->typeInDialog('10:00')
                ->acceptDialog()
                ->waitForText('Appointment rescheduled successfully!')
                ->screenshot('TC-001-rescheduled');

            // CANCEL APPOINTMENT
            $browser->click('#appointmentList button:last-child')
                ->waitForDialog(5)
                ->acceptDialog()
                ->waitForText('Appointment cancelled successfully.')
                ->screenshot('TC-001-cancelled');
        });

        $this->assertDatabaseHas('appointments', [
            'student_id' => $student->id,
            'counsellor_id' => $counsellor->id,
            'date' => $newDate,
            'time' => '10:00',
            'status' => 'cancelled',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TC-002: NEGATIVE TEST
    |--------------------------------------------------------------------------
    | Student cannot book an already occupied appointment slot.
    */

    public function test_TC002_student_cannot_book_occupied_slot(): void
    {
        $studentA = $this->createStudent();
        $studentB = $this->createStudent('student2@test.com');
        $counsellor = $this->createCounsellor();

        $date = Carbon::today()->addDays(7)->format('Y-m-d');

        $this->addAvailability($counsellor, $date, '09:00');

        Appointment::create([
            'student_id' => $studentA->id,
            'counsellor_id' => $counsellor->id,
            'date' => $date,
            'time' => '09:00',
            'mode' => 'Online',
            'status' => 'confirmed',
        ]);

        $this->browse(function (Browser $browser) use (
            $studentB,
            $counsellor,
            $date
        ) {
            $browser->loginAs($studentB)
                ->visit('/student/appointments')
                ->waitFor('#counsellor')
                ->waitUntil(
                    "document.querySelector('#counsellor option[value=\"{$counsellor->id}\"]') !== null",
                    10
                )
                ->select('#counsellor', (string) $counsellor->id)
                ->script("
                    const dateInput = document.querySelector('#date');
                    dateInput.value = '{$date}';
                    dateInput.dispatchEvent(new Event('input', { bubbles: true }));
                    dateInput.dispatchEvent(new Event('change', { bubbles: true }));
                ");

            $browser->waitForText('No available slots', 10)
                ->assertSeeIn('#time', 'No available slots')
                ->screenshot('TC-002-occupied-slot');
        });

        $this->assertDatabaseCount('appointments', 1);
    }
}