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
use App\Actions\Alvaras\UploadDocumentosAction;
use App\Services\DocumentoService;
use Illuminate\Support\Facades\Storage;

class AlvaraController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $empresa_id = $request->get('empresa_id');
        $tipo_slug = $request->get('tipo');

        $alvaras = Alvara::with([
            'empresa', 
            'tipoAlvara', 
            'notificacoes' => fn($q) => $q->where('tipo', 'envio_documento')->latest()
        ])
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

    public function store(StoreAlvaraRequest $request, CriarAlvaraAction $action, UploadDocumentosAction $uploadAction)
    {
        $alvara = $action->execute($request);

        $uploadAction->execute($alvara, $request);

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

    public function update(UpdateAlvaraRequest $request, Alvara $alvara, AtualizarAlvaraAction $action, UploadDocumentosAction $uploadAction)
    {
        $action->execute($alvara, $request);

        $uploadAction->execute($alvara, $request);

        return redirect()->route('alvaras.show', $alvara)
            ->with('success', 'Alvará atualizado com sucesso!');
    }

    public function destroyDocumento(Documento $documento, DocumentoService $service)
    {
        $service->delete($documento);
        return back()->with('success', 'Documento removido.');
    }

    public function destroy(Alvara $alvara, ExcluirAlvaraAction $action, DocumentoService $service)
    {
        // Remove documentos do storage
        foreach ($alvara->documentos as $doc) {
            $service->delete($doc);
        }
        $action->execute($alvara);
        return redirect()->route('alvaras.index')->with('success', 'Alvará removido com sucesso!');
    }

    public function enviarEmail(Request $request, Alvara $alvara, \App\Actions\Alvaras\EnviarAlvaraPorEmailAction $action)
    {
        $request->validate([
            'email' => 'required|email',
            'nome' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'mensagem' => 'nullable|string',
            'metodo' => 'nullable|string|in:email,whatsapp'
        ]);

        try {
            $notificacao = $action->execute($alvara, $request->only(['nome', 'email', 'telefone', 'mensagem', 'metodo']));
            
            return response()->json([
                'success' => true,
                'message' => 'Alvará enviado por email com sucesso!',
                'historico' => [
                    'id' => $notificacao->id,
                    'data' => $notificacao->created_at->format('d/m/Y H:i'),
                    'destinatario' => json_decode($notificacao->mensagem)->destinatario_nome ?? 'Desconhecido',
                    'metodo' => json_decode($notificacao->mensagem)->metodo ?? 'email',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
