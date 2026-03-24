<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('alvaras.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Novo Alvará</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                <form method="POST" action="{{ route('alvaras.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Empresa <span class="text-red-500">*</span></label>
                        <select name="empresa_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 @error('empresa_id') border-red-500 @enderror">
                            <option value="">Selecione a empresa...</option>
                            @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" @selected(old('empresa_id', $empresaSelecionada) == $empresa->id)>{{ $empresa->nome }}</option>
                            @endforeach
                        </select>
                        @error('empresa_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo de Alvará <span class="text-red-500">*</span></label>
                            <select name="tipo_alvara_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 @error('tipo_alvara_id') border-red-500 @enderror">
                                <option value="">Selecione o tipo...</option>
                                @foreach($tiposAlvara as $tipo)
                                    <option value="{{ $tipo->id }}" @selected(old('tipo_alvara_id', isset($tipoSelecionado) ? $tiposAlvara->where('slug', $tipoSelecionado)->first()?->id : null) == $tipo->id)>
                                        {{ $tipo->nome }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo_alvara_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Número / Protocolo</label>
                            <input type="text" name="numero" value="{{ old('numero') }}" placeholder="Ex: ALV-0001/2025" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Data de Emissão</label>
                            <input type="date" name="data_emissao" value="{{ old('data_emissao') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Data de Vencimento <span class="text-red-500">*</span></label>
                            <input type="date" name="data_vencimento" value="{{ old('data_vencimento') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 @error('data_vencimento') border-red-500 @enderror">
                            @error('data_vencimento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Status do Alvará <span class="text-red-500">*</span></label>
                            <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 @error('status') border-red-500 @enderror">
                                <option value="vigente" @selected(old('status') == 'vigente')>✔ Ativo</option>
                                <option value="proximo" @selected(old('status') == 'proximo')>⚠ Renovação</option>
                                <option value="vencido" @selected(old('status') == 'vencido')>❌ Vencido</option>
                            </select>
                            @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Observações</label>
                        <textarea name="observacoes" rows="3" placeholder="Informações adicionais, órgão responsável, etc." class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500">{{ old('observacoes') }}</textarea>
                    </div>

                    <!-- Upload de Documentos -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Documentos (PDF, imagens)</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-orange-400 transition" x-data="{ dragging: false }" @dragover.prevent="dragging = true" @dragleave="dragging = false" @drop.prevent="dragging = false">
                            <input type="file" name="documentos[]" id="documentos" multiple accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                            <label for="documentos" class="cursor-pointer">
                                <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                <p class="text-sm text-gray-600">Clique para selecionar ou <span class="text-orange-600 font-semibold">arraste os arquivos</span></p>
                                <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG — máx. 10MB por arquivo</p>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <a href="{{ route('alvaras.index') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition">Cancelar</a>
                        <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-md transition shadow-sm">
                            Salvar Alvará
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
