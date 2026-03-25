<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Empresa;
use App\Models\Alvara;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Always run Production Seeder (Official Data)
        $this->call(ProductionSeeder::class);

        // 2. Only run Dev Seeder if in local environment (Dummy Data)
        // This avoids the 'fake()' undefined function error in production (Require-Dev)
        if (app()->isLocal()) {
            $this->call(DevDummyDataSeeder::class);
        }
    }
}