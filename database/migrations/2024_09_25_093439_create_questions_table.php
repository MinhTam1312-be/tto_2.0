<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->text('content_question');
            $table->text('answer_question');
            $table->enum('type_question', ['multiple_choice', 'fill', 'true_false']);
            $table->boolean('del_flag');
            $table->timestamps();
            $table->foreign('id')
                ->references('id')
                ->on('documents')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
