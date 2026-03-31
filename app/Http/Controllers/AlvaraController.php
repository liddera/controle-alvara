<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alvara;
use App\Models\Empresa;
use App\Models\Documento;
use App\DTOs\AlvaraFilterDTO;
use App\Http\Requests\FilterAlvaraRequest;
use App\Http\Requests\StoreAlvaraRequest;
use App\Http\Requests\UpdateAlvaraRequest;
use App\Http\Requests\UpdateAlvaraObservacoesRequest;
use App\Actions\Alvaras\CriarAlvaraAction;
use App\Actions\Alvaras\AtualizarAlvaraAction;
use App\Actions\Alvaras\ExcluirAlvaraAction;
use App\Actions\Alvaras\UploadDocumentosAction;
use App\Services\DocumentoService;
use Illuminate\Support\Facades\Storage;

class AlvaraController extends Controller
{
    public function index(FilterAlvaraRequest $request)
    {
        $dto = AlvaraFilterDTO::fromRequest($request);

        $alvaras = Alvara::with([
            'empresa', 
            'tipoAlvara', 
            'notificacoes' => fn($q) => $q->where('tipo', 'envio_documento')->latest(),
            'documentDispatches' => fn($q) => $q->latest(),
            'documentDispatches.messages',
            'documentDispatches.messages.events',
        ])
            ->filterByDto($dto)
            ->latest()
            ->paginate(10);

        $empresas = Empresa::all();
        $tiposAlvara = \App\Models\TipoAlvara::all();
        $status = $dto->status === 'todos' ? '' : $dto->status;
        $empresa_id = $dto->empresa_id;
        $tipo_slug = $dto->tipo_slug;
        $tipoSelecionadoNome = $tipo_slug
            ? $tiposAlvara->firstWhere('slug', $tipo_slug)?->nome ?? $tipo_slug
            : null;
        $vencimento_de = $dto->vencimento_de;
        $vencimento_ate = $dto->vencimento_ate;

        return view('alvaras.index', compact(
            'alvaras',
            'empresas',
            'tiposAlvara',
            'status',
            'empresa_id',
            'tipo_slug',
            'tipoSelecionadoNome',
            'vencimento_de',
            'vencimento_ate'
        ));
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

    public function updateObservacoes(UpdateAlvaraObservacoesRequest $request, Alvara $alvara)
    {
        $alvara->update([
            'observacoes' => $request->validated('observacoes'),
        ]);

        return response()->json([
            'success' => true,
            'observacoes' => $alvara->observacoes,
        ]);
    }

    public function enviarEmail(Request $request, Alvara $alvara, \App\Actions\Alvaras\EnviarAlvaraPorEmailAction $action)
    {
        $request->validate([
            'email' => 'required|email',
            'nome' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'enviar_aviso_whatsapp' => 'nullable|boolean',
            'mensagem' => 'nullable|string',
        ]);

        try {
            $notificacao = $action->execute($alvara, $request->only(['nome', 'email', 'telefone', 'enviar_aviso_whatsapp', 'mensagem']));
            $msg = json_decode($notificacao->mensagem, true) ?? [];
            $metodo = ($msg['whatsapp_aviso'] ?? false) ? 'email+whatsapp' : 'email';
            $dispatchId = $msg['dispatch_id'] ?? null;
            $dispatchStatus = $msg['dispatch_status'] ?? null;
            
            return response()->json([
                'success' => true,
                'message' => 'Envio iniciado! O e-mail será enviado e, se marcado, o aviso no WhatsApp será disparado após o envio do e-mail.',
                'historico' => [
                    'id' => $notificacao->id,
                    'data' => $notificacao->created_at->format('d/m/Y H:i'),
                    'ts' => $notificacao->created_at->timestamp,
                    'destinatario' => $msg['destinatario_nome'] ?? 'Desconhecido',
                    'metodo' => $metodo,
                    'status' => $dispatchStatus,
                    'dispatch_id' => $dispatchId,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function enviarWhatsApp(Request $request, Alvara $alvara, \App\Actions\Alvaras\EnviarAlvaraPorWhatsAppAction $action)
    {
        $request->validate([
            'telefone' => 'required|string|max:20',
            'nome' => 'nullable|string|max:255',
            'mensagem' => 'nullable|string',
        ]);

        try {
            $notificacao = $action->execute($alvara, $request->only(['nome', 'telefone', 'mensagem']));
            $msg = json_decode($notificacao->mensagem, true) ?? [];

            return response()->json([
                'success' => true,
                'message' => 'Envio iniciado! As mensagens serao enviadas pelo WhatsApp.',
                'historico' => [
                    'id' => $notificacao->id,
                    'data' => $notificacao->created_at->format('d/m/Y H:i'),
                    'ts' => $notificacao->created_at->timestamp,
                    'destinatario' => $msg['destinatario_nome'] ?? 'Desconhecido',
                    'metodo' => 'whatsapp',
                    'status' => $msg['dispatch_status'] ?? null,
                    'dispatch_id' => $msg['dispatch_id'] ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
