<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empresa>
 */
class EmpresaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nome' => fake()->company(),
            'cnpj' => fake()->numerify('##.###.###/0001-##'),
            'responsavel' => fake()->name(),
            'telefone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
        ];
    }
}
