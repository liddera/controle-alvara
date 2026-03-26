<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/sidebar.css', 'resources/js/preview.js'])
    <style>
        [x-cloak] {
            display: none !important;
        }

        :root {
            --sidebar-bg: {{ $personalizacao->sidebar_bg_color ?? '#111827' }};
            --sidebar-text: {{ $personalizacao->sidebar_text_color ?? '#ffffff' }};
            --sidebar-hover-bg: {{ $personalizacao->sidebar_hover_color ?? '#1f2937' }};
        }
    </style>
    @if($personalizacao->favicon_url)
    <link rel="icon" type="image/png" href="{{ $personalizacao->favicon_url }}">
    @endif
</head>

<body class="font-sans antialiased">
    <div x-data="{ sidebarOpen: true }" class="min-h-screen bg-gray-100 flex transition-all duration-300">

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-20'"
            style="background-color: var(--sidebar-bg); color: var(--sidebar-text);"
            class="transition-all duration-300 flex-shrink-0 flex flex-col h-screen sticky top-0 shadow-lg z-20 border-r border-gray-800 overflow-y-auto">
            <div class="p-4 border-b border-gray-800 flex items-center justify-center min-h-[80px]">

                <!-- Logo grande (sidebar aberto) -->
                <img x-show="sidebarOpen" src="{{ $personalizacao->header_logo_url ?? asset('GEAlogo-Photoroom.png') }}"
                    alt="Logo"
                    class="h-16 w-auto transition-all duration-300 hover:scale-105">

                <!-- Logo pequena (sidebar fechado) -->
                <img x-show="!sidebarOpen" x-cloak
                    src="{{ $personalizacao->sidebar_compact_logo_url ?? asset('sidebralogo.png') }}" alt="Logo compacto"
                    class="h-10 w-auto transition-all duration-300 hover:scale-110 opacity-95">
            </div>
            <nav class="flex-1 py-6 px-3 space-y-2">
                <a href="{{ route('dashboard') }}"
                    class="flex items-center px-4 py-2 rounded-md font-semibold transition {{ request()->routeIs('dashboard') ? 'sidebar-active' : 'sidebar-custom-text-opacity' }}"
                    :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="Dashboard">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                    <span x-show="sidebarOpen" x-cloak class="ml-3">Dashboard</span>
                </a>

                @unlessrole('super-admin')
                <a href="{{ route('empresas.index') }}"
                    class="flex items-center px-4 py-2 rounded-md font-semibold transition {{ request()->routeIs('empresas.*') ? 'sidebar-active' : 'sidebar-custom-text-opacity' }}"
                    :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="Empresas">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                    <span x-show="sidebarOpen" x-cloak class="ml-3">Empresas</span>
                </a>
                @endunlessrole

                @unlessrole('super-admin')
                <a href="{{ route('alvaras.index') }}"
                    class="flex items-center px-4 py-2 rounded-md font-semibold transition {{ request()->routeIs('alvaras.*') ? 'sidebar-active' : 'sidebar-custom-text-opacity' }}"
                    :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="Alvarás">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <span x-show="sidebarOpen" x-cloak class="ml-3">Alvarás</span>
                </a>
                @endunlessrole

{{-- 
                @role('owner')
                <a href="{{ route('users.index') }}"
                    class="flex items-center px-4 py-2 rounded-md font-semibold transition {{ request()->routeIs('users.*') ? 'sidebar-active' : 'sidebar-custom-text-opacity' }}"
                    :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="Equipe">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                    <span x-show="sidebarOpen" x-cloak class="ml-3">Equipe</span>
                </a>
                @endrole
--}}

                {{-- Links de admin para o site Blade removidos, pois o Super Admin agora usa o Filament --}}

{{-- 
                <a href="{{ route('profile.edit') }}"
                    class="flex items-center px-4 py-2 rounded-md font-semibold transition {{ request()->routeIs('profile.edit') ? 'sidebar-active' : 'sidebar-custom-text-opacity' }}"
                    :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="Meu Perfil">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-cloak class="ml-3">Meu Perfil</span>
                </a>
--}}

                @role('owner')
                <div
                    x-data="{ open: {{ request()->routeIs('profile.tokens') || request()->routeIs('profile.alerts') || request()->routeIs('profile.personalization') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="w-full flex items-center px-4 py-2 rounded-md font-semibold transition {{ request()->routeIs('profile.tokens') || request()->routeIs('profile.alerts') || request()->routeIs('profile.personalization') ? 'sidebar-active' : 'sidebar-custom-text-opacity hover:sidebar-active' }}"
                        :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                        title="Configurações">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span x-show="sidebarOpen" x-cloak class="ml-3 flex-1 text-left">Configurações</span>
                        <svg x-show="sidebarOpen" class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>

                    <div x-show="open && sidebarOpen" x-cloak x-transition class="mt-2 ml-10 space-y-1">
                        <a href="{{ route('profile.tokens') }}"
                            class="block px-4 py-2 text-sm {{ request()->routeIs('profile.tokens') ? 'sidebar-active font-bold' : 'sidebar-sub-item hover:sidebar-active' }} rounded-md">
                            Tokens de API
                        </a>
                        <a href="{{ route('profile.alerts') }}"
                            class="block px-4 py-2 text-sm {{ request()->routeIs('profile.alerts') ? 'sidebar-active font-bold' : 'sidebar-sub-item hover:sidebar-active' }} rounded-md">
                            Alertas de Vencimento
                        </a>
                        <a href="{{ route('profile.personalization') }}"
                            class="block px-4 py-2 text-sm {{ request()->routeIs('profile.personalization') ? 'sidebar-active font-bold' : 'sidebar-sub-item hover:sidebar-active' }} rounded-md">
                            Personalização
                        </a>
                    </div>
                </div>
                @endrole
            </nav>

            <!-- Toggle Sidebar Button Bottom -->
            <div class="border-t border-gray-800 p-4">
                <button @click="sidebarOpen = !sidebarOpen"
                    class="w-full h-12 flex items-center px-4 rounded-xl sidebar-custom-text-opacity transition"
                    :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="Recolher/Expandir menu">
                    <div class="flex items-center justify-center w-6 h-6 shrink-0">
                        <svg x-show="sidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                        </svg>
                        <svg x-show="!sidebarOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </button>
            </div>
        </aside>

        <!-- Main Content Container -->
        <div class="flex-1 flex flex-col min-w-0">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>

</html>
