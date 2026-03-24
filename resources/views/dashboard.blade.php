<x-app-layout>
    <!-- Dashboard sem header redundante -->

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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="{}">
                <div class="p-4 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between gap-4">
                    <form action="{{ route('dashboard') }}" method="GET" class="flex flex-wrap gap-2 w-full md:w-full">
                        
                        <select name="empresa_id" class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 min-w-[200px]">
                            <option value="">Todas as Empresas</option>
                            @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                    {{ $empresa->razao_social }} ({{ $empresa->alvaras_count }})
                                </option>
                            @endforeach
                        </select>
                        
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Buscar por tipo..." 
                               class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 flex-grow">
                        
                        <select name="status" class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="todos" {{ request('status') == 'todos' ? 'selected' : '' }}>Todos os Status</option>
                            <option value="vigente" {{ request('status') == 'vigente' ? 'selected' : '' }}>Ativos</option>
                            <option value="proximo" {{ request('status') == 'proximo' ? 'selected' : '' }}>Em Renovação</option>
                            <option value="vencido" {{ request('status') == 'vencido' ? 'selected' : '' }}>Vencidos</option>
                        </select>

                        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-gray-700 transition">
                            Filtrar
                        </button>

                        <button type="button" 
                                onclick="window.location.href='{{ route('dashboard.export', request()->all()) }}'"
                                class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-blue-700 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Exportar
                        </button>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <x-alvara-table :alvaras="$alvaras" />
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
