@php
    $initialRecipientEmails = old('recipient_emails', []);
    $initialRecipientPhones = old('recipient_phones', []);

    if (!is_array($initialRecipientEmails)) {
        $initialRecipientEmails = [];
    }

    if (!is_array($initialRecipientPhones)) {
        $initialRecipientPhones = [];
    }

    $initialRecipientEmails = collect($initialRecipientEmails)
        ->filter(fn ($email) => filled($email))
        ->map(fn ($email) => strtolower(trim((string) $email)))
        ->unique()
        ->values()
        ->all();

    $initialRecipientPhones = collect($initialRecipientPhones)
        ->filter(fn ($phone) => filled($phone))
        ->map(fn ($phone) => preg_replace('/\D+/', '', (string) $phone))
        ->map(fn ($phone) => str_starts_with($phone, '00') ? substr($phone, 2) : $phone)
        ->filter(fn ($phone) => filled($phone))
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

        <div x-data="alertPhones(@js($initialRecipientPhones))">
            <x-input-label for="recipient_phone_input" :value="__('Destinatários WhatsApp (Telefones)')" />
            <p class="text-xs text-gray-500 mt-1">
                Adicione os telefones que devem receber o alerta via WhatsApp (apenas dígitos, com DDI + DDD).
                Formato BR: <span class="font-mono">+55 (DD) 9XXXX-XXXX</span>.
            </p>

            <div class="mt-3 flex flex-wrap gap-2">
                <template x-for="phone in recipients" :key="phone">
                    <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-800 text-xs px-3 py-1">
                        <span x-text="phone"></span>
                        <button type="button" class="ml-2 text-gray-500 hover:text-gray-700" @click="removeRecipient(phone)">
                            x
                        </button>
                    </span>
                </template>
            </div>

            <div class="mt-3 flex items-center gap-2">
                <input
                    id="recipient_phone_input"
                    x-model="newPhone"
                    @keydown.enter.prevent="addRecipient()"
                    type="tel"
                    inputmode="numeric"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm"
                    placeholder="Ex: 5599999999999"
                />
                <button
                    type="button"
                    @click="addRecipient()"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Adicionar
                </button>
            </div>

            <template x-for="phone in recipients" :key="`hidden-phone-${phone}`">
                <input type="hidden" name="recipient_phones[]" :value="phone">
            </template>

            <x-input-error class="mt-2" :messages="$errors->get('recipient_phones')" />
            <x-input-error class="mt-2" :messages="$errors->get('recipient_phones.*')" />
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

    @php
        $whatsAppStatusValue = $whatsAppStatusView['status'] ?? $whatsAppStatus;
        $whatsAppStatusLabel = $whatsAppStatusView['label'] ?? 'Desconectado';
        $whatsAppStatusClass = $whatsAppStatusView['class'] ?? 'bg-red-100 text-red-700';
    @endphp

    <div
        class="mt-6 rounded-xl border border-gray-200 bg-gradient-to-r from-green-50 via-white to-emerald-50 p-5 shadow-sm"
        data-whatsapp-connection
        data-refresh-url="{{ route('whatsapp.refresh') }}"
        data-status="{{ $whatsAppStatusValue }}"
    >
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <h3 class="text-md font-medium text-gray-900">WhatsApp</h3>

                    <span
                        data-role="whatsapp-status-pill"
                        data-status="{{ $whatsAppStatusValue }}"
                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $whatsAppStatusClass }}"
                    >
                        {{ $whatsAppStatusLabel }}
                    </span>
                </div>

                <p class="text-sm text-gray-600">
                    Conecte o WhatsApp do seu cliente para enviar alertas e documentos.
                </p>

                @if ($whatsAppStatus === \App\Services\WhatsApp\OwnerWhatsAppInstanceService::STATUS_MISCONFIGURED)
                    <p class="text-xs text-gray-500">
                        A integracao com WhatsApp nao esta disponivel no momento.
                    </p>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-white"
                    x-on:click.prevent="$dispatch('open-modal', 'whatsapp-info')"
                >
                    Info
                </button>

                <form method="post" action="{{ route('whatsapp.refresh') }}">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-white"
                    >
                        Atualizar Status
                    </button>
                </form>

                <form
                    method="post"
                    action="{{ route('whatsapp.disconnect') }}"
                    data-role="whatsapp-disconnect"
                    @if ($whatsAppStatus !== \App\Services\WhatsApp\OwnerWhatsAppInstanceService::STATUS_CONNECTED)
                        class="hidden"
                    @endif
                >
                        @csrf
                        @method('delete')
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-md border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50"
                        >
                            Desconectar
                        </button>
                    </form>
                <form
                    method="post"
                    action="{{ route('whatsapp.connect') }}"
                    data-role="whatsapp-connect"
                    @if ($whatsAppStatus === \App\Services\WhatsApp\OwnerWhatsAppInstanceService::STATUS_CONNECTED || $whatsAppStatus === \App\Services\WhatsApp\OwnerWhatsAppInstanceService::STATUS_MISCONFIGURED)
                        class="hidden"
                    @endif
                >
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-700"
                        >
                            Gerar QR Code
                        </button>
                </form>
            </div>
        </div>

        <div
            class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-1"
            data-role="whatsapp-qr-block"
            @if ($whatsAppStatus === \App\Services\WhatsApp\OwnerWhatsAppInstanceService::STATUS_CONNECTED || $whatsAppStatus === \App\Services\WhatsApp\OwnerWhatsAppInstanceService::STATUS_MISCONFIGURED)
                style="display: none;"
            @endif
        >
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <div class="text-sm font-semibold text-gray-900">QR Code</div>
                <p class="mt-1 text-xs text-gray-500" data-role="qr-instructions">
                    Ao clicar em "Gerar QR Code", aguarde alguns segundos. Se o QR nao aparecer, clique em "Atualizar Status".
                </p>

                    <div class="mt-3" data-role="qr-container">
                        @if (filled($whatsAppInstance?->last_qr_code_base64))
                            <div class="relative flex justify-center">
                                <img
                                    alt="QR Code WhatsApp"
                                    class="h-48 w-48 rounded-md border border-gray-200 bg-white p-2"
                                    data-role="qr-image"
                                    src="data:image/png;base64,{{ $whatsAppInstance->last_qr_code_base64 }}"
                                />
                                <div
                                    data-role="qr-overlay"
                                    class="hidden absolute inset-0 flex flex-col items-center justify-center rounded-md bg-white/80 text-xs text-gray-700"
                                >
                                    <div class="h-6 w-6 animate-spin rounded-full border-2 border-emerald-500 border-t-transparent"></div>
                                    <span class="mt-2 font-medium">Conectando...</span>
                                </div>
                            </div>
                        @elseif (filled($whatsAppInstance?->last_qr_code_payload))
                            <div class="relative space-y-3">
                            @if (app()->environment('local'))
                                <div class="flex justify-center">
                                    <img
                                        alt="QR Code WhatsApp (dev)"
                                        class="h-48 w-48 rounded-md border border-gray-200 bg-white p-2"
                                        src="https://api.qrserver.com/v1/create-qr-code/?size=192x192&data={{ urlencode($whatsAppInstance->last_qr_code_payload) }}"
                                    />
                                </div>
                                <p class="text-[10px] text-gray-500">
                                    Dev: este QR e gerado por um servico externo apenas para facilitar testes locais sem webhook.
                                </p>
                            @endif

                            <details class="rounded-md border border-gray-200 bg-gray-50 p-3">
                                <summary class="cursor-pointer text-xs font-medium text-gray-700">Ver payload do QR</summary>
                                <div class="mt-2 break-all font-mono text-[10px] text-gray-700" data-role="qr-payload">
                                    {{ $whatsAppInstance->last_qr_code_payload }}
                                </div>
                            </details>

                            <div
                                data-role="qr-overlay"
                                class="hidden absolute inset-0 flex flex-col items-center justify-center rounded-md bg-white/80 text-xs text-gray-700"
                            >
                                <div class="h-6 w-6 animate-spin rounded-full border-2 border-emerald-500 border-t-transparent"></div>
                                <span class="mt-2 font-medium">Conectando...</span>
                            </div>
                        </div>
                        @else
                            <div class="text-xs text-gray-500 italic">
                                QR code ainda nao foi recebido.
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
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

                            @foreach(($config->recipient_phones ?? []) as $recipientPhone)
                                <span class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-800 text-xs px-3 py-1">
                                    {{ $recipientPhone }}
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
                Quando um alerta entra na antecedencia configurada, o sistema cria um evento no Google Agenda.
            </p>
            <div class="mt-4 space-y-2 text-sm text-gray-600">
                <p>A data do evento e definida a partir da data de vencimento do alvara, aplicando os dias de antecedencia do alerta.</p>
                <p>Exemplo: se o vencimento for em 30/06 e o alerta estiver configurado para 15 dias antes, o evento sera criado para 15/06.</p>
                <p>O horario do evento sera fixo, das 08:00 as 09:00.</p>
                <p>A descricao inclui empresa, tipo, numero do alvara e a data real de vencimento.</p>
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

    <x-modal name="whatsapp-info" maxWidth="lg" centered>
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900">Como funciona a integracao com WhatsApp</h3>
            <p class="mt-3 text-sm leading-6 text-gray-600">
                Depois de conectar o WhatsApp, o sistema pode enviar alertas de vencimento e documentos diretamente para os telefones configurados.
            </p>
            <div class="mt-4 space-y-2 text-sm text-gray-600">
                <p>Os alertas seguem as mesmas regras de antecedencia ja cadastradas.</p>
                <p>Os documentos sao enviados como arquivo, uma mensagem por documento.</p>
                <p>Recomendamos usar numeros completos com DDI + DDD (apenas digitos).</p>
            </div>
            <div class="mt-6 flex justify-end">
                <button
                    type="button"
                    class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    x-on:click.prevent="$dispatch('close-modal', 'whatsapp-info')"
                >
                    Fechar
                </button>
            </div>
        </div>
    </x-modal>
</section>
