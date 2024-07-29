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
        Schema::table('zoom_meetings', function (Blueprint $table) {
            $table->string('start_url', 2048)->change();
            $table->string('join_url', 2048)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zoom_meetings', function (Blueprint $table) {
            //
              $table->string('start_url')->change();
            $table->string('join_url')->change();
        });
    }
};
