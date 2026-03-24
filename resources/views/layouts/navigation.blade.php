<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 sticky top-0 z-10 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('logo.png') }}" alt="Logo" class="block h-14 w-auto">
                    </a>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-4">

                <!-- 🔔 NOTIFICAÇÕES -->
                <x-dropdown align="right" width="128">
                    <x-slot name="trigger">
                        <button
                            class="relative p-2 text-gray-500 hover:text-amber-500 transition transform hover:scale-105">

                            <!-- 🔔 ÍCONE COM FUNDO -->
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

                                    <!-- 🔴 ÍCONE MENOR -->
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

                                            <!-- 🔴 TÍTULO EM VERMELHO -->
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
                        <button class="inline-flex items-center px-3 py-2 text-sm text-gray-500 hover:text-gray-700">
                            <div>{{ Auth::user()->name }}</div>
                            <svg class="ml-1 w-4 h-4 fill-current" viewBox="0 0 20 20">
                                <path d="M5.293 7.293L10 12l4.707-4.707 1.414 1.414L10 14.828 3.879 8.707z" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Profile
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Log Out
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