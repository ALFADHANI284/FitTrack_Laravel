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
        Schema::create('workouts', function (Blueprint $table) {
            $table->id();
            //kunci relasi (menyambungkan ke tabel categories)
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade'); 
            
            $table->string('name'); // Nama latihan (misal: Push Up, Squat)
            $table->integer('duration_minutes')->nullable(); // Durasi latihan
            $table->integer('calories_burned')->nullable(); // Estimasi kalori
            $table->text('description')->nullable(); // Cara melakukan latihan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workouts');
    }
};
