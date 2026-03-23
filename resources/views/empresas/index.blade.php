<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Empresas</h2>
            <a href="{{ route('empresas.create') }}" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-md font-semibold text-sm transition shadow-sm">
                + Nova Empresa
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-md flex justify-between items-center">
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="text-green-600 hover:text-green-800 font-bold text-lg leading-none">&times;</button>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-600 font-semibold border-b">
                            <tr>
                                <th class="px-6 py-3">Empresa</th>
                                <th class="px-6 py-3">CNPJ</th>
                                <th class="px-6 py-3">Responsável</th>
                                <th class="px-6 py-3">Contato</th>
                                <th class="px-6 py-3 text-center">Alvarás</th>
                                <th class="px-6 py-3 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($empresas as $empresa)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800">{{ $empresa->nome }}</div>
                                    <div class="text-xs text-gray-500">{{ $empresa->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-mono text-xs">{{ $empresa->cnpj }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $empresa->responsavel }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $empresa->telefone }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold">
                                        {{ $empresa->alvaras_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right flex items-center justify-end gap-2">
                                    <a href="{{ route('empresas.show', $empresa) }}" class="text-gray-400 hover:text-gray-700" title="Ver Alvarás">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    <a href="{{ route('empresas.edit', $empresa) }}" class="text-blue-400 hover:text-blue-700" title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <form method="POST" action="{{ route('empresas.destroy', $empresa) }}" onsubmit="return confirm('Excluir empresa e todos os seus alvarás?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-700" title="Excluir">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        <p class="text-lg font-medium">Nenhuma empresa cadastrada</p>
                                        <a href="{{ route('empresas.create') }}" class="bg-orange-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-orange-700">
                                            + Cadastrar Primeira Empresa
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($empresas->hasPages())
                <div class="px-6 py-4 border-t">
                    {{ $empresas->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
