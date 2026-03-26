<section class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 mb-8">
    <header class="flex items-center gap-4 mb-8">
        <div class="p-3 bg-indigo-100 rounded-2xl">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                </path>
            </svg>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-800">
                {{ __('Foto de Perfil') }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ __('Atualize sua foto de perfil para personalização do painel.') }}
            </p>
        </div>
    </header>

    <form method="post" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="flex flex-col md:flex-row items-center gap-8 p-6 bg-gray-50 rounded-3xl border border-gray-100">

            <!-- AVATAR -->
            <div class="flex-none">
                <div class="relative group cursor-pointer" onclick="document.getElementById('profile_photo').click()">

                    <!-- Glow -->
                    <div class="absolute -inset-1 rounded-full bg-gradient-to-br from-indigo-100 to-gray-100 opacity-70 blur-sm group-hover:opacity-100 transition">
                    </div>

                    <div class="relative h-24 w-24 rounded-full overflow-hidden border-4 border-white shadow-md bg-white flex-none">

                        @if(auth()->user()->profile_photo_url)
                        <img id="photo-preview" src="{{ auth()->user()->profile_photo_url }}"
                            class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        @else
                        <div id="photo-placeholder"
                            class="h-full w-full bg-gradient-to-br from-indigo-50 via-white to-gray-50 flex flex-col items-center justify-center">
                            <svg class="w-8 h-8 text-indigo-200 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="1.5" d="M3 9a2 2 0 012-2h.93l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89L16.93 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <circle cx="12" cy="13" r="3" stroke-width="1.5" />
                            </svg>
                            <span class="text-[8px] text-indigo-300 font-bold uppercase tracking-tighter">Add</span>
                        </div>
                        @endif

                        <!-- HOVER -->
                        <div class="absolute inset-0 z-10 flex flex-col items-center justify-center rounded-full
                                    bg-black/40 opacity-0 group-hover:opacity-100 transition duration-300 space-y-1">

                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="2"
                                    d="M3 9a2 2 0 012-2h.93l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89L16.93 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <circle cx="12" cy="13" r="3" stroke-width="2" />
                            </svg>
                            <span class="text-[8px] text-white font-bold uppercase tracking-widest">Trocar</span>

                        </div>

                    </div>
                </div>
            </div>

            <!-- INPUT -->
            <div class="flex-1 w-full flex flex-col gap-4">
                <label class="text-sm font-bold text-gray-700 block">
                    {{ __('Escolher Nova Foto') }}
                </label>

                <div class="relative group">
                    <input id="profile_photo" name="profile_photo" type="file"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*" 
                        onchange="handleFilePreview(this, 'photo-preview', 'photo-placeholder', 'photo_name')" 
                        required />

                    <div
                        class="flex items-center gap-3 p-4 bg-white rounded-2xl border border-gray-100 group-hover:border-indigo-200 transition">
                        <span
                            class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Escolher arquivo
                        </span>

                        <span id="photo_name" class="text-xs text-gray-400 truncate max-w-[200px]">
                            Nenhum arquivo escolhido
                        </span>
                    </div>
                </div>

                <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
            </div>
        </div>

        <!-- ACTION -->
        <div class="flex items-center gap-4 pt-4">
            <button type="submit"
                class="bg-[#1f2937] text-white px-8 py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-black shadow-lg shadow-gray-100 transition flex items-center gap-3">

                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2"
                        d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>

                {{ __('Atualizar Foto') }}
            </button>

            @if (session('status') === 'profile-photo-updated' || session('photo_success'))
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                class="text-sm text-green-600 font-bold flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ __('Foto atualizada.') }}
            </p>
            @endif
        </div>
    </form>
</section>