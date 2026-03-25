<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoAlvaraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\TipoAlvara::updateOrCreate(['slug' => 'sanitario'], ['nome' => 'Alvará Sanitário']);
        \App\Models\TipoAlvara::updateOrCreate(['slug' => 'funcionamento'], ['nome' => 'Alvará de Funcionamento']);
        \App\Models\TipoAlvara::updateOrCreate(['slug' => 'bombeiros'], ['nome' => 'Alvará do Corpo de Bombeiros']);
        \App\Models\TipoAlvara::updateOrCreate(['slug' => 'policia'], ['nome' => 'Alvará da Polícia Civil']);
    }
}
