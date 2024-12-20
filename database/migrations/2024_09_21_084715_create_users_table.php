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
        Schema::create('users', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->text('discription_user')->nullable();
            $table->string('password')->nullable();
            $table->string('fullname');
            $table->tinyInteger('age')->unsigned()->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('phonenumber', 12)->unique()->nullable();
            $table->string('avatar');
            $table->string('provider_id')->nullable();
            $table->enum('role', ['client','marketing', 'instructor', 'accountant', 'admin'])->default('client');
            // client là người dùng, marketing là người viết bài tự quản lý bài viết của mình, instructor là giảng viên có quyền thêm khóa học của nó, 
            // accountant là kế toán thống kê tiền và trả lương cho giảng viên, admin là quyền cao nhất.
            $table->boolean('del_flag');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
