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
            $table->id('request_id');
            $table->unsignedBigInteger('student_id')->nullable(); // Can be linked to users table later
            $table->string('counsellor_name')->default('Alisa');
            $table->text('concern'); // Description of problem/concerns (PBI-09)
            $table->text('goals')->nullable();
            $table->string('appointment_date')->default('2026-10-21');
            $table->string('appointment_time')->default('10:00');
            $table->string('appointment_mode')->default('In person');
            $table->string('status')->default('Pending'); // Pending, Approved, Draft, Completed
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
