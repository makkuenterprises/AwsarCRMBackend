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
        Schema::create('details', function (Blueprint $table) {
            $table->id();
            $table->string('logo')->nullable();
            $table->string('side_logo')->nullable();
            $table->string('favicon_icon')->nullable();
            $table->string('business_name')->nullable();
            $table->string('email')->nullable();
            $table->string('smtp_host')->nullable();
            $table->string('smtp_ports')->nullable(); // JSON column for storing multiple SMTP ports
            $table->string('smtp_username')->nullable();
            $table->string('smtp_password')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('details');
    }
};
