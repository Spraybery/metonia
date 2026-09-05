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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate')->unique();
            $table->string('make')->default('Metonia');
            $table->string('model');
            $table->string('year', 4)->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->enum('stage', [
                '1. Intake & Diagnosis',
                '2. Structural & Frame',
                '3. Powertrain & Mechanical',
                '4. Electrical & Harness',
                '5. Bodywork & Spray Paint',
                '6. Interior & Glass Fit',
                '7. Quality & Road Test',
                '8. Completed & Dispatched',
            ])->default('1. Intake & Diagnosis');
            $table->string('assigned_to')->nullable();
            $table->timestamp('intake_date')->useCurrent();
            $table->text('notes')->nullable();
            $table->unsignedInteger('checklist_done')->default(0);
            $table->unsignedInteger('checklist_total')->default(3);
            $table->decimal('labor_cost', 12, 2)->default(0.00);
            $table->decimal('invoice_total', 12, 2)->default(0.00);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('stage', 'idx_vehicles_stage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
