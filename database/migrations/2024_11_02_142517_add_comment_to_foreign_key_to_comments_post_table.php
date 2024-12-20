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
        Schema::table('comments_post', function (Blueprint $table) {
            $table->foreign('comment_to')
                ->references('id')
                ->on('comments_post')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments_post', function (Blueprint $table) {
            //
        });
    }
};
