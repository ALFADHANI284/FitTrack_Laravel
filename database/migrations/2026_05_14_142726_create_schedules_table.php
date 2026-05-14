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
    Schema::create('schedules', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
        
        // Tambahkan relasi ke tabel workouts
        $table->foreignId('workout_id')->constrained()->onDelete('cascade'); 
        
        // Title bisa dibikin nullable karena kita bisa ambil nama dari tabel workout
        $table->string('title')->nullable(); 
        $table->text('description')->nullable();
        $table->dateTime('schedule_time'); 
        $table->boolean('is_notified')->default(false); 
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
