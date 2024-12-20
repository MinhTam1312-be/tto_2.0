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
        Schema::create('faqs_course', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->text('question_faq');
            $table->text('answer_faq');
            $table->ulid('course_id');
            $table->boolean('del_flag');
            $table->timestamps();
            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs_course');
    }
};
