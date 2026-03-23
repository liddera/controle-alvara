<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alvara;
use Illuminate\Http\Request;
use App\Http\Requests\StoreAlvaraRequest;
use App\Http\Requests\UpdateAlvaraRequest;
use App\Http\Resources\AlvaraResource;
use App\Actions\Alvaras\CriarAlvaraAction;
use App\Actions\Alvaras\AtualizarAlvaraAction;
use App\Actions\Alvaras\ExcluirAlvaraAction;

class AlvaraController extends Controller
{
    public function index()
    {
        $alvaras = Alvara::with('empresa')->paginate(15);
        
        return AlvaraResource::collection($alvaras);
    }

    public function store(StoreAlvaraRequest $request, CriarAlvaraAction $action)
    {
        $alvara = $action->execute($request);

        return new AlvaraResource($alvara);
    }

    public function show(Alvara $alvara)
    {
        return new AlvaraResource($alvara);
    }

    public function update(UpdateAlvaraRequest $request, Alvara $alvara, AtualizarAlvaraAction $action)
    {
        $alvara = $action->execute($alvara, $request);

        return new AlvaraResource($alvara);
    }

    public function destroy(Alvara $alvara, ExcluirAlvaraAction $action)
    {
        $action->execute($alvara);

        return response()->noContent();
    }
}
