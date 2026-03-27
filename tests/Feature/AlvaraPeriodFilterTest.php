<?php

namespace Tests\Feature;

use App\Models\Alvara;
use App\Models\Empresa;
use App\Models\TipoAlvara;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlvaraPeriodFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_filters_alvaras_by_vencimento_period(): void
    {
        $user = User::factory()->create();

        $tipo = TipoAlvara::create([
            'nome' => 'Alvara de Funcionamento',
            'slug' => 'alvara-funcionamento',
        ]);

        $empresa = Empresa::create([
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'nome' => 'Empresa Periodo',
            'cnpj' => '11.111.111/0001-11',
            'responsavel' => 'Teste',
            'telefone' => '69999999999',
            'email' => 'contato@periodo.com',
        ]);

        Alvara::create([
            'empresa_id' => $empresa->id,
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'tipo_alvara_id' => $tipo->id,
            'tipo' => $tipo->nome,
            'numero' => 'ALV-IN',
            'data_vencimento' => '2026-03-15',
            'status' => 'proximo',
        ]);

        Alvara::create([
            'empresa_id' => $empresa->id,
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'tipo_alvara_id' => $tipo->id,
            'tipo' => $tipo->nome,
            'numero' => 'ALV-OUT',
            'data_vencimento' => '2026-04-20',
            'status' => 'vigente',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', [
            'vencimento_de' => '2026-03-01',
            'vencimento_ate' => '2026-03-31',
        ]));

        $response->assertOk();
        $response->assertSee('15/03/2026');
        $response->assertDontSee('20/04/2026');
    }

    public function test_alvaras_index_filters_alvaras_by_vencimento_period(): void
    {
        $user = User::factory()->create();

        $tipo = TipoAlvara::create([
            'nome' => 'Alvara Sanitario',
            'slug' => 'alvara-sanitario',
        ]);

        $empresa = Empresa::create([
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'nome' => 'Empresa Filtro',
            'cnpj' => '22.222.222/0001-22',
            'responsavel' => 'Teste',
            'telefone' => '69999999999',
            'email' => 'contato@filtro.com',
        ]);

        Alvara::create([
            'empresa_id' => $empresa->id,
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'tipo_alvara_id' => $tipo->id,
            'tipo' => $tipo->nome,
            'numero' => 'ALV-15',
            'data_vencimento' => '2026-05-15',
            'status' => 'vigente',
        ]);

        Alvara::create([
            'empresa_id' => $empresa->id,
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'tipo_alvara_id' => $tipo->id,
            'tipo' => $tipo->nome,
            'numero' => 'ALV-28',
            'data_vencimento' => '2026-06-28',
            'status' => 'vigente',
        ]);

        $response = $this->actingAs($user)->get(route('alvaras.index', [
            'vencimento_de' => '2026-05-01',
            'vencimento_ate' => '2026-05-31',
        ]));

        $response->assertOk();
        $response->assertSee('15/05/2026');
        $response->assertDontSee('28/06/2026');
    }

    public function test_dashboard_status_filter_does_not_change_summary_cards(): void
    {
        $user = User::factory()->create();

        $tipo = TipoAlvara::create([
            'nome' => 'Alvara de Funcionamento',
            'slug' => 'alvara-funcionamento',
        ]);

        $empresa = Empresa::create([
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'nome' => 'Empresa Cards',
            'cnpj' => '33.333.333/0001-33',
            'responsavel' => 'Teste',
            'telefone' => '69999999999',
            'email' => 'contato@cards.com',
        ]);

        Alvara::create([
            'empresa_id' => $empresa->id,
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'tipo_alvara_id' => $tipo->id,
            'tipo' => $tipo->nome,
            'numero' => 'ALV-ATIVO',
            'data_vencimento' => now()->addDays(60)->toDateString(),
            'status' => 'vigente',
        ]);

        Alvara::create([
            'empresa_id' => $empresa->id,
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'tipo_alvara_id' => $tipo->id,
            'tipo' => $tipo->nome,
            'numero' => 'ALV-RENOVA',
            'data_vencimento' => now()->addDays(10)->toDateString(),
            'status' => 'proximo',
        ]);

        Alvara::create([
            'empresa_id' => $empresa->id,
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'tipo_alvara_id' => $tipo->id,
            'tipo' => $tipo->nome,
            'numero' => 'ALV-VENCIDO',
            'data_vencimento' => now()->subDays(5)->toDateString(),
            'status' => 'vencido',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', [
            'status' => 'vigente',
        ]));

        $response->assertOk();
        $response->assertSee('ALV-ATIVO');
        $response->assertDontSee('ALV-RENOVA');
        $response->assertDontSee('ALV-VENCIDO');
        $response->assertSee('>3<', false);
        $response->assertSee('>1<', false);
    }
}
