<section>
    <div class="max-w-4xl mx-auto">
        <div class="flex flex-col md:flex-row items-start md:items-center gap-12 py-6">

            <!-- LEFT: FORM -->
            <div class="w-full max-w-md">
                <header class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ __('Profile Information') }}
                    </h2>
                    <p class="text-sm text-gray-500">
                        {{ __("Update your account's profile information and email address.") }}
                    </p>
                </header>

                <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data"
                    class="space-y-5">

                    @csrf
                    @method('patch')

                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                            :value="old('name', $user->name)" required />
                        <x-input-error :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                            :value="old('email', $user->email)" required />
                        <x-input-error :messages="$errors->get('email')" />
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t">
                        <x-primary-button>
                            {{ __('Save') }}
                        </x-primary-button>

                        @if (session('status') === 'profile-updated')
                        <span class="text-sm text-gray-500">
                            {{ __('Saved.') }}
                        </span>
                        @endif
                    </div>

                    <!-- INPUT FILE -->
                    <input id="profile_photo" name="profile_photo" type="file" class="hidden" accept="image/*" 
                        onchange="handleFilePreview(this, 'photo-preview-final', 'photo-placeholder-final')" />

                </form>
            </div>

            <!-- RIGHT: PHOTO -->
            <div class="flex-none w-32 min-w-[8rem] flex flex-col items-center justify-center">
                <div class="flex flex-col items-center space-y-3 w-32">

                    <div class="relative group cursor-pointer w-32 h-32 flex-none"
                        onclick="document.getElementById('profile_photo').click()">

                        <!-- Glow -->
                        <div
                            class="absolute -inset-1 rounded-full bg-gradient-to-br from-indigo-100 to-gray-100 opacity-70 blur-sm group-hover:opacity-100 transition">
                        </div>

                        <!-- Avatar -->
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

                            <!-- HOVER -->
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

                    <div class="flex flex-col items-center">
                        <p class="text-xs text-gray-500 font-medium text-center">
                            Alterar foto de perfil
                        </p>

                        @if($user->profile_photo_path)
                            <div class="mt-2">
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

                    <x-input-error :messages="$errors->get('profile_photo')" />
                </div>
            </div>

        </div>
    </div>

    <!-- Reusable Confirmation Modal -->
    <x-confirmation-modal name="confirm-asset-deletion" confirm="Remover" />
</section>