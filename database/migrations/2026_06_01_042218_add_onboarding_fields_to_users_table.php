<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Menampung alasan utama (Slide 1)
            $table->string('motivation')->nullable()->after('password');

            // Menampung target/goal (Slide 2)
            $table->enum('goal', ['lose_weight', 'maintain_weight', 'gain_weight'])->nullable()->after('motivation');

            // Menampung data fisik Dasar (Slide 3 & 4)
            $table->enum('gender', ['male', 'female'])->nullable()->after('goal');
            $table->integer('age')->nullable()->after('gender');
            $table->decimal('weight', 5, 2)->nullable()->after('age'); 
            $table->decimal('height', 5, 2)->nullable()->after('weight'); 

            // Menampung level aktivitas (Slide 5)
            $table->enum('activity_level', ['sedentary', 'lightly_active', 'moderately_active', 'very_active'])->nullable()->after('height');

            // Menampung hasil kalkulasi kalori target harian (Slide 6)
            $table->integer('daily_calorie_target')->nullable()->after('activity_level');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'motivation', 'goal', 'gender', 'age', 'weight', 'height', 'activity_level', 'daily_calorie_target'
            ]);
        });
    }
};