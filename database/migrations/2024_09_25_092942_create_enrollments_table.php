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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->tinyInteger('rating_course')->nullable();
            $table->text('feedback_text')->nullable();
            $table->enum('status_course', ['completed', 'in_progress', 'failed'])->default('in_progress');
            $table->text('certificate_course')->nullable();
            $table->boolean('enroll')->default(false)->nullable();
            $table->ulid('module_id');
            $table->ulid('user_id');
            $table->boolean('del_flag');
            $table->timestamps();
            $table->foreign('module_id')
                ->references('id')
                ->on('modules')
                ->onDelete('restrict');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
