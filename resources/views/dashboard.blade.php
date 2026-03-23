<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gerenciamento de Alvarás') }}
            </h2>
            <p class="text-sm text-gray-500">
                Visualize e gerencie todos os alvarás da empresa selecionada
            </p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Cards de Totalizadores -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-blue-500 p-4">
                    <div class="text-sm text-blue-600 font-bold uppercase mb-1">Total de Alvarás</div>
                    <div class="text-3xl font-bold text-blue-600">{{ $stats['total'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-green-500 p-4">
                    <div class="text-sm text-green-600 font-bold uppercase mb-1">Ativos</div>
                    <div class="text-3xl font-bold text-green-600">{{ $stats['ativos'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-orange-500 p-4">
                    <div class="text-sm text-orange-600 font-bold uppercase mb-1">Em Renovação</div>
                    <div class="text-3xl font-bold text-orange-600">{{ $stats['em_renovacao'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-red-500 p-4">
                    <div class="text-sm text-red-600 font-bold uppercase mb-1">Vencidos</div>
                    <div class="text-3xl font-bold text-red-600">{{ $stats['vencidos'] }}</div>
                </div>
            </div>

            <!-- Lista de Alvarás -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between gap-4">
                    <div class="flex gap-2 w-full md:w-1/2">
                        <input type="text" placeholder="Buscar por tipo de alvará..." class="border-gray-300 rounded-md shadow-sm w-full text-sm block">
                        <select class="border-gray-300 rounded-md shadow-sm text-sm">
                            <option>Todos os Status</option>
                            <option>Ativos</option>
                            <option>Em Renovação</option>
                            <option>Vencidos</option>
                        </select>
                        <button class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-gray-700">Filtrar</button>
                    </div>
                    <div>
                        <button class="border border-gray-300 bg-white text-gray-700 px-4 py-2 rounded-md text-sm font-semibold hover:bg-gray-50">Ver Empresa</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-50 text-gray-600 font-semibold border-b">
                            <tr>
                                <th class="px-6 py-3">Tipo de Alvará</th>
                                <th class="px-6 py-3">Data Vencimento</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Cidade</th>
                                <th class="px-6 py-3">Observação</th>
                                <th class="px-6 py-3 text-right">Anexos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($alvaras as $alvara)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800">{{ $alvara->tipo }}</div>
                                    <div class="text-xs text-gray-500">{{ $alvara->numero ?? 'Conselho / Órgão Responsável' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1 text-gray-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        {{ $alvara->data_vencimento->format('d/m/Y') }}
                                    </div>
                                    @php
                                        $diffDays = now()->diffInDays($alvara->data_vencimento, false);
                                    @endphp
                                    <div class="text-xs {{ $diffDays < 0 ? 'text-red-500' : 'text-gray-500' }}">
                                        @if($diffDays < 0)
                                            (Vencido há {{ abs(intval($diffDays)) }} dias)
                                        @elseif($diffDays <= 30)
                                            (Vence em {{ intval($diffDays) }} dias)
                                        @else
                                            (Vence em {{ intval($diffDays) }} dias)
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($alvara->status === 'vigente')
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold flex inline-flex items-center gap-1">✔ Ativo</span>
                                    @elseif($alvara->status === 'proximo')
                                        <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs font-semibold">⚠ Em Renovação</span>
                                    @else
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-semibold flex inline-flex items-center gap-1">❌ Vencido</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600">Ji-Paraná, RO</td>
                                <td class="px-6 py-4 text-gray-600 truncate max-w-xs" title="{{ $alvara->observacoes }}">
                                    {{ $alvara->observacoes ?? 'Sem observação' }}
                                </td>
                                <td class="px-6 py-4 text-right flex items-center justify-end gap-2 text-gray-500">
                                    <button class="hover:text-gray-800" title="Ver anexos">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    </button>
                                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded shadow-sm text-sm font-semibold transition">
                                        Baixar
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    Nenhum alvará cadastrado para esta empresa.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    @if($alvaras->count() > 0)
                    <div class="px-6 py-3 border-t bg-gray-50 text-xs text-gray-500 flex justify-between items-center">
                        <div>Mostrando {{ $alvaras->count() }} de {{ $alvaras->count() }} alvarás cadastrados</div>
                        <div class="flex items-center gap-3">
                            <span class="font-semibold">Legenda de Status:</span>
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded">✔ Ativo</span>
                            <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded">⚠ Em Renovação</span>
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded">❌ Vencido</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
