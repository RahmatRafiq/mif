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
        Schema::create('schedule_daily_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->date('date');
            $table->integer('target_output'); // Target untuk hari ini
            $table->integer('actual_output')->default(0); // Actual output yang dicapai
            $table->integer('balance')->default(0); // target - actual (bisa negatif jika exceed)
            $table->boolean('is_completed')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Unique constraint: one record per schedule per day
            $table->unique(['schedule_id', 'date']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_daily_outputs');
    }
};
