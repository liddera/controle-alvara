<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Alvara;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Buscando empresas do usuário
        $empresas = $user->empresas;
        
        // Empresa selecionada
        $empresaId = $request->get('empresa_id', $empresas->first()->id ?? null);
        $empresaSelecionada = $empresas->where('id', $empresaId)->first();
        
        // Carregando Alvarás da empresa selecionada (paginados ou todos, vamos usar get pra UI simples)
        $alvaras = collect();
        if ($empresaSelecionada) {
            $alvaras = Alvara::where('empresa_id', $empresaId)->get();
        }

        // Estatísticas
        $stats = [
            'total' => $alvaras->count(),
            'ativos' => $alvaras->where('status', 'vigente')->count(),
            'em_renovacao' => $alvaras->where('status', 'proximo')->count(),
            'vencidos' => $alvaras->where('status', 'vencido')->count(),
        ];
        
        return view('dashboard', compact('empresas', 'empresaSelecionada', 'alvaras', 'stats'));
    }
}
