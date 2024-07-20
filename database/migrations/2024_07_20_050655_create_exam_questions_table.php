<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('exam_questions', function (Blueprint $table) {
           $table->id();
           $table->unsignedBigInteger('exam_id'); 
           $table->unsignedBigInteger('question_id'); 
           $table->unsignedBigInteger('section_id'); 
           $table->float('marks');
            $table->float('negative_marks')->nullable();
            $table->timestamps();
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
        });

    }

    public function down()
    {
        Schema::dropIfExists('exam_questions');
    }
};