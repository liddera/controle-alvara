<section class="space-y-6">
    <header>
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-red-400">
            {{ __('Zona sensível') }}
        </p>
        <h2 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900">
            {{ __('Excluir conta') }}
        </h2>

        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
            {{ __('Ao excluir sua conta, todos os dados e recursos vinculados serão removidos permanentemente. Antes de continuar, salve qualquer informação que deseje manter.') }}
        </p>
    </header>

    <div class="rounded-[1.5rem] border border-red-100 bg-white/90 p-5">
        <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
            <div class="max-w-2xl">
                <h3 class="text-sm font-semibold text-slate-900">
                    {{ __('Ação permanente e sem reversão') }}
                </h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    {{ __('A exclusão remove seu acesso e apaga os dados vinculados à sua conta. Faça isso apenas quando tiver certeza de que não precisará mais dessas informações.') }}
                </p>
            </div>

            <div class="shrink-0">
                <x-danger-button
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                >{{ __('Excluir conta') }}</x-danger-button>
            </div>
        </div>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Tem certeza de que deseja excluir sua conta?') }}
            </h2>

            <p class="mt-1 text-sm text-slate-600">
                {{ __('Ao excluir sua conta, todos os dados e recursos vinculados serão removidos permanentemente. Digite sua senha para confirmar essa ação.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Senha') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Senha') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancelar') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Excluir conta') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
