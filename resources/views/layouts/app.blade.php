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
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: true }" class="min-h-screen bg-gray-100 flex transition-all duration-300">
            
            <!-- Sidebar -->
            <aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="transition-all duration-300 bg-gray-900 text-white flex-shrink-0 flex flex-col min-h-screen shadow-lg z-20">
                <div class="p-6 text-2xl font-bold uppercase tracking-wider border-b border-gray-800 flex items-center h-[73px]" :class="sidebarOpen ? 'justify-between' : 'justify-center'">
                    <span x-show="sidebarOpen">Alvras</span>
                    <span x-show="!sidebarOpen" x-cloak>AV</span>
                </div>
                
                <nav class="flex-1 py-6 px-3 space-y-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2 rounded-md font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="Dashboard">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        <span x-show="sidebarOpen" x-cloak class="ml-3">Dashboard</span>
                    </a>
                    
                    <a href="{{ route('empresas.index') }}" class="flex items-center px-4 py-2 rounded-md font-semibold transition {{ request()->routeIs('empresas.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="Empresas">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <span x-show="sidebarOpen" x-cloak class="ml-3">Empresas</span>
                    </a>
                    
                    <a href="#" class="flex items-center px-4 py-2 rounded-md font-semibold transition text-gray-400 hover:bg-gray-800 hover:text-white" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="Alvarás">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span x-show="sidebarOpen" x-cloak class="ml-3">Alvarás</span>
                    </a>
                    
                    <a href="#" class="flex items-center px-4 py-2 rounded-md font-semibold transition text-gray-400 hover:bg-gray-800 hover:text-white" :class="sidebarOpen ? 'justify-start' : 'justify-center'" title="Configurações">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span x-show="sidebarOpen" x-cloak class="ml-3">Configurações</span>
                    </a>
                </nav>

                <!-- Toggle Sidebar Button Bottom -->
                <div class="border-t border-gray-800 p-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="w-full flex items-center text-gray-400 hover:text-white transition" :class="sidebarOpen ? 'justify-end' : 'justify-center'" title="Recolher/Expandir menu">
                        <svg x-show="sidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                        <svg x-show="!sidebarOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
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
