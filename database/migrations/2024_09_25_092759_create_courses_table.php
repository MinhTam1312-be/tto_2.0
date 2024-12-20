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
        Schema::create('courses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name_course');
            $table->string('slug_course');
            $table->string('img_course');
            $table->text('discription_course'); // đã thêm mô tả
            $table->enum('status_course', ['confirming', 'success', 'failed'])->default('confirming'); // đã thêm trạng thái thái
            $table->unsignedBigInteger('price_course');
            $table->unsignedBigInteger('discount_price_course')->nullable();
            $table->unsignedBigInteger('views_course');
            $table->float('rating_course', 3, 1);
            $table->decimal('tax_rate', 5, 2);
            $table->boolean('del_flag')->default(0);
            $table->ulid('user_id');
            $table->timestamps();
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
        Schema::dropIfExists('courses');
    }
};
