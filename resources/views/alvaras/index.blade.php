<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Alvarás</h2>
            <a href="{{ route('alvaras.create') }}" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-md font-semibold text-sm transition shadow-sm">
                + Novo Alvará
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-md flex justify-between items-center">
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="text-green-600 font-bold text-lg leading-none">&times;</button>
            </div>
            @endif

            <!-- Filtros -->
            <form method="GET" action="{{ route('alvaras.index') }}" class="bg-white p-4 rounded-lg shadow-sm flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Empresa</label>
                    <select name="empresa_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Todas as Empresas</option>
                        @foreach($empresas as $emp)
                        <option value="{{ $emp->id }}" @selected($empresa_id == $emp->id)>{{ $emp->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                    <select name="status" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Todos os Status</option>
                        <option value="vigente" @selected($status === 'vigente')>✔ Ativo</option>
                        <option value="proximo" @selected($status === 'proximo')>⚠ Em Renovação</option>
                        <option value="vencido" @selected($status === 'vencido')>❌ Vencido</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-semibold">Filtrar</button>
                    <a href="{{ route('alvaras.index') }}" class="border border-gray-300 text-gray-600 px-4 py-2 rounded-md text-sm font-semibold hover:bg-gray-50">Limpar</a>
                </div>
            </form>

            <!-- Tabela -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-600 font-semibold border-b">
                            <tr>
                                <th class="px-6 py-3">Tipo</th>
                                <th class="px-6 py-3">Empresa</th>
                                <th class="px-6 py-3">Número</th>
                                <th class="px-6 py-3">Vencimento</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Docs</th>
                                <th class="px-6 py-3 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($alvaras as $alvara)
                            @php $diff = now()->diffInDays($alvara->data_vencimento, false); @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800">{{ $alvara->tipo }}</div>
                                    <div class="text-xs text-gray-500">{{ $alvara->numero ?? '—' }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 text-sm">{{ $alvara->empresa->nome }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $alvara->numero ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-gray-700">{{ $alvara->data_vencimento->format('d/m/Y') }}</div>
                                    <div class="text-xs {{ $diff < 0 ? 'text-red-500' : ($diff <= 30 ? 'text-orange-500' : 'text-gray-400') }}">
                                        {{ $diff < 0 ? 'Vencido há ' . abs((int)$diff) . ' dias' : 'Vence em ' . (int)$diff . ' dias' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($alvara->status === 'vigente')
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">✔ Ativo</span>
                                    @elseif($alvara->status === 'proximo')
                                        <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs font-semibold">⚠ Renovação</span>
                                    @else
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-semibold">❌ Vencido</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-gray-400 text-xs">{{ $alvara->documentos_count ?? 0 }}</span>
                                </td>
                                <td class="px-6 py-4 text-right flex items-center justify-end gap-2">
                                    <a href="{{ route('alvaras.show', $alvara) }}" class="text-gray-400 hover:text-gray-700" title="Ver detalhes">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    <a href="{{ route('alvaras.edit', $alvara) }}" class="text-blue-400 hover:text-blue-700" title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <form method="POST" action="{{ route('alvaras.destroy', $alvara) }}" onsubmit="return confirm('Excluir este alvará e todos os documentos?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-700" title="Excluir">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="text-lg font-medium">Nenhum alvará encontrado</p>
                                        <a href="{{ route('alvaras.create') }}" class="bg-orange-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-orange-700">
                                            + Cadastrar Primeiro Alvará
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($alvaras->hasPages())
                <div class="px-6 py-4 border-t">{{ $alvaras->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
