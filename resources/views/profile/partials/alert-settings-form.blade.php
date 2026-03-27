@php
    $initialRecipientEmails = old('recipient_emails', []);

    if (!is_array($initialRecipientEmails)) {
        $initialRecipientEmails = [];
    }

    $initialRecipientEmails = collect($initialRecipientEmails)
        ->filter(fn ($email) => filled($email))
        ->map(fn ($email) => strtolower(trim((string) $email)))
        ->unique()
        ->values()
        ->all();
@endphp

<section x-data="alertRecipients(@js(strtolower($ownerAlertEmail)), @js($initialRecipientEmails))">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Alertas de Vencimento') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Configure com quantos dias de antecedência você deseja receber avisos por e-mail e no painel.') }}
        </p>
    </header>

    @if (session('success'))
        <div class="mt-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <form method="post" action="{{ route('profile.alerts.store') }}" class="mt-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="tipo_alvara_id" :value="__('Tipo de Alvará (Opcional)')" />
                <select id="tipo_alvara_id" name="tipo_alvara_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm">
                    <option value="">{{ __('Todos os Tipos') }}</option>
                    @foreach($tiposAlvara as $tipo)
                        <option value="{{ $tipo->id }}" @selected(old('tipo_alvara_id') == $tipo->id)>{{ $tipo->nome }}</option>
                    @endforeach
                </select>
                <p class="text-[10px] text-gray-500 mt-1">Deixe em branco para aplicar a todos os alvarás.</p>
            </div>

            <div>
                <x-input-label for="days_before" :value="__('Dias Antes do Vencimento')" />
                <x-text-input id="days_before" name="days_before" type="number" class="mt-1 block w-full" required min="0" max="365" placeholder="Ex: 40" :value="old('days_before')" />
                <x-input-error class="mt-2" :messages="$errors->get('days_before')" />
            </div>
        </div>

        <div>
            <x-input-label for="recipient_email_input" :value="__('Destinatários de E-mail')" />
            <p class="text-xs text-gray-500 mt-1">
                O e-mail do dono é sempre incluído automaticamente. Adicione abaixo os e-mails extras que também devem receber o alerta.
            </p>

            <div class="mt-3 flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-full bg-orange-100 text-orange-800 text-xs px-3 py-1">
                    <span class="font-semibold mr-1">Dono:</span>
                    <span x-text="ownerEmail">{{ strtolower($ownerAlertEmail) }}</span>
                </span>

                <template x-for="email in recipients" :key="email">
                    <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-800 text-xs px-3 py-1">
                        <span x-text="email"></span>
                        <button type="button" class="ml-2 text-gray-500 hover:text-gray-700" @click="removeRecipient(email)">
                            x
                        </button>
                    </span>
                </template>
            </div>

            <div class="mt-3 flex items-center gap-2">
                <input
                    id="recipient_email_input"
                    x-model="newEmail"
                    @keydown.enter.prevent="addRecipient()"
                    type="email"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm"
                    placeholder="Ex: financeiro@empresa.com.br"
                />
                <button
                    type="button"
                    @click="addRecipient()"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Adicionar
                </button>
            </div>

            <template x-for="email in recipients" :key="`hidden-${email}`">
                <input type="hidden" name="recipient_emails[]" :value="email">
            </template>

            <x-input-error class="mt-2" :messages="$errors->get('recipient_emails')" />
            <x-input-error class="mt-2" :messages="$errors->get('recipient_emails.*')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Adicionar Alerta') }}</x-primary-button>
        </div>
    </form>

    <div class="mt-10 rounded-xl border border-gray-200 bg-gradient-to-r from-amber-50 via-white to-orange-50 p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <h3 class="text-md font-medium text-gray-900">Google Agenda</h3>
                    @if ($googleCalendarStatus === \App\Services\GoogleCalendarService::STATUS_CONNECTED)
                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">
                            Conectado
                        </span>
                    @elseif ($googleCalendarStatus === \App\Services\GoogleCalendarService::STATUS_RECONNECT_REQUIRED)
                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700">
                            Reconectar Google
                        </span>
                    @elseif ($googleCalendarStatus === \App\Services\GoogleCalendarService::STATUS_DISCONNECTED)
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                            Desconectado
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700">
                            Indisponivel
                        </span>
                    @endif
                </div>

                <p class="text-sm text-gray-600">
                    Conecte ao Google para receber alertas tambem na agenda.
                </p>

                @if ($googleCalendarStatus === \App\Services\GoogleCalendarService::STATUS_RECONNECT_REQUIRED)
                    <p class="text-xs font-medium text-red-600">
                        A conexao com o Google nao esta mais valida. Reconecte para voltar a criar eventos.
                    </p>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-white"
                    x-on:click.prevent="$dispatch('open-modal', 'google-calendar-info')"
                >
                    Info
                </button>

                @if ($googleCalendarStatus === \App\Services\GoogleCalendarService::STATUS_CONNECTED)
                    <form method="post" action="{{ route('google.disconnect') }}">
                        @csrf
                        @method('delete')
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-md border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50"
                        >
                            Desconectar
                        </button>
                    </form>
                @else
                    <a
                        href="{{ route('google.redirect') }}"
                        class="inline-flex items-center rounded-md bg-orange-500 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-orange-600"
                    >
                        {{ $googleCalendarStatus === \App\Services\GoogleCalendarService::STATUS_RECONNECT_REQUIRED ? 'Reconectar Google' : 'Conectar com Google' }}
                    </a>
                @endif
            </div>
        </div>

        @if ($googleCalendarStatus === \App\Services\GoogleCalendarService::STATUS_MISCONFIGURED)
            <p class="mt-3 text-xs text-gray-500">
                A integracao com Google Agenda nao esta disponivel no momento.
            </p>
        @endif
    </div>

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
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-orange-100 text-orange-800 text-xs px-3 py-1">
                                <span class="font-semibold mr-1">Dono:</span>
                                {{ strtolower($config->user?->email ?? $ownerAlertEmail) }}
                            </span>

                            @foreach(($config->recipient_emails ?? []) as $recipientEmail)
                                <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-800 text-xs px-3 py-1">
                                    {{ strtolower($recipientEmail) }}
                                </span>
                            @endforeach
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

    <x-modal name="google-calendar-info" maxWidth="lg" centered>
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900">Como funciona a integracao com Google Agenda</h3>
            <p class="mt-3 text-sm leading-6 text-gray-600">
                Quando um alerta chegar ao momento configurado, o Alvras cria um evento no seu Google Agenda usando a data de vencimento do alvara como base.
            </p>
            <div class="mt-4 space-y-2 text-sm text-gray-600">
                <p>A data do evento e calculada assim: data de vencimento menos os dias de antecedencia do alerta.</p>
                <p>O horario do evento sera fixo, das 08:00 as 09:00.</p>
                <p>A descricao do evento leva empresa, tipo, numero do alvara e a data real de vencimento.</p>
            </div>
            <div class="mt-6 flex justify-end">
                <button
                    type="button"
                    class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    x-on:click.prevent="$dispatch('close-modal', 'google-calendar-info')"
                >
                    Fechar
                </button>
            </div>
        </div>
    </x-modal>
</section>
