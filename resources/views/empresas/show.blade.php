<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('empresas.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $empresa->nome }} <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-sm font-semibold ml-2">Ativo</span>
                </h2>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('empresas.edit', $empresa) }}" class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-md font-semibold text-sm transition">Editar</a>
                <a href="{{ route('alvaras.create', ['empresa_id' => $empresa->id]) }}" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-md font-semibold text-sm transition shadow-sm">
                    + Cadastrar Alvará
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Info da Empresa -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm text-gray-700">
                    <div><span class="font-semibold text-gray-500 block mb-1">CNPJ</span>{{ $empresa->cnpj }}</div>
                    <div><span class="font-semibold text-gray-500 block mb-1">Responsável</span>{{ $empresa->responsavel }}</div>
                    <div><span class="font-semibold text-gray-500 block mb-1">Telefone</span>{{ $empresa->telefone }}</div>
                    <div><span class="font-semibold text-gray-500 block mb-1">E-mail</span>{{ $empresa->email }}</div>
                </div>
            </div>

            <!-- Alvarás da Empresa -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-700">Alvarás Cadastrados ({{ $empresa->alvaras->count() }})</h3>
                </div>
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-600 font-semibold border-b">
                        <tr>
                            <th class="px-6 py-3">Tipo</th>
                            <th class="px-6 py-3">Número</th>
                            <th class="px-6 py-3">Vencimento</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Observações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($empresa->alvaras as $alvara)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-semibold text-gray-800">{{ $alvara->tipo }}</td>
                            <td class="px-6 py-4 text-gray-600 font-mono text-xs">{{ $alvara->numero ?? '—' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $alvara->data_vencimento->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                @if($alvara->status === 'vigente')
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">✔ Ativo</span>
                                @elseif($alvara->status === 'proximo')
                                    <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs font-semibold">⚠ Em Renovação</span>
                                @else
                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-semibold">❌ Vencido</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-xs">{{ $alvara->observacoes ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">Nenhum alvará cadastrado para esta empresa.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
