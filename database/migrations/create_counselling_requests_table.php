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
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('counsellor_name')->default('Alisa');
            $table->text('concern');
            $table->text('goals')->nullable();
            $table->string('appointment_date')->default('2026-10-21');
            $table->string('appointment_time')->default('10:00');
            $table->string('appointment_mode')->default('In person');
            $table->string('status')->default('Pending');
            $table->text('counsellor_notes')->nullable();
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
