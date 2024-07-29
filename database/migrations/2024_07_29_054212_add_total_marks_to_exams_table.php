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
    Schema::table('exams', function (Blueprint $table) {
        $table->float('total_marks', 8, 2)->nullable()->after('passing_marks');
    });
}

public function down(): void
{
    Schema::table('exams', function (Blueprint $table) {
        $table->dropColumn('total_marks');
    });
}

};
