<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Http\Requests\StoreEmpresaRequest;
use App\Http\Requests\UpdateEmpresaRequest;
use App\Actions\Empresas\CriarEmpresaAction;
use App\Actions\Empresas\AtualizarEmpresaAction;
use App\Actions\Empresas\ExcluirEmpresaAction;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        $empresas = Empresa::withCount('alvaras')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('empresas.index', compact('empresas'));
    }

    public function create()
    {
        return view('empresas.create');
    }

    public function store(StoreEmpresaRequest $request, CriarEmpresaAction $action)
    {
        $action->execute($request);
        return redirect()->route('empresas.index')->with('success', 'Empresa cadastrada com sucesso!');
    }

    public function show(Empresa $empresa)
    {
        $empresa->load('alvaras');
        return view('empresas.show', compact('empresa'));
    }

    public function edit(Empresa $empresa)
    {
        return view('empresas.edit', compact('empresa'));
    }

    public function update(UpdateEmpresaRequest $request, Empresa $empresa, AtualizarEmpresaAction $action)
    {
        $action->execute($empresa, $request);
        return redirect()->route('empresas.index')->with('success', 'Empresa atualizada com sucesso!');
    }

    public function destroy(Empresa $empresa, ExcluirEmpresaAction $action)
    {
        $action->execute($empresa);
        return redirect()->route('empresas.index')->with('success', 'Empresa removida com sucesso!');
    }
}
