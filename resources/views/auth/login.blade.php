<x-guest-layout>
    <x-auth-session-status class="mb-4 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" value="E-mail" class="text-sm font-semibold text-slate-700" />
            <x-text-input id="email" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-sm text-slate-900 shadow-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-5" x-data="{ showPassword: false }">
            <div class="flex items-center justify-between gap-4">
                <x-input-label for="password" value="Senha" class="text-sm font-semibold text-slate-700" />

                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-slate-500 transition hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-200 focus:ring-offset-2 rounded-md" href="{{ route('password.request') }}">
                        Esqueceu a senha?
                    </a>
                @endif
            </div>

            <div class="relative mt-2">
                <x-text-input id="password" class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3.5 pr-12 text-sm text-slate-900 shadow-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
                                x-bind:type="showPassword ? 'text' : 'password'"
                                name="password"
                                required autocomplete="current-password" />

                <button type="button"
                    class="absolute inset-y-0 right-0 inline-flex items-center justify-center px-4 text-slate-400 transition hover:text-slate-700 focus:outline-none"
                    x-on:click="showPassword = !showPassword"
                    x-bind:aria-label="showPassword ? 'Ocultar senha' : 'Mostrar senha'"
                    x-bind:title="showPassword ? 'Ocultar senha' : 'Mostrar senha'">
                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>

                    <svg x-show="showPassword" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m3 3 18 18M10.585 10.587A2 2 0 0 0 13.414 13.414M9.88 5.09A10.94 10.94 0 0 1 12 5c4.478 0 8.268 2.943 9.542 7a10.526 10.526 0 0 1-4.112 5.145M6.228 6.228A10.45 10.45 0 0 0 2.458 12c1.274 4.057 5.065 7 9.542 7a10.94 10.94 0 0 0 4.06-.772" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-5 flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex items-center text-sm text-slate-600">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-teal-700 shadow-sm focus:ring-teal-500" name="remember">
                <span class="ms-2">Manter conectado</span>
            </label>

            <span class="text-xs uppercase tracking-[0.2em] text-slate-400">
                Acesso seguro
            </span>
        </div>

        <div class="mt-8">
            <x-primary-button class="inline-flex w-full justify-center rounded-2xl border-0 bg-slate-900 px-5 py-4 text-sm font-semibold uppercase tracking-[0.2em] text-white transition hover:bg-teal-700 focus:bg-teal-700 active:bg-slate-950 focus:ring-teal-300">
                Entrar no painel
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
