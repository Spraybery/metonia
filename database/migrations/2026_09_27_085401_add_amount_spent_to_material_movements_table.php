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
        Schema::table('material_movements', function (Blueprint $table) {
            $table->decimal('amount_spent', 12, 2)->nullable()->after('unit_cost');
            $table->string('amount_spent_recorded_by')->nullable()->after('amount_spent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_movements', function (Blueprint $table) {
            $table->dropColumn(['amount_spent', 'amount_spent_recorded_by']);
        });
    }
};
