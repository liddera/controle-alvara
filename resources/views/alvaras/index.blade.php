<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Alvarás
                @if($tipo_slug)
                    <span class="text-sm text-gray-500 ml-2">— {{ $tipoSelecionadoNome ?? $tipo_slug }}</span>
                @endif
            </h2>
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
                    <x-searchable-select 
                        name="empresa_id" 
                        :options="$empresas->map(fn($e) => ['id' => $e->id, 'nome' => $e->nome])->values()->toArray()"
                        :value="$empresa_id"
                        :initialSearch="$empresas->firstWhere('id', $empresa_id)?->nome ?? ''"
                        placeholder="Todas as Empresas"
                    />
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tipo de Alvará</label>
                    <select name="tipo" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Todos os Tipos</option>
                        @foreach($tiposAlvara as $tipo)
                            <option value="{{ $tipo->slug }}" @selected($tipo_slug == $tipo->slug)>{{ $tipo->nome }}</option>
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
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Vencimento de</label>
                    <input type="date" name="vencimento_de" value="{{ $vencimento_de ?? '' }}"
                        class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">até</label>
                    <input type="date" name="vencimento_ate" value="{{ $vencimento_ate ?? '' }}"
                        class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-semibold">Filtrar</button>
                    <a href="{{ route('alvaras.index') }}" class="border border-gray-300 text-gray-600 px-4 py-2 rounded-md text-sm font-semibold hover:bg-gray-50">Limpar</a>
                </div>
            </form>

            <!-- Tabela -->
            <x-alvara-table :alvaras="$alvaras" />

        </div>
    </div>
</x-app-layout>
