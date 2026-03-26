<section class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
    <header class="flex items-center gap-4 mb-8">
        <div class="p-3 bg-indigo-100 rounded-2xl">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4l2 2h4a2 2 0 012 2v12a4 4 0 01-4 4H7z"></path>
            </svg>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-800">
                {{ __('Personalização do Sistema') }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ __('Customize logo, favicon e cores do painel') }}
            </p>
        </div>
    </header>

    <form method="post" action="{{ route('profile.personalization.update') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 pt-6 border-t border-gray-50">
            <!-- Logotipo -->
            <div class="relative group">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-sm font-bold text-gray-700 block">{{ __('Logotipo (PNG/JPG)') }}</label>
                    @if(optional($personalizacao)->logo_path)
                        <button type="button" 
                            class="text-[10px] text-red-500 hover:text-red-700 font-bold uppercase tracking-widest transition-colors"
                            x-on:click.prevent="$dispatch('open-confirm-modal', { 
                                name: 'confirm-branding-deletion', 
                                action: '{{ route('profile.personalization.logo.destroy') }}', 
                                title: '{{ __('Remover Logotipo') }}', 
                                content: '{{ __('Deseja realmente remover o logotipo da empresa?') }}',
                                method: 'DELETE'
                            })">
                            {{ __('Remover') }}
                        </button>
                    @endif
                </div>
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-100 group-hover:border-indigo-200 transition-colors">
                    <div id="logo-placeholder" class="flex-shrink-0 w-16 h-16 rounded-xl shadow-sm flex items-center justify-center p-2 border border-gray-100 overflow-hidden bg-gradient-to-br from-gray-50 to-indigo-50/30">
                        @if(optional($personalizacao)->logo_url)
                            <img id="logo-preview" src="{{ $personalizacao->logo_url }}" alt="Logo Atual" class="max-h-full max-w-full object-contain">
                        @else
                            <svg class="w-6 h-6 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="relative">
                            <input id="logo" name="logo" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*" onchange="handleFilePreview(this, 'logo-preview', 'logo-placeholder', 'logo_name')" />
                            <div class="flex items-center gap-2">
                                <span class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-indigo-100 transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                    Escolher arquivo
                                </span>
                                <span id="logo_name" class="text-xs text-gray-400 truncate max-w-[150px]">Nenhum arquivo escolhido</span>
                            </div>
                        </div>
                        <p class="mt-2 text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Tamanho recomendado: até 2MB</p>
                    </div>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('logo')" />
            </div>

            <!-- Favicon -->
            <div class="relative group lg:border-l lg:border-gray-50 lg:pl-8">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-sm font-bold text-gray-700 block">{{ __('Favicon (32x32 PNG)') }}</label>
                    @if(optional($personalizacao)->favicon_path)
                        <button type="button" 
                            class="text-[10px] text-red-500 hover:text-red-700 font-bold uppercase tracking-widest transition-colors"
                            x-on:click.prevent="$dispatch('open-confirm-modal', { 
                                name: 'confirm-branding-deletion', 
                                action: '{{ route('profile.personalization.favicon.destroy') }}', 
                                title: '{{ __('Remover Favicon') }}', 
                                content: '{{ __('Deseja realmente remover o favicon do sistema?') }}',
                                method: 'DELETE'
                            })">
                            {{ __('Remover') }}
                        </button>
                    @endif
                </div>
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-100 group-hover:border-indigo-200 transition-colors">
                    <div id="favicon-placeholder" class="flex-shrink-0 w-16 h-16 rounded-xl shadow-sm flex items-center justify-center p-2 border border-gray-100 bg-gradient-to-br from-gray-50 to-indigo-50/30">
                        @if(optional($personalizacao)->favicon_url)
                            <img id="favicon-preview" src="{{ $personalizacao->favicon_url }}" alt="Favicon Atual" class="w-8 h-8">
                        @else
                            <svg class="w-6 h-6 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="relative">
                            <input id="favicon" name="favicon" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*" onchange="handleFilePreview(this, 'favicon-preview', 'favicon-placeholder', 'favicon_name')" />
                            <div class="flex items-center gap-2">
                                <span class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-indigo-100 transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                    Escolher arquivo
                                </span>
                                <span id="favicon_name" class="text-xs text-gray-400 truncate max-w-[150px]">Nenhum arquivo escolhido</span>
                            </div>
                        </div>
                        <p class="mt-2 text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Tamanho: 32x32px, até 1MB</p>
                    </div>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('favicon')" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-8 border-t border-gray-50">
            <!-- Sidebar BG -->
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-50 rounded-lg">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4l2 2h4a2 2 0 012 2v12a4 4 0 01-4 4H7z"></path></svg>
                    </div>
                    <label class="text-sm font-bold text-gray-700">{{ __('Cor do Sidebar') }}</label>
                </div>
                <div class="flex items-center gap-0 bg-gray-50 rounded-xl border border-gray-100 overflow-hidden group hover:border-blue-200 transition-colors h-14">
                    <input id="sidebar_bg_color" name="sidebar_bg_color" type="color" class="w-16 h-full border-none cursor-pointer bg-transparent p-1" value="{{ old('sidebar_bg_color', optional($personalizacao)->sidebar_bg_color ?? '#1f2937') }}" oninput="this.nextElementSibling.innerText = this.value.toUpperCase()" />
                    <span class="flex-1 px-4 text-sm font-medium text-gray-500 uppercase tracking-wider">
                        {{ old('sidebar_bg_color', optional($personalizacao)->sidebar_bg_color ?? '#1F2937') }}
                    </span>
                </div>
                <x-input-error class="mt-1" :messages="$errors->get('sidebar_bg_color')" />
            </div>

            <!-- Sidebar Text -->
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-red-50 rounded-lg">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <label class="text-sm font-bold text-gray-700">{{ __('Cor do Texto') }}</label>
                </div>
                <div class="flex items-center gap-0 bg-gray-50 rounded-xl border border-gray-100 overflow-hidden group hover:border-red-200 transition-colors h-14">
                    <input id="sidebar_text_color" name="sidebar_text_color" type="color" class="w-16 h-full border-none cursor-pointer bg-transparent p-1" value="{{ old('sidebar_text_color', optional($personalizacao)->sidebar_text_color ?? '#ffffff') }}" oninput="this.nextElementSibling.innerText = this.value.toUpperCase()" />
                    <span class="flex-1 px-4 text-sm font-medium text-gray-500 uppercase tracking-wider">
                        {{ old('sidebar_text_color', optional($personalizacao)->sidebar_text_color ?? '#FFFFFF') }}
                    </span>
                </div>
                <x-input-error class="mt-1" :messages="$errors->get('sidebar_text_color')" />
            </div>

            <!-- Sidebar Hover -->
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-50 rounded-lg">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"></path></svg>
                    </div>
                    <label class="text-sm font-bold text-gray-700">{{ __('Cor do Hover') }}</label>
                </div>
                <div class="flex items-center gap-0 bg-gray-50 rounded-xl border border-gray-100 overflow-hidden group hover:border-indigo-200 transition-colors h-14">
                    <input id="sidebar_hover_color" name="sidebar_hover_color" type="color" class="w-16 h-full border-none cursor-pointer bg-transparent p-1" value="{{ old('sidebar_hover_color', optional($personalizacao)->sidebar_hover_color ?? '#374151') }}" oninput="this.nextElementSibling.innerText = this.value.toUpperCase()" />
                    <span class="flex-1 px-4 text-sm font-medium text-gray-500 uppercase tracking-wider">
                        {{ old('sidebar_hover_color', optional($personalizacao)->sidebar_hover_color ?? '#374151') }}
                    </span>
                </div>
                <x-input-error class="mt-1" :messages="$errors->get('sidebar_hover_color')" />
            </div>
        </div>

        <div class="flex items-center gap-4 pt-6">
            <button type="submit"
                class="bg-black text-white px-8 py-4 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-gray-800 transition active:scale-[0.98] shadow-lg shadow-gray-200/50 flex items-center gap-3">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                {{ __('Salvar Alterações') }}
            </button>

            @if (session('status') === 'personalization-updated' || session('success'))
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm text-green-600 font-bold flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ __('Salvo com sucesso!') }}
                </p>
            @endif
        </div>
    </form>

    <!-- Reusable Confirmation Modal -->
    <x-confirmation-modal name="confirm-branding-deletion" confirm="Remover" />
</section>
