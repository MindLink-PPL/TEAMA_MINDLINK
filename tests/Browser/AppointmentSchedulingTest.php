<?php

namespace Tests\Browser;

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AppointmentSchedulingTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function createStudent(string $email = 'student@test.com'): User
    {
        return User::create([
            'name' => 'Test Student',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => 'student',
        ]);
    }

    private function createCounsellor(): User
    {
        return User::create([
            'name' => 'Test Counsellor',
            'email' => 'counsellor@test.com',
            'password' => Hash::make('password123'),
            'role' => 'counsellor',
            'verification_status' => 'verified',
        ]);
    }

    private function addAvailability(
        User $counsellor,
        string $date,
        string $time
    ): void {
        Availability::create([
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
    ->pause(3000)
    ->screenshot('debug-student-page')
    ->assertPathIs('/student/appointments')
                ->waitUntil(
                    "document.querySelector('#counsellor option[value=\"{$counsellor->id}\"]') !== null"
                )

->select('#counsellor', (string) $counsellor->id)

// Set the date correctly using JavaScript
->script("
    const dateInput = document.querySelector('#date');
    dateInput.value = '{$date}';
    dateInput.dispatchEvent(new Event('input', { bubbles: true }));
    dateInput.dispatchEvent(new Event('change', { bubbles: true }));
")

->waitUntil(
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
                    "document.querySelector('#counsellor option[value=\"{$counsellor->id}\"]') !== null"
                )
                ->select('#counsellor', (string) $counsellor->id)
                ->type('#date', $date)
                ->waitForText('No available slots', '#time')
                ->assertSeeIn('#time', 'No available slots')
                ->screenshot('TC-002-occupied-slot');
        });

        $this->assertDatabaseCount('appointments', 1);
    }
}
