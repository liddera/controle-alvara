<section>
    <header>
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">
            {{ __('Segurança') }}
        </p>
        <h2 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900">
            {{ __('Atualizar senha') }}
        </h2>

        <p class="mt-2 text-sm leading-6 text-slate-600">
            {{ __('Use uma senha longa e segura para proteger melhor sua conta.') }}
        </p>
    </header>

    <div class="mt-6 rounded-[1.5rem] border border-slate-200 bg-slate-50/70 p-5">
        <div class="flex items-start gap-3">
            <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <svg class="h-5 w-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.5 10.5V7.875a4.125 4.125 0 10-8.25 0V10.5m-.75 0h9a1.5 1.5 0 011.5 1.5v6a1.5 1.5 0 01-1.5 1.5h-9A1.5 1.5 0 016 18v-6a1.5 1.5 0 011.5-1.5z" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-900">
                    {{ __('Proteja o acesso à sua conta') }}
                </h3>
                <p class="mt-1 text-sm leading-6 text-slate-600">
                    {{ __('Troque sua senha sempre que necessário e use uma combinação forte para manter seus dados mais seguros.') }}
                </p>
            </div>
        </div>
    </div>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Senha atual')" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-2 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('Nova senha')" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-2 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirmar senha')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-2 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Salvar') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-slate-600"
                >{{ __('Salvo.') }}</p>
            @endif
        </div>
    </form>
</section>
