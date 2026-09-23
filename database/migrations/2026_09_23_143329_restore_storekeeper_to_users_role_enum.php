<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A prior migration dropped "Storekeeper" from the MySQL role enum in
     * favor of "Shopkeeper", but "Storekeeper" is the role name the app
     * actually uses. Restore it (keeping "Shopkeeper" too, since existing
     * rows and the app's legacy-synonym checks already accept it).
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('Admin', 'Manager', 'Storekeeper', 'Shopkeeper', 'Accountant') NOT NULL DEFAULT 'Manager'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('Admin', 'Manager', 'Shopkeeper', 'Accountant') NOT NULL DEFAULT 'Manager'");
        }
    }
};
