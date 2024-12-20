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
        Schema::create('transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->unsignedBigInteger('amount');
            $table->enum('payment_method', ['Momo', 'VnPay']);
            $table->enum('status', ['pending', 'completed', 'failed', 'canceled']);
            $table->text('payment_discription')->nullable();
            $table->ulid('enrollment_id');
            $table->boolean('del_flag');
            $table->timestamps();
            $table->foreign('enrollment_id')
                ->references('id')
                ->on('enrollments')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
