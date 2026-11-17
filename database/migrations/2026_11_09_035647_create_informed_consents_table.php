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
        Schema::create('informed_consents', function (Blueprint $table) {
            $table->id();
            
            // Ini 3 baris kolom tambahan dari kita buat nangkep data PBI-06
            $table->string('student_id')->nullable(); 
            $table->boolean('is_agreed')->default(false);
            $table->timestamp('agreed_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informed_consents');
    }
};