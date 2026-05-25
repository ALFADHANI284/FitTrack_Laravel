<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('daily_calories_target')->default(0)->after('tier');
            $table->integer('daily_protein_target')->default(0)->after('daily_calories_target');
            $table->integer('daily_carbs_target')->default(0)->after('daily_protein_target');
            $table->integer('daily_fat_target')->default(0)->after('daily_carbs_target');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['daily_calories_target', 'daily_protein_target', 'daily_carbs_target', 'daily_fat_target']);
        });
    }
};