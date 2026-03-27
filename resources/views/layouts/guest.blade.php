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
</head>

<body class="font-sans text-gray-900 antialiased">
    @if (request()->routeIs('login'))
        <div class="relative min-h-screen overflow-hidden bg-[#f5f1e8]">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(15,118,110,0.12),_transparent_34%),radial-gradient(circle_at_bottom_right,_rgba(180,83,9,0.10),_transparent_28%),linear-gradient(135deg,_#f8f5ef_0%,_#f2ede3_48%,_#ebe6dc_100%)]"></div>
            <div class="absolute inset-0 opacity-40 bg-[linear-gradient(to_right,rgba(15,23,42,0.05)_1px,transparent_1px),linear-gradient(to_bottom,rgba(15,23,42,0.05)_1px,transparent_1px)] bg-[size:72px_72px]"></div>

            <div class="relative mx-auto flex min-h-screen w-full max-w-7xl items-center px-6 py-10 sm:px-8 lg:px-10">
                <div class="grid w-full gap-8 lg:grid-cols-[1.15fr_0.85fr] lg:gap-12">
                    <section class="order-2 flex flex-col justify-center lg:order-1">
                        <span class="mb-6 inline-flex w-fit items-center rounded-full border border-slate-900/10 bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-[0.28em] text-slate-600 shadow-sm backdrop-blur">
                            Controle de Alvaras
                        </span>

                        <h1 class="max-w-2xl text-4xl font-semibold leading-tight text-slate-900 sm:text-5xl" style="font-family: 'Georgia', 'Times New Roman', serif;">
                            Antecipe vencimentos com mais clareza
                        </h1>

                        <p class="mt-5 max-w-xl text-base leading-7 text-slate-600 sm:text-lg">
                            Um ambiente pensado para organizar alvaras, acompanhar renovacoes e manter sua operacao em dia.
                        </p>

                        <div class="mt-10 grid gap-4 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3">
                            <article class="rounded-3xl border border-white/70 bg-white/78 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur">
                                <div class="mb-4 h-1.5 w-14 rounded-full bg-teal-600"></div>
                                <h2 class="text-lg font-semibold text-slate-900">
                                    Visao clara dos prazos
                                </h2>
                                <p class="mt-3 text-sm leading-6 text-slate-600">
                                    Acompanhe o que se aproxima e priorize cada renovacao com mais seguranca.
                                </p>
                            </article>

                            <article class="rounded-3xl border border-white/70 bg-white/78 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur">
                                <div class="mb-4 h-1.5 w-14 rounded-full bg-amber-600"></div>
                                <h2 class="text-lg font-semibold text-slate-900">
                                    Tudo no mesmo fluxo
                                </h2>
                                <p class="mt-3 text-sm leading-6 text-slate-600">
                                    Empresas, documentos e vencimentos reunidos em uma experiencia mais organizada.
                                </p>
                            </article>

                            <article class="rounded-3xl border border-white/70 bg-white/78 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur">
                                <div class="mb-4 h-1.5 w-14 rounded-full bg-sky-700"></div>
                                <h2 class="text-lg font-semibold text-slate-900">
                                    Menos urgencia na rotina
                                </h2>
                                <p class="mt-3 text-sm leading-6 text-slate-600">
                                    Receba alertas com antecedencia e conduza cada etapa com mais previsibilidade.
                                </p>
                            </article>
                        </div>
                    </section>

                    <section class="order-1 flex items-center justify-center lg:order-2 lg:justify-end">
                        <div class="w-full max-w-lg rounded-[2rem] border border-white/70 bg-white/88 p-7 shadow-[0_24px_80px_rgba(15,23,42,0.14)] backdrop-blur sm:p-9">
                            <div class="mb-8">
                                <span class="inline-flex items-center rounded-full bg-slate-900 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/90">
                                    Acesso ao sistema
                                </span>

                                <h2 class="mt-4 text-3xl font-semibold text-slate-900">
                                    Entrar
                                </h2>

                                <p class="mt-2 text-sm leading-6 text-slate-500">
                                    Acesse seu painel para acompanhar vencimentos, documentos e renovacoes.
                                </p>
                            </div>

                            <div class="space-y-5">
                                {{ $slot }}
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    @else
        <div
            class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 via-gray-200 to-gray-300 relative overflow-hidden">

            <div class="absolute inset-0 bg-gradient-to-br from-gray-50 via-gray-100 to-gray-200"></div>

            <div class="absolute inset-0 opacity-30 
                bg-[linear-gradient(to_right,rgba(0,0,0,0.04)_1px,transparent_1px),
                    linear-gradient(to_bottom,rgba(0,0,0,0.04)_1px,transparent_1px)]
                bg-[size:60px_60px]">
            </div>

            <div
                class="relative w-full max-w-6xl flex flex-col md:flex-row bg-white/80 backdrop-blur-lg shadow-2xl rounded-2xl overflow-hidden border border-gray-200">

                <div class="md:w-1/2 flex flex-col items-center justify-center 
            bg-gradient-to-br from-blue-400 via-indigo-400 to-indigo-500 
            p-12 text-white relative">
                    <div class="absolute inset-0 bg-black/10"></div>

                    <div class="relative z-10 text-center">
                        <a href="/">
                            <img src="{{ asset('logo.png') }}" alt="GEA Logo"
                                class="h-72 w-auto mb-8 mx-auto drop-shadow-xl object-contain">
                        </a>
                    </div>
                </div>

                <div class="md:w-1/2 p-10 md:p-12 flex items-center bg-white">
                    <div class="w-full max-w-md mx-auto">
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">
                            Entrar na sua conta
                        </h2>

                        <p class="text-sm text-gray-500 mb-6">
                            Acesse seu painel de controle
                        </p>

                        <div class="space-y-5">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</body>

</html>
