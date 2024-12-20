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
        Schema::create('notes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('title_note');
            $table->text('content_note');
            $table->unsignedInteger('cache_time_note');
            $table->ulid('document_id');
            $table->ulid('user_id');
            $table->boolean('del_flag');
            $table->timestamps();
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
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
        Schema::dropIfExists('notes');
    }
};
