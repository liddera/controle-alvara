<section>
    <div class="mx-auto max-w-5xl">
        <div class="flex flex-col-reverse gap-10 lg:flex-row lg:items-start lg:justify-between">

            <div class="w-full max-w-2xl">
                <header class="mb-8">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">
                        {{ __('Dados da conta') }}
                    </p>
                    <h2 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900">
                        {{ __('Informações do perfil') }}
                    </h2>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600">
                        {{ __('Atualize as informações da sua conta e o endereço de e-mail.') }}
                    </p>
                </header>

                <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data"
                    class="space-y-6">

                    @csrf
                    @method('patch')

                    <div>
                        <x-input-label for="name" :value="__('Nome')" />
                        <x-text-input id="name" name="name" type="text" class="mt-2 block w-full"
                            :value="old('name', $user->name)" required />
                        <x-input-error :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('E-mail')" />
                        <x-text-input id="email" name="email" type="email" class="mt-2 block w-full"
                            :value="old('email', $user->email)" required />
                        <x-input-error :messages="$errors->get('email')" />
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                        <x-primary-button>
                            {{ __('Salvar') }}
                        </x-primary-button>

                        @if (session('status') === 'profile-updated')
                        <span class="text-sm text-slate-500">
                            {{ __('Salvo.') }}
                        </span>
                        @endif
                    </div>

                    <input id="profile_photo" name="profile_photo" type="file" class="hidden" accept="image/*" 
                        onchange="handleFilePreview(this, 'photo-preview-final', 'photo-placeholder-final')" />
                </form>
            </div>

            <div class="w-full max-w-sm rounded-[1.75rem] border border-slate-200 bg-slate-50/80 p-6 shadow-sm">
                <div class="flex flex-col items-center text-center">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">
                        {{ __('Foto de perfil') }}
                    </p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ __('Mantenha sua foto atualizada para facilitar a identificação da sua conta.') }}
                    </p>

                    <div class="relative mt-6 h-32 w-32 cursor-pointer group"
                        onclick="document.getElementById('profile_photo').click()">
                        <div
                            class="absolute -inset-1 rounded-full bg-gradient-to-br from-indigo-100 to-gray-100 opacity-70 blur-sm group-hover:opacity-100 transition">
                        </div>

                        <div
                            class="relative flex-none overflow-hidden h-32 w-32 rounded-full border border-white/60 shadow-md ring-1 ring-gray-200 bg-white">

                            @if($user->profile_photo_url)
                            <img id="photo-preview-final" src="{{ $user->profile_photo_url }}"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                            <div id="photo-placeholder-final"
                                class="h-full w-full bg-gradient-to-br from-indigo-50 via-white to-gray-50 flex flex-col items-center justify-center">
                                <svg class="w-10 h-10 text-indigo-200 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-width="1.5" d="M3 9a2 2 0 012-2h.93l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89L16.93 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <circle cx="12" cy="13" r="3" stroke-width="1.5" />
                                </svg>
                                <span class="text-[10px] text-indigo-300 font-bold uppercase tracking-widest">Enviar Foto</span>
                            </div>
                            @endif

                            <div
                                class="absolute inset-0 rounded-full bg-black/40 opacity-0 group-hover:opacity-100 transition duration-300 flex flex-col items-center justify-center space-y-1">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-width="2"
                                        d="M3 9a2 2 0 012-2h.93l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89L16.93 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <circle cx="12" cy="13" r="3" stroke-width="2" />
                                </svg>

                                <span class="text-[10px] text-white font-bold uppercase tracking-widest">
                                    Trocar
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col items-center">
                        <p class="text-xs font-medium text-slate-500">
                            {{ __('Alterar foto de perfil') }}
                        </p>

                        @if($user->profile_photo_path)
                            <div class="mt-3">
                                <button type="button"
                                    class="text-[10px] text-red-500 hover:text-red-700 font-bold uppercase tracking-widest transition-colors"
                                    x-on:click.prevent="$dispatch('open-confirm-modal', { 
                                        name: 'confirm-asset-deletion', 
                                        action: '{{ route('profile.photo.destroy') }}', 
                                        title: '{{ __('Remover Foto de Perfil') }}', 
                                        content: '{{ __('Você tem certeza que deseja remover sua foto de perfil?') }}',
                                        method: 'DELETE'
                                    })">
                                    {{ __('Remover Foto') }}
                                </button>
                            </div>
                        @endif
                    </div>

                    <x-input-error :messages="$errors->get('profile_photo')" class="mt-4" />
                </div>
            </div>
        </div>
    </div>

    <x-confirmation-modal name="confirm-asset-deletion" confirm="Remover" />
</section>
