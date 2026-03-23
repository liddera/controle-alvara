<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('alvaras.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $alvara->tipo }}</h2>
                    <p class="text-sm text-gray-500">{{ $alvara->empresa->nome }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('alvaras.edit', $alvara) }}" class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-md font-semibold text-sm transition">Editar</a>
                <form method="POST" action="{{ route('alvaras.destroy', $alvara) }}" onsubmit="return confirm('Excluir este alvará?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="border border-red-300 text-red-600 hover:bg-red-50 px-4 py-2 rounded-md font-semibold text-sm transition">Excluir</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-md flex justify-between items-center">
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="text-green-600 font-bold text-lg">&times;</button>
            </div>
            @endif

            <!-- Info Principal -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        @if($alvara->status === 'vigente')
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">✔ Ativo</span>
                        @elseif($alvara->status === 'proximo')
                            <span class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm font-semibold">⚠ Em Renovação</span>
                        @else
                            <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">❌ Vencido</span>
                        @endif
                    </div>
                    @php $diff = now()->diffInDays($alvara->data_vencimento, false); @endphp
                    <div class="text-right text-sm {{ $diff < 0 ? 'text-red-600' : ($diff <= 30 ? 'text-orange-600' : 'text-gray-500') }}">
                        <div class="font-bold text-lg">{{ $alvara->data_vencimento->format('d/m/Y') }}</div>
                        <div>{{ $diff < 0 ? 'Vencido há ' . abs((int)$diff) . ' dias' : 'Vence em ' . (int)$diff . ' dias' }}</div>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-sm">
                    <div><span class="font-semibold text-gray-500 block mb-1">Tipo</span>{{ $alvara->tipo }}</div>
                    <div><span class="font-semibold text-gray-500 block mb-1">Número</span>{{ $alvara->numero ?? '—' }}</div>
                    <div><span class="font-semibold text-gray-500 block mb-1">Emissão</span>{{ $alvara->data_emissao?->format('d/m/Y') ?? '—' }}</div>
                    <div><span class="font-semibold text-gray-500 block mb-1">Empresa</span>{{ $alvara->empresa->nome }}</div>
                </div>
                @if($alvara->observacoes)
                <div class="mt-4 pt-4 border-t text-sm text-gray-600">
                    <span class="font-semibold text-gray-500 block mb-1">Observações</span>
                    {{ $alvara->observacoes }}
                </div>
                @endif
            </div>

            <!-- Documentos -->
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-700">Documentos ({{ $alvara->documentos->count() }})</h3>
                    <a href="{{ route('alvaras.edit', $alvara) }}" class="text-sm text-orange-600 hover:text-orange-800 font-semibold">+ Adicionar Documento</a>
                </div>

                @if($alvara->documentos->isEmpty())
                <div class="p-8 text-center text-gray-500">
                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                    <p>Nenhum documento anexado. <a href="{{ route('alvaras.edit', $alvara) }}" class="text-orange-600 hover:underline">Adicionar agora →</a></p>
                </div>
                @else
                <ul class="divide-y divide-gray-200">
                    @foreach($alvara->documentos as $doc)
                    <li class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                        <div class="flex items-center gap-3">
                            <svg class="w-8 h-8 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <div>
                                <div class="font-semibold text-gray-800 text-sm">{{ $doc->nome_arquivo }}</div>
                                <div class="text-xs text-gray-500">{{ number_format($doc->tamanho / 1024, 1) }} KB · {{ $doc->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ Storage::url($doc->caminho) }}" target="_blank" class="text-blue-500 hover:text-blue-700 text-sm font-semibold">Ver</a>
                            <a href="{{ Storage::url($doc->caminho) }}" download="{{ $doc->nome_arquivo }}" class="text-gray-500 hover:text-gray-700 text-sm">Baixar</a>
                            <form method="POST" action="{{ route('documentos.destroy', $doc) }}" onsubmit="return confirm('Remover documento?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-700 text-sm">Remover</button>
                            </form>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
