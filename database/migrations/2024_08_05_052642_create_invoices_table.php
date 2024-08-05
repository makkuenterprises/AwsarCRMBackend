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
        Schema::create('invoices', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('enrollment_id');
        $table->unsignedBigInteger('student_id');
        $table->unsignedBigInteger('course_id');
        $table->string('invoice_no')->unique();
        $table->string('student_name');
        $table->string('course_name');
        $table->decimal('total_amount', 8, 2); // Total fee for the course
        $table->decimal('paid_amount', 8, 2);  // Amount paid
        $table->decimal('remaining_amount', 8, 2); // Remaining amount to be paid
        $table->date('invoice_date');
        $table->timestamps(); 

        $table->foreign('enrollment_id')->references('id')->on('courses_enrollements')->onDelete('cascade');
        $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
