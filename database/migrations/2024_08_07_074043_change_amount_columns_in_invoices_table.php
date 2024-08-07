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
        Schema::table('invoices', function (Blueprint $table) {
            //
            $table->string('total_amount')->change();
            $table->string('paid_amount')->change();
            $table->string('remaining_amount')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            //
             $table->decimal('total_amount', 8, 2)->change();
            $table->decimal('paid_amount', 8, 2)->change();
            $table->decimal('remaining_amount', 8, 2)->change();
        });
    }
};
