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
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag')->unique();
            $table->string('name');
            $table->enum('category', [
                'Pneumatic Tools',
                'Torque & Calibration Gauges',
                'Welding & Plasma Cutters',
                'Lifts & Hydraulics',
                'Diagnostic Scanners',
            ]);
            $table->string('brand')->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['Available', 'Checked Out', 'In Maintenance'])->default('Available');
            $table->string('assigned_to')->nullable();
            $table->date('next_calibration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
