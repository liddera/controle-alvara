<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Empresas
                @if($tipo_slug)
                    <span class="text-sm text-gray-500 ml-2">— {{ \App\Models\TipoAlvara::where('slug', $tipo_slug)->first()?->nome ?? $tipo_slug }}</span>
                @endif
            </h2>
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

            <!-- Filtro de busca -->
            <form method="GET" action="{{ route('empresas.index') }}" class="bg-white p-4 rounded-lg shadow-sm flex gap-3 items-center">
                <div class="relative flex-grow">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                           placeholder="Buscar por Nome ou CNPJ..."
                           class="w-full pl-9 border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-semibold transition">
                    Buscar
                </button>
                @if($search)
                    <a href="{{ route('empresas.index') }}" class="text-sm text-gray-500 hover:text-gray-700 border border-gray-300 px-3 py-2 rounded-md transition">
                        Limpar
                    </a>
                @endif
            </form>

            <x-empresa-table :empresas="$empresas" />

        </div>
    </div>
</x-app-layout>
