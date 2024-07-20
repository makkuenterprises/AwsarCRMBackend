<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamp('start_time');
            $table->timestamp('end_time');
             $table->unsignedBigInteger('batch_id'); 
            $table->float('passing_marks');
            $table->timestamps();

               $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('exams');
    }
};
