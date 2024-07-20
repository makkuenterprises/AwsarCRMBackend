<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamQuestionResponsesTable extends Migration
{
    public function up()
    {
        Schema::create('exam_question_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_response_id');
            $table->unsignedBigInteger('question_id');
            $table->text('response')->nullable(); // Store response as text
            $table->float('marks', 8, 2)->nullable(); // Marks for the response
            $table->float('negative_marks', 8, 2)->nullable(); // Negative marks for the response
            $table->timestamps();

            $table->foreign('exam_response_id')->references('id')->on('exam_responses')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('exam_question_responses');
    }
}
