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
        Schema::table('details', function (Blueprint $table) {
            // Drop existing column
            $table->dropColumn('smtp_ports');

            // Add new column with string data type
            $table->string('smtp_ports')->nullable(); // Modify as per your requirements
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('details', function (Blueprint $table) {
            // Drop the string column
            $table->dropColumn('smtp_ports');

            // Add new column with JSON data type
            $table->json('smtp_ports')->nullable(); // Modify as per your requirements
        });
    }
};
