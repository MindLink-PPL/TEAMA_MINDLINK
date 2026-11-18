<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('counselling_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id')->nullable()->index();
            $table->string('counsellor_name')->default('Alisa • Student wellbeing');
            $table->text('concern');
            $table->text('goals')->nullable();
            $table->text('notes')->nullable();
            $table->text('counsellor_notes')->nullable();
            $table->timestamp('consent_accepted_at')->nullable();
            $table->string('scheduled_date')->default('Wednesday, 21 October 2026');
            $table->string('scheduled_time')->default('10:00');
            $table->string('session_mode')->default('In person');
            $table->string('appointment_date')->default('2026-10-21');
            $table->string('appointment_time')->default('10:00');
            $table->string('appointment_mode')->default('In person');
            $table->string('status')->default('Confirmed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counselling_requests');
    }
};