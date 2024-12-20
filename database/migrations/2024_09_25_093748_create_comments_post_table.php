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
        Schema::create('comments_post', function (Blueprint $table) {
            $table->ulid('id')->primary()->unique();
            $table->text('comment_text');
            $table->ulid('post_id');
            $table->ulid('user_id');
            $table->ulid('comment_to')->nullable();
            $table->boolean('del_flag');
            $table->timestamps();
            $table->foreign('post_id')
                ->references('id')
                ->on('posts')
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
        Schema::dropIfExists('comments_post');
    }
};
