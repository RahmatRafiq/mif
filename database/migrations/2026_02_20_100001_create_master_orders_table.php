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
        Schema::create('master_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // PO-001, PO-002, etc
            $table->string('product_name');
            $table->string('product_code')->nullable();
            $table->integer('qty_total');
            $table->string('customer')->nullable();
            $table->date('order_date');
            $table->date('due_date');
            $table->enum('status', ['pending', 'scheduled', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_orders');
    }
};
