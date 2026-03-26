<x-app-layout>
    <!-- Dashboard sem header redundante -->

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Cards de Totalizadores -->
            @php
                $baseFilters = request()->except(['status', 'page']);
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <a href="{{ route('dashboard', array_merge($baseFilters, ['status' => 'todos'])) }}"
                    class="block bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-blue-500 p-4 hover:scale-105 transition-transform cursor-pointer">
                    <div class="text-sm text-blue-600 font-bold uppercase mb-1">Total de Alvarás</div>
                    <div class="text-3xl font-bold text-blue-600">{{ $stats['total'] }}</div>
                </a>
                <a href="{{ route('dashboard', array_merge($baseFilters, ['status' => 'vigente'])) }}"
                    class="block bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-green-500 p-4 hover:scale-105 transition-transform cursor-pointer">
                    <div class="text-sm text-green-600 font-bold uppercase mb-1">Ativos</div>
                    <div class="text-3xl font-bold text-green-600">{{ $stats['ativos'] }}</div>
                </a>
                <a href="{{ route('dashboard', array_merge($baseFilters, ['status' => 'proximo'])) }}"
                    class="block bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-orange-500 p-4 hover:scale-105 transition-transform cursor-pointer">
                    <div class="text-sm text-orange-600 font-bold uppercase mb-1">Em Renovação</div>
                    <div class="text-3xl font-bold text-orange-600">{{ $stats['em_renovacao'] }}</div>
                </a>
                <a href="{{ route('dashboard', array_merge($baseFilters, ['status' => 'vencido'])) }}"
                    class="block bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-red-500 p-4 hover:scale-105 transition-transform cursor-pointer">
                    <div class="text-sm text-red-600 font-bold uppercase mb-1">Vencidos</div>
                    <div class="text-3xl font-bold text-red-600">{{ $stats['vencidos'] }}</div>
                </a>
            </div>

            <!-- Lista de Alvarás -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <form action="{{ route('dashboard') }}" method="GET" class="flex flex-col md:flex-row md:items-end gap-3 w-full">
                        <div class="w-full md:w-64">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Empresa</label>
                            <x-searchable-select
                                name="empresa_id"
                                :options="$empresas->map(fn($e) => ['id' => $e->id, 'nome' => $e->nome])->values()->toArray()"
                                :value="request('empresa_id')"
                                :initialSearch="$empresaSelecionada?->nome ?? ''"
                                placeholder="Todas as Empresas"
                            />
                        </div>

                        <div class="w-full md:w-52">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Tipo de Alvará</label>
                            <select name="tipo_alvara_id"
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Todos os Tipos</option>
                                @foreach($tiposAlvara as $tipo)
                                <option value="{{ $tipo->id }}" @selected(request('tipo_alvara_id') == $tipo->id)>
                                    {{ $tipo->nome }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full md:w-44">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                            <select name="status"
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="todos" @selected(request('status', 'todos') == 'todos')>Todos os Status</option>
                                <option value="vigente" @selected(request('status') == 'vigente')>Ativos</option>
                                <option value="proximo" @selected(request('status') == 'proximo')>Em Renovação</option>
                                <option value="vencido" @selected(request('status') == 'vencido')>Vencidos</option>
                            </select>
                        </div>

                        <div class="w-full md:w-44">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Vencimento de</label>
                            <input type="date" name="vencimento_de" value="{{ request('vencimento_de') }}"
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="w-full md:w-44">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">até</label>
                            <input type="date" name="vencimento_ate" value="{{ request('vencimento_ate') }}"
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="flex gap-2 md:ml-auto">
                            <button type="submit"
                                class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-gray-700 transition">
                                Filtrar
                            </button>
                            <a href="{{ route('dashboard') }}"
                                class="border border-gray-300 text-gray-600 px-4 py-2 rounded-md text-sm font-semibold hover:bg-gray-50">
                                Limpar
                            </a>
                            <button type="button"
                                onclick="window.location.href='{{ route('dashboard.export', request()->except('page')) }}'"
                                class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-blue-700 transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Exportar
                            </button>
                        </div>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <x-alvara-table :alvaras="$alvaras" />
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
