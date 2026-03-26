<x-app-layout>
    <!-- Dashboard sem header redundante -->

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Cards de Totalizadores -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <a href="{{ route('dashboard', ['status' => 'todos']) }}"
                    class="block bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-blue-500 p-4 hover:scale-105 transition-transform cursor-pointer">
                    <div class="text-sm text-blue-600 font-bold uppercase mb-1">Total de Alvarás</div>
                    <div class="text-3xl font-bold text-blue-600">{{ $stats['total'] }}</div>
                </a>
                <a href="{{ route('dashboard', ['status' => 'vigente']) }}"
                    class="block bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-green-500 p-4 hover:scale-105 transition-transform cursor-pointer">
                    <div class="text-sm text-green-600 font-bold uppercase mb-1">Ativos</div>
                    <div class="text-3xl font-bold text-green-600">{{ $stats['ativos'] }}</div>
                </a>
                <a href="{{ route('dashboard', ['status' => 'proximo']) }}"
                    class="block bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-orange-500 p-4 hover:scale-105 transition-transform cursor-pointer">
                    <div class="text-sm text-orange-600 font-bold uppercase mb-1">Em Renovação</div>
                    <div class="text-3xl font-bold text-orange-600">{{ $stats['em_renovacao'] }}</div>
                </a>
                <a href="{{ route('dashboard', ['status' => 'vencido']) }}"
                    class="block bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-red-500 p-4 hover:scale-105 transition-transform cursor-pointer">
                    <div class="text-sm text-red-600 font-bold uppercase mb-1">Vencidos</div>
                    <div class="text-3xl font-bold text-red-600">{{ $stats['vencidos'] }}</div>
                </a>
            </div>

            <!-- Lista de Alvarás -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="{}">
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <form action="{{ route('dashboard') }}" method="GET" x-data="{ 
                            open: false, 
                            search: '{{ $empresaSelecionada ? $empresaSelecionada->nome : '' }}', 
                            id: '{{ request('empresa_id') }}',
                            empresas: {{ $empresas->map(fn($e) => ['id' => $e->id, 'nome' => $e->nome])->toJson() }},
                            get filteredEmpresas() {
                                if (!this.search) return this.empresas;
                                return this.empresas.filter(e => e.nome.toLowerCase().includes(this.search.toLowerCase()));
                            }
                          }" class="flex flex-wrap gap-2 w-full items-center">

                        <!-- Busca de Empresa (Custom Searchable Select) -->
                        <div class="relative min-w-[200px] flex-grow md:flex-grow-0">
                            <input type="text" x-model="search" @focus="open = true" @click.away="open = false"
                                @keydown.escape="open = false" placeholder="Selecione Empresa..."
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 pr-8 cursor-pointer"
                                autocomplete="off">
                            <!-- Ícone de Chevron (para parecer um select normal) -->
                            <div class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>

                            <!-- Dropdown Customizado -->
                            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                                x-cloak>
                                <div @click="search = ''; id = ''; open = false"
                                    class="px-4 py-2 cursor-pointer hover:bg-blue-50 hover:text-blue-700 text-sm border-b border-gray-100 font-semibold text-gray-500">
                                    Todas as Empresas
                                </div>
                                <template x-for="empresa in filteredEmpresas" :key="empresa.id">
                                    <div @click="search = empresa.nome; id = empresa.id; open = false"
                                        class="px-4 py-2 cursor-pointer hover:bg-blue-50 hover:text-blue-700 text-sm transition-colors border-b border-gray-50 last:border-b-0"
                                        :class="id == empresa.id ? 'bg-blue-50 text-blue-700 font-bold' : 'text-gray-700'">
                                        <span x-text="empresa.nome"></span>
                                    </div>
                                </template>
                                <div x-show="filteredEmpresas.length === 0"
                                    class="px-4 py-2 text-sm text-gray-500 italic text-center">
                                    Nenhuma empresa encontrada
                                </div>
                            </div>
                            <input type="hidden" name="empresa_id" x-model="id">
                        </div>

                        <!-- Filtro por Tipo (Dropdown) -->
                        <select name="tipo_alvara_id"
                            class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos os Tipos</option>
                            @foreach($tiposAlvara as $tipo)
                            <option value="{{ $tipo->id }}" {{ request('tipo_alvara_id')==$tipo->id ? 'selected' : ''
                                }}>
                                {{ $tipo->nome }}
                            </option>
                            @endforeach
                        </select>

                        <select name="status"
                            class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="todos" {{ request('status')=='todos' ? 'selected' : '' }}>Todos os Status
                            </option>
                            <option value="vigente" {{ request('status')=='vigente' ? 'selected' : '' }}>Ativos</option>
                            <option value="proximo" {{ request('status')=='proximo' ? 'selected' : '' }}>Em Renovação
                            </option>
                            <option value="vencido" {{ request('status')=='vencido' ? 'selected' : '' }}>Vencidos
                            </option>
                        </select>

                        <button type="submit"
                            class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-gray-700 transition">
                            Filtrar
                        </button>

                        <button type="button"
                            onclick="window.location.href='{{ route('dashboard.export', request()->all()) }}'"
                            class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-blue-700 transition flex items-center gap-2 ml-auto md:ml-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
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