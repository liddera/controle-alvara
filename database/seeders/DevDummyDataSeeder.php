<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Empresa;
use App\Models\Alvara;
use App\Models\Plan;
use App\Models\TipoAlvara;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevDummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Generates fake data for development and testing.
     */
    public function run(): void
    {
        // 1. Client Owner (Account Holder) - Fixed Email for Easy Login
        $owner = User::factory()->create([
            'name' => 'Eliel Diniz',
            'email' => 'eliel@alvras.com',
            'password' => Hash::make('password'),
            'plan_id' => Plan::where('nome', 'Plano Intermediário')->first()?->id ?? null,
        ]);
        $owner->assignRole('owner');
        $owner->owner_id = $owner->id;
        $owner->save();

        // 2. Team Member
        $member = User::factory()->create([
            'name' => 'Membro da Equipe',
            'email' => 'membro@alvras.com',
            'password' => Hash::make('password'),
            'parent_id' => $owner->id,
            'owner_id' => $owner->id,
            'plan_id' => $owner->plan_id,
        ]);
        $member->assignRole('member');

        // 3. Companies for the owner
        $empresas = Empresa::factory()->count(3)->create([
            'user_id' => $owner->id,
            'owner_id' => $owner->id,
        ]);

        $tipoPadrao = TipoAlvara::first();

        // 4. Alvarás for each company
        foreach ($empresas as $empresa) {
            Alvara::factory()->count(rand(2, 5))->create([
                'empresa_id' => $empresa->id,
                'user_id' => $owner->id,
                'owner_id' => $owner->id,
                'tipo_alvara_id' => $tipoPadrao?->id,
            ]);
            
            // Associate random types (Pivot)
            $empresa->tiposAlvara()->attach(TipoAlvara::limit(2)->pluck('id'));
        }
        
        // 5. Default Alert Configurations
        $owner->alertConfigs()->createMany([
            ['days_before' => 30, 'is_active' => true],
            ['days_before' => 15, 'is_active' => true],
            ['days_before' => 7, 'is_active' => true],
        ]);
    }
}
