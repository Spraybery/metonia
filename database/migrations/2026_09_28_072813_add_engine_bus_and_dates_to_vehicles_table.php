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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('engine_no')->nullable()->after('year');
            $table->string('bus_category')->nullable()->after('engine_no');
            $table->date('date_out')->nullable()->after('intake_date');
            $table->date('delivery_date')->nullable()->after('date_out');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['engine_no', 'bus_category', 'date_out', 'delivery_date']);
        });
    }
};
