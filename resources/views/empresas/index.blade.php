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

            <x-empresa-table :empresas="$empresas" />

        </div>
    </div>
</x-app-layout>
