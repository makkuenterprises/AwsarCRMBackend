<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
              $table->unsignedBigInteger('exam_id'); 
            $table->string('name');
            $table->timestamps();

          $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('sections');
    }
};