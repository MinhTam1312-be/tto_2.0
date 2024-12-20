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
        Schema::create('posts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('title_post');
            $table->string('slug_post');
            $table->mediumText('content_post');
            $table->string('img_post')->nullable();
            $table->integer('views_post');
            $table->enum('status_post', ['confirming', 'success', 'failed'])->default('confirming'); // mới thêm cột post vào đây
            $table->boolean('del_flag');
            $table->ulid('user_id');
            $table->ulid('category_id');
            $table->timestamps();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
            $table->foreign('category_id')
                ->references('id')
                ->on('post_categories')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
