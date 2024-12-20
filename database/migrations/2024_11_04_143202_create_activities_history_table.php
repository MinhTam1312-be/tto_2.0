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
        Schema::create('activities_history', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name_activity');
            $table->text('discription_activity');
            $table->enum('status_activity', ['confirming', 'success', 'fail']);
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
        Schema::dropIfExists('activities_history');
    }
};
