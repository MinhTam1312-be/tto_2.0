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
        Schema::create('status_docs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->boolean('status_doc')->default(false)->nullable();
            $table->unsignedInteger('cache_time_video')->nullable();
            $table->ulid('document_id');
            $table->ulid('enrollment_id');
            $table->boolean('del_flag');
            $table->timestamps();
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->onDelete('restrict');
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
        Schema::dropIfExists('status_docs');
    }
};
