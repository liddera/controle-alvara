<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Alvara>
 */
class AlvaraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vencimento = fake()->dateTimeBetween('-1 month', '+1 year');
        $status = 'vigente';
        
        if ($vencimento < now()) {
            $status = 'vencido';
        } elseif ($vencimento <= now()->addDays(30)) {
            $status = 'proximo';
        }

        return [
            'empresa_id' => Empresa::factory(),
            'user_id' => User::factory(),
            'tipo' => fake()->randomElement(['Alvará de Funcionamento', 'Alvará Sanitário', 'Alvará dos Bombeiros']),
            'numero' => fake()->numerify('ALV-####/2026'),
            'data_emissao' => fake()->dateTimeBetween('-1 year', 'now'),
            'data_vencimento' => $vencimento,
            'status' => $status,
            'observacoes' => fake()->optional()->sentence(),
        ];
    }
}
