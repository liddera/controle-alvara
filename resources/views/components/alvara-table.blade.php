@props(['alvaras'])

<div x-data="alvaraSendModal()" class="bg-white overflow-hidden shadow-sm sm:rounded-lg relative">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-gray-50 text-gray-400 font-bold border-b text-[10px] uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4 border-r border-gray-100 italic">Nome da Empresa</th>
                    <th class="px-6 py-4 border-r border-gray-100 italic">CNPJ</th>
                    <th class="px-6 py-4 border-r border-gray-100 italic">Tipo</th>
                    <th class="px-6 py-4 border-r border-gray-100 italic text-center">Status</th>
                    <th class="px-6 py-4 border-r border-gray-100 italic">Data</th>
                    <th class="px-6 py-4 text-right pr-12 italic">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($alvaras as $alvara)
                <tr class="hover:bg-blue-50/30 transition border-b border-gray-100 last:border-none">
                    <td class="px-6 py-4 border-r border-gray-100">
                        <div class="font-bold text-[#4a5568] uppercase text-[11px]">{{ $alvara->empresa->nome ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 border-r border-gray-100">
                        <div class="text-[#718096] font-medium text-[12px]">{{ $alvara->empresa->cnpj ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 border-r border-gray-100">
                        <div class="text-[#4a5568] font-semibold text-[11px] uppercase">{{ $alvara->tipoAlvara?->nome ?? $alvara->tipo }}</div>
                    </td>
                    <td class="px-6 py-4 text-center border-r border-gray-100">
                        @if($alvara->status === 'vigente')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold bg-[#edfaf2] text-[#38a169] border border-[#c6f6d5]">
                                ✔ Ativo
                            </span>
                        @elseif($alvara->status === 'proximo')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold bg-[#fffaf0] text-[#dd6b20] border border-[#feebc8]">
                                ⚠ Renovação
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold bg-[#fff5f5] text-[#e53e3e] border border-[#fed7d7]">
                                ❌ Vencido
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 border-r border-gray-100">
                        <div class="text-[#4a5568] font-medium text-[12px]">{{ $alvara->data_vencimento->format('d/m/Y') }}</div>
                    </td>
                    <td class="px-6 py-4 text-right pr-8">
                        <div class="flex items-center justify-end gap-3 text-[#5a67d8]">
                            {{-- View --}}
                            <a href="{{ route('alvaras.show', $alvara) }}" class="hover:scale-110 transition" title="Ver Alvará">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                            @php
                                $modalData = [
                                    'id' => $alvara->id,
                                    'empresa' => $alvara->empresa->nome,
                                    'nome' => $alvara->empresa->responsavel,
                                    'email' => $alvara->empresa->email,
                                    'telefone' => $alvara->empresa->telefone,
                                    'historico' => $alvara->notificacoes->map(function($n) {
                                        $msg = json_decode($n->mensagem, true) ?? [];
                                        return [
                                            'data' => $n->created_at->format('d/m/Y H:i'),
                                            'destinatario' => $msg['destinatario_nome'] ?? 'Desconhecido',
                                            'metodo' => $msg['metodo'] ?? 'email'
                                        ];
                                    })->values()->all()
                                ];
                            @endphp
                            <button type="button" 
                                @click="openModal({{ json_encode($modalData) }})"
                                class="hover:scale-110 transition text-blue-500" title="Enviar Alvará">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                            </button>
                            {{-- Edit --}}
                            <a href="{{ route('alvaras.edit', $alvara) }}" class="hover:scale-110 transition" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            </a>
                            {{-- Delete --}}
                            <form method="POST" action="{{ route('alvaras.destroy', $alvara) }}" class="inline" onsubmit="return confirm('Excluir este alvará?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="hover:scale-110 transition text-red-400" title="Excluir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">
                        Nenhum alvará encontrado para os filtros selecionados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($alvaras->total() > 0 && method_exists($alvaras, 'links'))
        <div class="px-6 py-4 border-t bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-xs text-gray-500">
                Mostrando {{ $alvaras->firstItem() }} até {{ $alvaras->lastItem() }} de {{ $alvaras->total() }} alvarás
            </div>
            <div class="w-full md:w-auto flex justify-end">
                {{ $alvaras->appends(request()->all())->links() }}
            </div>
        </div>
        @endif
    </div>

    <!-- Modal de Envio -->
    <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background Overlay -->
            <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="open = false" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div x-show="open" x-transition.scale.origin.bottom class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full">
                
                <div class="bg-gray-50 px-4 py-3 border-b flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Enviar Alvará - <span x-text="alvara.empresa" class="text-blue-600 font-bold"></span>
                    </h3>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Fechar</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    
                    <!-- Alertas -->
                    <template x-if="successMessage">
                        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative">
                            <span class="block sm:inline" x-text="successMessage"></span>
                        </div>
                    </template>
                    <template x-if="errorMessage">
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative">
                            <span class="block sm:inline" x-text="errorMessage"></span>
                        </div>
                    </template>

                    <form @submit.prevent="submit" class="space-y-4">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Nome -->
                            <div>
                                <label for="nome" class="block text-sm font-medium text-gray-700">Nome do Destinatário</label>
                                <input type="text" x-model="form.nome" id="nome" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">E-mail <span class="text-red-500">*</span></label>
                                <input type="email" x-model="form.email" id="email" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                        </div>

                        <!-- Telefone -->
                        <div>
                            <label for="telefone" class="block text-sm font-medium text-gray-700">Telefone / WhatsApp</label>
                            <input type="text" x-model="form.telefone" id="telefone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Opções de Envio -->
                        <div>
                            <span class="block text-sm font-medium text-gray-700 mb-2">Método de Envio</span>
                            <div class="flex gap-4">
                                <label class="flex items-center">
                                    <input type="radio" x-model="form.metodo" value="email" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">E-mail</span>
                                </label>
                                <label class="flex items-center opacity-50 cursor-not-allowed" title="Em breve">
                                    <input type="radio" value="whatsapp" disabled class="focus:ring-green-500 h-4 w-4 text-green-600 border-gray-300 cursor-not-allowed">
                                    <span class="ml-2 text-sm text-gray-700">WhatsApp <span class="text-xs text-gray-500">(Em breve)</span></span>
                                </label>
                            </div>
                        </div>

                         <!-- Mensagem Adicional -->
                         <div>
                            <label for="mensagem" class="block text-sm font-medium text-gray-700">Mensagem Adicional (Opcional)</label>
                            <textarea id="mensagem" x-model="form.mensagem" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Escreva uma mensagem para incluir no corpo do e-mail..."></textarea>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" :disabled="loading" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                                <span x-show="!loading">Enviar Documento</span>
                                <span x-show="loading">Enviando...</span>
                            </button>
                        </div>
                    </form>

                    <!-- Histórico de Envios -->
                    <div class="mt-8 border-t pt-4">
                        <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Histórico de Envios deste Documento
                        </h4>
                        
                        <div class="bg-gray-50 rounded shadow-inner max-h-48 overflow-y-auto">
                            <div x-show="historico.length === 0" class="p-4 text-center text-xs text-gray-500 italic">
                                Nenhum envio registrado ainda.
                            </div>
                            <ul class="divide-y divide-gray-200">
                                <template x-for="(hist, index) in historico" :key="index">
                                    <li class="px-4 py-2 flex justify-between items-center text-xs hover:bg-gray-100">
                                        <div>
                                            <span class="font-semibold text-gray-700" x-text="hist.data"></span> -
                                            <span class="text-gray-600" x-text="hist.destinatario"></span>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-blue-100 text-blue-800 uppercase" x-text="hist.metodo">
                                        </span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('alvaraSendModal', () => ({
        open: false,
        loading: false,
        successMessage: '',
        errorMessage: '',
        alvara: {
            id: null,
            empresa: '',
        },
        form: {
            nome: '',
            email: '',
            telefone: '',
            metodo: 'email',
            mensagem: ''
        },
        historico: [],
        
        openModal(data) {
            this.alvara.id = data.id;
            this.alvara.empresa = data.empresa;
            this.form.nome = data.nome;
            this.form.email = data.email;
            this.form.telefone = data.telefone;
            this.form.metodo = 'email';
            this.form.mensagem = '';
            this.historico = data.historico || [];
            this.successMessage = '';
            this.errorMessage = '';
            this.open = true;
        },
        
        submit() {
            if (!this.form.email) {
                this.errorMessage = 'O e-mail é obrigatório.';
                return;
            }
            this.loading = true;
            this.errorMessage = '';
            this.successMessage = '';
            
            fetch(`/alvaras/${this.alvara.id}/enviar-email`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.form)
            })
            .then(response => response.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    this.successMessage = data.message;
                    if (data.historico) {
                        this.historico.unshift(data.historico);
                    }
                    // Reset do formulário, mantendo e-mail
                    this.form.mensagem = '';
                } else {
                    this.errorMessage = data.message || 'Erro ao enviar o e-mail.';
                }
            })
            .catch(error => {
                this.loading = false;
                this.errorMessage = 'Falha na comunicação com o servidor. Verifique sua conexão e logs de email.';
            });
        }
    }));
});
</script>
