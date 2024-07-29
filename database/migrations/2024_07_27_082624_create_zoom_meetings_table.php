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
        Schema::create('zoom_meetings', function (Blueprint $table) {
              $table->id();
            $table->string('uuid')->nullable();
            $table->bigInteger('meeting_id')->unique();
            $table->string('host_id');
            $table->string('host_email');
            $table->string('topic');
            $table->integer('type');
            $table->string('status');
            $table->timestamp('start_time');
            $table->integer('duration');
            $table->string('timezone');
            $table->string('agenda')->nullable();
            $table->string('start_url')->nullable();
            $table->string('join_url')->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoom_meetings');
    }
};
