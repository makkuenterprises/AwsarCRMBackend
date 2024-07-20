<?php
// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// class CreateExamResponsesTable extends Migration
// {
//     public function up()
//     {
//         Schema::create('exam_responses', function (Blueprint $table) {
//             $table->id();
//             $table->unsignedBigInteger('exam_id');
//             $table->unsignedBigInteger('student_id');
//             $table->float('total_marks', 8, 2)->nullable(); // Total marks available for the exam
//             $table->float('gained_marks', 8, 2)->nullable(); // Marks gained by the student
//             $table->float('passing_marks', 8, 2)->nullable(); // Passing marks for the exam
//             $table->float('negative_marks', 8, 2)->nullable(); // Negative marks
//             $table->integer('total_correct_answers')->default(0); // Total number of correct answers
//             $table->integer('total_wrong_answers')->default(0); // Total number of wrong answers
//             $table->integer('total_question')->default(0); // Total number of wrong answers
//             $table->timestamps();
//             $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
//             $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
//         });
//     }

//     public function down()
//     {
//         Schema::dropIfExists('exam_responses');
//     }
// }
