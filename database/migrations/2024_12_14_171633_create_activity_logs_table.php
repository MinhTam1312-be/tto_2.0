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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('fullname'); // Tên người dùng
            $table->string('role'); // Vai trò của người dùng
            $table->string('action'); // Hành động thực hiện
            $table->text('discription'); // Mô tả chi tiết
            $table->enum('status', ['success', 'fail']); // Trạng thái hoạt động
            $table->timestamps(); // Thời gian hoạt động
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};