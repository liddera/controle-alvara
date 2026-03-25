<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('empresas.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nova Empresa</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                <form method="POST" action="{{ route('empresas.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nome / Razão Social <span class="text-red-500">*</span></label>
                        <input type="text" name="nome" value="{{ old('nome') }}" placeholder="Ex: Acme Ltda" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 @error('nome') border-red-500 @enderror">
                        @error('nome') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">CNPJ <span class="text-red-500">*</span></label>
                        <input type="text" name="cnpj" value="{{ old('cnpj') }}" placeholder="00.000.000/0001-00" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 @error('cnpj') border-red-500 @enderror">
                        @error('cnpj') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Responsável <span class="text-red-500">*</span></label>
                            <input type="text" name="responsavel" value="{{ old('responsavel') }}" placeholder="Nome completo" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 @error('responsavel') border-red-500 @enderror">
                            @error('responsavel') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Telefone <span class="text-red-500">*</span></label>
                            <input type="text" name="telefone" value="{{ old('telefone') }}" placeholder="(00) 00000-0000" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 @error('telefone') border-red-500 @enderror">
                            @error('telefone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">E-mail <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="contato@empresa.com.br" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 @error('email') border-red-500 @enderror">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tipos de Alvará Possuídos</label>
                        <div x-data="{ selected: {{ json_encode(old('tipos_alvara', [])) }}.map(String) }" class="grid grid-cols-1 md:grid-cols-2 gap-2 p-4 bg-gray-50 rounded-md border">
                            @foreach($tiposAlvara as $tipo)
                            <div class="flex flex-col p-2 hover:bg-white rounded transition">
                                <label class="flex items-center space-x-3 cursor-pointer">
                                    <input type="checkbox" x-model="selected" name="tipos_alvara[]" value="{{ $tipo->id }}" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                    <span class="text-sm text-gray-700">{{ $tipo->nome }}</span>
                                </label>
                                <div x-show="selected.includes('{{ $tipo->id }}')" x-collapse class="mt-2 pl-7">
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Data de Vencimento</label>
                                    <input type="date" name="datas_vencimento[{{ $tipo->id }}]" value="{{ old('datas_vencimento.'.$tipo->id) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-1">
                                    @error('datas_vencimento.'.$tipo->id) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <a href="{{ route('empresas.index') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition">Cancelar</a>
                        <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-md transition shadow-sm">
                            Salvar Empresa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
