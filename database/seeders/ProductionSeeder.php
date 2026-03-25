<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Essential data for the application to function in production.
     */
    public function run(): void
    {
        // 1. Essential Reference Data
        $this->call([
            RolePermissionSeeder::class ,
            PlanSeeder::class ,
            TipoAlvaraSeeder::class ,
        ]);

        // 2. Create the Super Admin User (Official Credentials)
        // Using updateOrCreate instead of factory to avoid faker and handle repeat runs
        $superAdmin = User::updateOrCreate(
        ['email' => 'Elieldiniz1@outlook.com'],
        [
            'name' => 'Eliel Diniz',
            'password' => Hash::make('@diniz0012'),
            'is_active' => true,
        ]
        );

        // Ensure the role is assigned
        if (!$superAdmin->hasRole('super-admin')) {
            $superAdmin->assignRole('super-admin');
        }
    }
}