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
        Schema::create('wellbeing_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('mood')->nullable();
            $table->json('answers')->nullable();
            $table->unsignedInteger('total_score')->default(0);
            $table->text('guidance')->nullable();
            $table->timestamps();
        });

        Schema::create('counselling_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('counsellor_name')->default('Alisa • Student wellbeing');
            $table->string('concern')->default('Academic pressure and anxiety');
            $table->text('notes')->nullable();
            $table->timestamp('consent_accepted_at')->nullable();
            $table->string('scheduled_date')->default('Wednesday, 21 October 2026');
            $table->string('scheduled_time')->default('10:00');
            $table->string('session_mode')->default('In person');
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
        Schema::dropIfExists('wellbeing_checkins');
    }
};

