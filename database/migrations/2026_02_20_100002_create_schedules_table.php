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
            $table->foreignId('order_id')->constrained('master_orders')->onDelete('cascade');
            $table->foreignId('line_id')->constrained('master_lines')->onDelete('cascade');
            $table->date('start_date');
            $table->date('finish_date'); // Original planned finish date
            $table->date('current_finish_date'); // Actual finish date (after balancing)
            $table->integer('qty_total_target');
            $table->integer('qty_completed')->default(0); // Total actual completed
            $table->integer('days_extended')->default(0); // Days extended due to balancing
            $table->enum('status', ['pending', 'in_progress', 'completed', 'delayed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['line_id', 'start_date', 'current_finish_date']);
            $table->index('status');
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
