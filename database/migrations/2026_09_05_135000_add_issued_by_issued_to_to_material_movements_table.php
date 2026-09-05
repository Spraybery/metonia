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
            $table->string('issued_by')->nullable()->after('person');
            $table->string('issued_to')->nullable()->after('issued_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_movements', function (Blueprint $table) {
            $table->dropColumn(['issued_by', 'issued_to']);
        });
    }
};
