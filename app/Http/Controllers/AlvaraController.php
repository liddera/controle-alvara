<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alvara;
use App\Models\Empresa;
use App\Models\Documento;
use App\Http\Requests\StoreAlvaraRequest;
use App\Http\Requests\UpdateAlvaraRequest;
use App\Actions\Alvaras\CriarAlvaraAction;
use App\Actions\Alvaras\AtualizarAlvaraAction;
use App\Actions\Alvaras\ExcluirAlvaraAction;
use Illuminate\Support\Facades\Storage;

class AlvaraController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $empresa_id = $request->get('empresa_id');
        $tipo_slug = $request->get('tipo');

        $alvaras = Alvara::with(['empresa', 'tipoAlvara'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($empresa_id, fn ($q) => $q->where('empresa_id', $empresa_id))
            ->when($tipo_slug, function ($q) use ($tipo_slug) {
                $q->whereHas('tipoAlvara', fn ($query) => $query->where('slug', $tipo_slug));
            })
            ->latest()
            ->paginate(10);

        $empresas = Empresa::all();
        $tiposAlvara = \App\Models\TipoAlvara::all();

        return view('alvaras.index', compact('alvaras', 'empresas', 'tiposAlvara', 'status', 'empresa_id', 'tipo_slug'));
    }

    public function create(Request $request)
    {
        $empresas = Empresa::all();
        $tiposAlvara = \App\Models\TipoAlvara::all();
        $empresaSelecionada = $request->get('empresa_id');
        $tipoSelecionado = $request->get('tipo'); // Slug do tipo se vier do sidebar
        
        return view('alvaras.create', compact('empresas', 'tiposAlvara', 'empresaSelecionada', 'tipoSelecionado'));
    }

    public function store(StoreAlvaraRequest $request, CriarAlvaraAction $action)
    {
        $alvara = $action->execute($request);

        // Upload de documentos
        if ($request->hasFile('documentos')) {
            foreach ($request->file('documentos') as $arquivo) {
                $caminho = $arquivo->store('documentos/' . $alvara->id, 'public');
                Documento::create([
                    'alvara_id' => $alvara->id,
                    'nome_arquivo' => $arquivo->getClientOriginalName(),
                    'caminho' => $caminho,
                    'tipo' => $arquivo->getMimeType(),
                    'tamanho' => $arquivo->getSize(),
                ]);
            }
        }

        return redirect()->route('alvaras.show', $alvara)
            ->with('success', 'Alvará cadastrado com sucesso!');
    }

    public function show(Alvara $alvara)
    {
        $alvara->load(['empresa', 'documentos']);
        return view('alvaras.show', compact('alvara'));
    }

    public function edit(Alvara $alvara, Request $request)
    {
        $empresas = Empresa::all();
        $tiposAlvara = \App\Models\TipoAlvara::all();
        return view('alvaras.edit', compact('alvara', 'empresas', 'tiposAlvara'));
    }

    public function update(UpdateAlvaraRequest $request, Alvara $alvara, AtualizarAlvaraAction $action)
    {
        $action->execute($alvara, $request);

        // Upload de novos documentos
        if ($request->hasFile('documentos')) {
            foreach ($request->file('documentos') as $arquivo) {
                $caminho = $arquivo->store('documentos/' . $alvara->id, 'public');
                Documento::create([
                    'alvara_id' => $alvara->id,
                    'nome_arquivo' => $arquivo->getClientOriginalName(),
                    'caminho' => $caminho,
                    'tipo' => $arquivo->getMimeType(),
                    'tamanho' => $arquivo->getSize(),
                ]);
            }
        }

        return redirect()->route('alvaras.show', $alvara)
            ->with('success', 'Alvará atualizado com sucesso!');
    }

    public function destroyDocumento(Documento $documento)
    {
        Storage::disk('public')->delete($documento->caminho);
        $documento->delete();
        return back()->with('success', 'Documento removido.');
    }

    public function destroy(Alvara $alvara, ExcluirAlvaraAction $action)
    {
        // Remove documentos do storage
        foreach ($alvara->documentos as $doc) {
            Storage::disk('public')->delete($doc->caminho);
        }
        $action->execute($alvara);
        return redirect()->route('alvaras.index')->with('success', 'Alvará removido com sucesso!');
    }
}
