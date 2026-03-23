<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Http\Requests\StoreEmpresaRequest;
use App\Http\Requests\UpdateEmpresaRequest;
use App\Http\Resources\EmpresaResource;
use App\Actions\Empresas\CriarEmpresaAction;
use App\Actions\Empresas\AtualizarEmpresaAction;
use App\Actions\Empresas\ExcluirEmpresaAction;

class EmpresaController extends Controller
{
    public function index()
    {
        $empresas = Empresa::withCount('alvaras')
            ->where('user_id', auth()->id())
            ->paginate(15);

        return EmpresaResource::collection($empresas);
    }

    public function store(StoreEmpresaRequest $request, CriarEmpresaAction $action)
    {
        $empresa = $action->execute($request);
        return new EmpresaResource($empresa);
    }

    public function show(Empresa $empresa)
    {
        return new EmpresaResource($empresa->loadCount('alvaras'));
    }

    public function update(UpdateEmpresaRequest $request, Empresa $empresa, AtualizarEmpresaAction $action)
    {
        $empresa = $action->execute($empresa, $request);
        return new EmpresaResource($empresa);
    }

    public function destroy(Empresa $empresa, ExcluirEmpresaAction $action)
    {
        $action->execute($empresa);
        return response()->noContent();
    }
}
