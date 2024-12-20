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
        Schema::create('comments_document', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('comment_title');
            $table->text('comment_text');
            $table->ulid('document_id');
            $table->ulid('user_id');
            $table->ulid('comment_to')->nullable(); // Không thêm khóa ngoại ở đây
            $table->boolean('del_flag');
            $table->timestamps();

            // Thêm các khóa ngoại
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
        Schema::dropIfExists('comments_document');
    }
};
