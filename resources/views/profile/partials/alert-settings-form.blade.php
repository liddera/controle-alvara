<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Alertas de Vencimento') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Configure com quantos dias de antecedência você deseja receber avisos por e-mail e no painel.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.alerts.store') }}" class="mt-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="tipo_alvara_id" :value="__('Tipo de Alvará (Opcional)')" />
                <select id="tipo_alvara_id" name="tipo_alvara_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm">
                    <option value="">{{ __('Todos os Tipos') }}</option>
                    @foreach($tiposAlvara as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nome }}</option>
                    @endforeach
                </select>
                <p class="text-[10px] text-gray-500 mt-1">Deixe em branco para aplicar a todos os alvarás.</p>
            </div>

            <div>
                <x-input-label for="days_before" :value="__('Dias Antes do Vencimento')" />
                <x-text-input id="days_before" name="days_before" type="number" class="mt-1 block w-full" required min="0" max="365" placeholder="Ex: 40" />
                <x-input-error class="mt-2" :messages="$errors->get('days_before')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Adicionar Alerta') }}</x-primary-button>
        </div>
    </form>

    <div class="mt-10">
        <h3 class="text-md font-medium text-gray-900 mb-4">Seus Alertas Ativos</h3>
        <div class="space-y-4">
            @forelse ($configs as $config)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div>
                        <div class="font-bold text-gray-800">
                            {{ $config->days_before }} dias antes
                        </div>
                        <div class="text-xs text-gray-500 lowercase">
                            {{ $config->tipoAlvara ? "apenas para {$config->tipoAlvara->nome}" : 'para todos os tipos de alvarás' }}
                        </div>
                    </div>
                    <form method="post" action="{{ route('profile.alerts.destroy', $config->id) }}">
                        @csrf
                        @method('delete')
                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-semibold">
                            Remover
                        </button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-500 italic">Nenhum alerta configurado ainda.</p>
            @endforelse
        </div>
    </div>
</section>
