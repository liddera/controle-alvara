<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Empresa;
use App\Models\Alvara;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criação de um usuário fixo para testes fáceis
        $user = User::factory()->create([
            'name' => 'Eliel Diniz',
            'email' => 'admin@alvras.com',
            'password' => Hash::make('password'),
        ]);

        // Criar Empresas para o usuário principal
        $empresas = Empresa::factory(3)->create([
            'user_id' => $user->id,
        ]);

        // Para cada empresa criar alguns Alvarás
        foreach ($empresas as $empresa) {
            Alvara::factory(rand(2, 5))->create([
                'empresa_id' => $empresa->id,
                'user_id' => $user->id,
            ]);
        }
        
        // Configurações de alerta default para o usuário admin
        $user->alertConfigs()->createMany([
            ['dias_antes' => 30, 'tipo' => 'sistema'],
            ['dias_antes' => 15, 'tipo' => 'email'],
            ['dias_antes' => 7, 'tipo' => 'whatsapp'],
        ]);
    }
}
