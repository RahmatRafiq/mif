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
        Schema::create('master_lines', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Line A, Line B, etc
            $table->string('code')->unique(); // L001, L002, etc
            $table->text('description')->nullable();
            $table->integer('capacity_per_day')->default(0); // Optional: capacity info
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_lines');
    }
};
