
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('certificate_path')->nullable();

            $table->string('verification_status')
                ->nullable()
                ->index();

            $table->text('rejection_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['verification_status']);

            $table->dropColumn([
                'certificate_path',
                'verification_status',
                'rejection_reason',
            ]);
        });
    }
};
