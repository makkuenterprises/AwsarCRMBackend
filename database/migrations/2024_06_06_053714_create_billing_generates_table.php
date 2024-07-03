<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.s
     */
    public function up(): void
    {
    Schema::create('billing_generates', function (Blueprint $table) {
    $table->id(); // Auto-incrementing primary key
    $table->string('studentid'); // Column for student ID
    $table->string('studentName'); // Column for student name
    $table->string('courseid'); // Column for course ID
    $table->string('courseName'); // Column for course name
    $table->string('invoiceNo'); // Column for invoice number
    $table->string('invoiceDate'); // Column for invoice date
    $table->decimal('amount', 12, 2); // Column for the amount billed
    $table->string('paymentMethod')->nullable(); // Column for the payment method used, nullable
    $table->string('status')->default('active'); // Column for status with default value 'active'
    $table->text('remarks')->nullable(); // Column for any additional remarks or notes, nullable
    $table->string('createdBy'); // Foreign key constraint linking to the users table
    $table->timestamps(); // Columns for created_at and updated_at timestamps
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_generates');
    }
};
