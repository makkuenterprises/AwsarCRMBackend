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
        Schema::table('slides_images', function (Blueprint $table) {
            //
            $table->string('role')->nullable();// To store the image title

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slides_images', function (Blueprint $table) {
            //
             $table->dropColumn('role');
        });
    }
};
