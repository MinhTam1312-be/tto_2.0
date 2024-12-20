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
        Schema::create('modules', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('route_id');
            $table->ulid('course_id');
            $table->boolean('del_flag');
            $table->timestamps();
            $table->foreign('route_id')
                ->references('id')
                ->on('routes')
                ->onDelete('restrict');
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
        Schema::dropIfExists('modules');
    }
};
