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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->bigIncrements('id')->from(100001);
            $table->foreignId('teacher_id')->nullable()->references('id')->on('teachers');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->longText('message')->nullable();
            $table->enum('status',['APPROVED','PENDING','DENIED'])->default('PENDING');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
