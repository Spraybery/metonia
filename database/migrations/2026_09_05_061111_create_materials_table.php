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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', [
                'Consumables',
                'Metals',
                'Aluminium',
                'Bolts & Fasteners',
                'Rubbers',
                'Fibreglass',
                'Reflecting & Safety',
            ]);
            $table->enum('unit', ['Pieces', 'Rolls', 'Liters', 'Kilograms']);
            $table->decimal('qty', 10, 2)->default(0.00);
            $table->decimal('low_stock', 10, 2)->default(0.00);
            $table->decimal('unit_cost', 12, 2)->default(0.00);
            $table->string('supplier')->nullable();
            $table->timestamps();

            $table->index('category', 'idx_materials_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
