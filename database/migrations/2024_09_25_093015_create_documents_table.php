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
        Schema::create('documents', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name_document');
            $table->unsignedTinyInteger('serial_document');
            $table->text('discription_document')->nullable();
            $table->string('url_video')->nullable();
            $table->enum('type_document', ['video', 'code', 'quiz','summary']);
            $table->ulid('chapter_id');
            $table->boolean('del_flag');
            $table->timestamps();
            $table->foreign('chapter_id')
                ->references('id')
                ->on('chapters')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
