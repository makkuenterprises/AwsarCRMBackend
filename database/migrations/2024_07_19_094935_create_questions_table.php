<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. 
     */
 public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID
            $table->text('question_text');
            $table->enum('question_type', ['MCQ', 'Short Answer', 'Fill in the Blanks']);
            $table->json('options')->nullable(); // For MCQ options
            $table->json('correct_answers')->nullable(); // For MCQ multiple correct answers
            $table->string('image')->nullable(); // Path to the image
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('questions');
    }
};
 