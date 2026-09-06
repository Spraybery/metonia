<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Core RBAC System Accounts
        $users = [
            [
                'name' => 'Eng. Martin Kariuki',
                'username' => 'admin',
                'email' => 'admin@metonia.co.ke',
                'password' => Hash::make('password'),
                'role' => 'Admin',
            ],
            [
                'name' => 'Grace Nduta',
                'username' => 'manager',
                'email' => 'manager@metonia.co.ke',
                'password' => Hash::make('password'),
                'role' => 'Manager',
            ],
            [
                'name' => 'David Omondi',
                'username' => 'shopkeeper',
                'email' => 'shopkeeper@metonia.co.ke',
                'password' => Hash::make('password'),
                'role' => 'Shopkeeper',
            ],
            [
                'name' => 'Alice Wambui',
                'username' => 'accountant',
                'email' => 'accountant@metonia.co.ke',
                'password' => Hash::make('password'),
                'role' => 'Accountant',
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['username' => $u['username']], $u);
        }
    }
}
