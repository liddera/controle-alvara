@props(['alvaras'])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
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
                            {{-- Share/Send (Placeholder) --}}
                            <button class="hover:scale-110 transition text-gray-400 pointer-events-none" title="Enviar (Em breve)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                            </button>
                            {{-- Search (Placeholder/Preview) --}}
                            <button class="hover:scale-110 transition text-gray-400 pointer-events-none" title="Visualizar rápido">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
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
</div>
