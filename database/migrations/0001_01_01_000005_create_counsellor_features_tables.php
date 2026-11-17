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
        Schema::table('counselling_requests', function (Blueprint $table) {
            $table->string('student_code')->nullable()->after('user_id');
            $table->foreignId('counsellor_id')->nullable()->after('counsellor_name')->constrained('users')->nullOnDelete();
            $table->string('priority')->default('New')->after('status');
            $table->string('submitted_label')->nullable()->after('priority');
            $table->text('session_note')->nullable()->after('notes');
            $table->string('note_status')->default('none')->after('session_note');
            $table->string('support_action')->nullable()->after('note_status');
            $table->boolean('follow_up_due')->default(false)->after('support_action');
            $table->timestamp('student_notified_at')->nullable()->after('follow_up_due');
        });

        Schema::create('student_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('counsellor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('counselling_request_id')->nullable()->constrained('counselling_requests')->nullOnDelete();
            $table->string('student_code')->default('ST-187');
            $table->string('referral_type');
            $table->string('urgency')->default('Standard');
            $table->text('reason');
            $table->text('support_plan')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_referrals');

        Schema::table('counselling_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('counsellor_id');
            $table->dropColumn([
                'student_code',
                'priority',
                'submitted_label',
                'session_note',
                'note_status',
                'support_action',
                'follow_up_due',
                'student_notified_at',
            ]);
        });
    }
};
