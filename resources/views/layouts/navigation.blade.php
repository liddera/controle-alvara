<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 sticky top-0 z-10 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 relative">

            <!-- Espaço vazio à esquerda -->
            <div class="w-1/3"></div>

            <!-- 🔥 LOGO CENTRALIZADA -->
            <div class="absolute left-1/2 transform -translate-x-1/2">
                <a href="{{ route('dashboard') }}">
                    <img src="{{ $personalizacao->logo_url ?? asset('GEAlogo-Photoroom.png') }}" alt="Logo"
                        class="block h-16 w-auto transition hover:scale-105 {{ $personalizacao->logo_url ? '' : 'brightness-125 contrast-125' }}">
                </a>
            </div>

            <!-- Direita: notificações + usuário -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-4 ml-auto">

                <!-- 🔔 NOTIFICAÇÕES -->
                <x-dropdown align="right" width="128">
                    <x-slot name="trigger">
                        <button
                            class="relative p-2 text-gray-500 hover:text-amber-500 transition transform hover:scale-105">

                            <div
                                class="w-9 h-9 flex items-center justify-center rounded-full bg-amber-100 border border-amber-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1">
                                    </path>
                                </svg>
                            </div>

                            @if(auth()->user()->unreadNotifications->count() > 0)
                            <span
                                class="absolute top-0 right-0 text-[10px] font-bold text-white bg-red-600 px-1.5 py-[2px] rounded-full border border-white">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                            @endif

                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Notificações</h3>

                            @if(auth()->user()->unreadNotifications->count() > 0)
                            <form method="POST" action="{{ route('notifications.mark-as-read') }}">
                                @csrf
                                <button type="submit" class="text-xs text-amber-500 hover:text-amber-600">
                                    Limpar
                                </button>
                            </form>
                            @endif
                        </div>

                        <div class="max-h-[340px] overflow-y-auto divide-y divide-gray-50">

                            @forelse(auth()->user()->unreadNotifications->take(10) as $notification)

                            <a href="{{ route('notifications.read', $notification->id) }}"
                                class="block px-5 py-3 hover:bg-gray-50 transition group relative">

                                <div class="flex items-start gap-3">

                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-7 h-7 bg-red-100 rounded-full flex items-center justify-center border border-red-200">
                                            <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start mb-1">
                                            <p class="text-sm font-semibold text-red-600 leading-tight">
                                                Alerta de Vencimento
                                            </p>

                                            <span class="text-[10px] text-gray-400 ml-2">
                                                {{ $notification->created_at->diffForHumans(short: true) }}
                                            </span>
                                        </div>

                                        <p class="text-xs text-gray-600 leading-relaxed">
                                            {{ $notification->data['message'] }}
                                        </p>
                                    </div>
                                </div>

                            </a>

                            @empty
                            <div class="px-5 py-10 text-center">
                                <p class="text-xs text-gray-400 italic">
                                    Sem notificações
                                </p>
                            </div>
                            @endforelse

                        </div>
                    </x-slot>
                </x-dropdown>

                <!-- 👤 USER -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center gap-2 px-1 py-1 pr-3 border border-gray-200 rounded-full bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition ease-in-out duration-150 shadow-sm ml-2">
                            @php
                            $nameParts = explode(' ', Auth::user()->name);
                            $initials = substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0,
                            1) : '');
                            @endphp

                            @if(auth()->user()->profile_photo_url)
                                <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover shadow-inner border border-gray-200">
                            @else
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-800 text-gray-200 font-bold text-xs uppercase shadow-inner">
                                    {{ $initials }}
                                </div>
                            @endif

                            <div class="text-sm font-bold text-gray-700 hidden sm:block">
                                {{ Auth::user()->name }}
                            </div>

                            <svg class="w-4 h-4 text-gray-400 ms-1 mr-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 rounded-t-md">
                            <p class="text-xs text-gray-500">Logado como</p>
                            <p class="text-sm font-semibold truncate text-gray-800">{{ Auth::user()->email }}</p>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            <div class="flex items-center gap-2 font-medium">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Meu Perfil
                            </div>
                        </x-dropdown-link>

                        <div class="border-t border-gray-100 my-1"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                <div class="flex items-center gap-2 font-medium text-red-600 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                        </path>
                                    </svg>
                                    Sair do Sistema
                                </div>
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>

            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="p-2 text-gray-400 hover:bg-gray-100 rounded-md">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open}" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

        </div>
    </div>
</nav>