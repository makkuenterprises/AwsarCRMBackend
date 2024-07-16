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
        //
        Schema::create('course_teacher', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('course_id');
    $table->unsignedBigInteger('teacher_id');
    $table->timestamps();

    $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
    $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
