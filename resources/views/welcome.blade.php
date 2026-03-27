<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Alvras') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-slate-900 antialiased bg-[#f5f1e8]">
    <div class="relative min-h-screen overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(15,118,110,0.12),_transparent_34%),radial-gradient(circle_at_bottom_right,_rgba(180,83,9,0.10),_transparent_28%),linear-gradient(135deg,_#f8f5ef_0%,_#f2ede3_48%,_#ebe6dc_100%)]"></div>
        <div class="absolute inset-0 opacity-40 bg-[linear-gradient(to_right,rgba(15,23,42,0.05)_1px,transparent_1px),linear-gradient(to_bottom,rgba(15,23,42,0.05)_1px,transparent_1px)] bg-[size:72px_72px]"></div>

        <div class="relative mx-auto max-w-7xl px-6 py-6 sm:px-8 lg:px-10">
            <header class="flex items-center justify-between gap-4 py-2">
                <div>
                    <span class="inline-flex items-center rounded-full border border-slate-900/10 bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-[0.28em] text-slate-600 shadow-sm backdrop-blur">
                        Controle de Alvaras
                    </span>
                </div>

                <nav class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="inline-flex items-center rounded-full border border-slate-900/10 bg-white/70 px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm backdrop-blur transition hover:border-slate-900/20 hover:text-slate-900">
                            Ir para o painel
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center rounded-full bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700">
                            Entrar
                        </a>
                    @endauth
                </nav>
            </header>

            <main class="pb-12 pt-8 sm:pt-12 lg:pt-16">
                <section class="grid items-center gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:gap-14">
                    <div>
                        <h1 class="max-w-3xl text-5xl font-semibold leading-[1.02] text-slate-900 sm:text-6xl" style="font-family: 'Georgia', 'Times New Roman', serif;">
                            Antecipe vencimentos com mais clareza
                        </h1>

                        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
                            Organize alvaras, acompanhe renovacoes e mantenha sua operacao em dia com uma visao mais clara dos prazos.
                        </p>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            @auth
                                <a href="{{ route('dashboard') }}"
                                    class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-6 py-4 text-sm font-semibold uppercase tracking-[0.18em] text-white shadow-[0_24px_60px_rgba(15,23,42,0.16)] transition hover:bg-teal-700">
                                    Acessar painel
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-6 py-4 text-sm font-semibold uppercase tracking-[0.18em] text-white shadow-[0_24px_60px_rgba(15,23,42,0.16)] transition hover:bg-teal-700">
                                    Entrar no sistema
                                </a>
                            @endauth

                            <a href="#beneficios"
                                class="inline-flex items-center justify-center rounded-2xl border border-slate-900/10 bg-white/70 px-6 py-4 text-sm font-semibold uppercase tracking-[0.18em] text-slate-700 shadow-sm backdrop-blur transition hover:border-slate-900/20 hover:text-slate-900">
                                Conhecer recursos
                            </a>
                        </div>

                        <div class="mt-10 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-3xl border border-white/70 bg-white/78 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur">
                                <p class="text-3xl font-semibold text-slate-900">1 painel</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Empresas, documentos e vencimentos em um fluxo unico.</p>
                            </div>

                            <div class="rounded-3xl border border-white/70 bg-white/78 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur">
                                <p class="text-3xl font-semibold text-slate-900">+ controle</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Alertas e visibilidade para agir antes do vencimento.</p>
                            </div>

                            <div class="rounded-3xl border border-white/70 bg-white/78 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur">
                                <p class="text-3xl font-semibold text-slate-900">- urgencia</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Rotina mais organizada e menos correria nas renovacoes.</p>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="rounded-[2rem] border border-white/70 bg-white/82 p-6 shadow-[0_30px_90px_rgba(15,23,42,0.14)] backdrop-blur sm:p-8">
                            <div class="grid gap-4">
                                <div class="rounded-3xl border border-slate-200/70 bg-[#fbfaf7] p-6">
                                    <div class="mb-4 h-1.5 w-14 rounded-full bg-teal-600"></div>
                                    <h2 class="text-xl font-semibold text-slate-900">Monitoramento de vencimentos</h2>
                                    <p class="mt-3 text-sm leading-6 text-slate-600">
                                        Visualize os proximos prazos com antecedencia e mantenha cada renovacao no radar.
                                    </p>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="rounded-3xl border border-slate-200/70 bg-white p-5">
                                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Alertas</p>
                                        <p class="mt-3 text-base font-semibold text-slate-900">Antecedencia configuravel</p>
                                        <p class="mt-2 text-sm leading-6 text-slate-600">Defina quando receber avisos e organize sua rotina com previsibilidade.</p>
                                    </div>

                                    <div class="rounded-3xl border border-slate-200/70 bg-white p-5">
                                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Agenda</p>
                                        <p class="mt-3 text-base font-semibold text-slate-900">Eventos no Google</p>
                                        <p class="mt-2 text-sm leading-6 text-slate-600">Leve alertas para a agenda e acompanhe vencimentos tambem fora do sistema.</p>
                                    </div>
                                </div>

                                <div class="rounded-3xl border border-slate-200/70 bg-slate-900 p-6 text-white">
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/60">Fluxo de trabalho</p>
                                    <div class="mt-4 grid gap-4 sm:grid-cols-3">
                                        <div>
                                            <p class="text-sm font-semibold text-white">Cadastre</p>
                                            <p class="mt-2 text-sm leading-6 text-white/70">Empresas, alvaras e documentos em um so ambiente.</p>
                                        </div>

                                        <div>
                                            <p class="text-sm font-semibold text-white">Acompanhe</p>
                                            <p class="mt-2 text-sm leading-6 text-white/70">Tenha visao clara do que exige atencao na operacao.</p>
                                        </div>

                                        <div>
                                            <p class="text-sm font-semibold text-white">Antecipe</p>
                                            <p class="mt-2 text-sm leading-6 text-white/70">Receba avisos e aja antes do vencimento apertar.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="beneficios" class="mt-20 sm:mt-24">
                    <div class="grid gap-5 lg:grid-cols-3">
                        <article class="rounded-[2rem] border border-white/70 bg-white/82 p-6 shadow-[0_20px_70px_rgba(15,23,42,0.08)] backdrop-blur">
                            <div class="mb-5 h-1.5 w-16 rounded-full bg-teal-600"></div>
                            <h3 class="text-xl font-semibold text-slate-900">Visao clara dos prazos</h3>
                            <p class="mt-4 text-sm leading-7 text-slate-600">
                                Acompanhe o que se aproxima e priorize cada renovacao com mais seguranca.
                            </p>
                        </article>

                        <article class="rounded-[2rem] border border-white/70 bg-white/82 p-6 shadow-[0_20px_70px_rgba(15,23,42,0.08)] backdrop-blur">
                            <div class="mb-5 h-1.5 w-16 rounded-full bg-amber-600"></div>
                            <h3 class="text-xl font-semibold text-slate-900">Tudo no mesmo fluxo</h3>
                            <p class="mt-4 text-sm leading-7 text-slate-600">
                                Empresas, documentos e vencimentos reunidos em uma experiencia mais organizada.
                            </p>
                        </article>

                        <article class="rounded-[2rem] border border-white/70 bg-white/82 p-6 shadow-[0_20px_70px_rgba(15,23,42,0.08)] backdrop-blur">
                            <div class="mb-5 h-1.5 w-16 rounded-full bg-sky-700"></div>
                            <h3 class="text-xl font-semibold text-slate-900">Menos urgencia na rotina</h3>
                            <p class="mt-4 text-sm leading-7 text-slate-600">
                                Receba alertas com antecedencia e conduza cada etapa com mais previsibilidade.
                            </p>
                        </article>
                    </div>
                </section>

                <section class="mt-20 grid gap-5 lg:grid-cols-[0.9fr_1.1fr]">
                    <div class="rounded-[2rem] border border-white/70 bg-white/82 p-7 shadow-[0_20px_70px_rgba(15,23,42,0.08)] backdrop-blur">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Para a operacao</p>
                        <h2 class="mt-4 text-3xl font-semibold text-slate-900">Mais organizacao para quem precisa agir no prazo</h2>
                        <p class="mt-4 text-sm leading-7 text-slate-600">
                            O Alvras foi pensado para dar visibilidade aos vencimentos, centralizar informacoes e reduzir a dependencia de controles paralelos.
                        </p>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="rounded-[2rem] border border-white/70 bg-white/82 p-6 shadow-[0_20px_70px_rgba(15,23,42,0.08)] backdrop-blur">
                            <h3 class="text-lg font-semibold text-slate-900">Cadastro centralizado</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">Empresas, alvaras e documentos organizados em um unico ambiente de consulta.</p>
                        </div>

                        <div class="rounded-[2rem] border border-white/70 bg-white/82 p-6 shadow-[0_20px_70px_rgba(15,23,42,0.08)] backdrop-blur">
                            <h3 class="text-lg font-semibold text-slate-900">Alertas configuraveis</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">Defina a antecedencia dos avisos e acompanhe o que realmente exige acao.</p>
                        </div>

                        <div class="rounded-[2rem] border border-white/70 bg-white/82 p-6 shadow-[0_20px_70px_rgba(15,23,42,0.08)] backdrop-blur">
                            <h3 class="text-lg font-semibold text-slate-900">Google Agenda</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">Crie eventos no Google Calendar para reforcar o acompanhamento dos vencimentos.</p>
                        </div>

                        <div class="rounded-[2rem] border border-white/70 bg-white/82 p-6 shadow-[0_20px_70px_rgba(15,23,42,0.08)] backdrop-blur">
                            <h3 class="text-lg font-semibold text-slate-900">Painel de controle</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">Tenha uma visao mais objetiva do que vencer, do que renovar e do que priorizar.</p>
                        </div>
                    </div>
                </section>

                <section class="mt-20">
                    <div class="rounded-[2.25rem] border border-slate-900/10 bg-slate-900 px-7 py-10 text-white shadow-[0_30px_90px_rgba(15,23,42,0.22)] sm:px-10">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                            <div class="max-w-2xl">
                                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-white/60">Pronto para acessar</p>
                                <h2 class="mt-4 text-3xl font-semibold sm:text-4xl">Mantenha vencimentos, documentos e renovacoes sob controle.</h2>
                                <p class="mt-4 text-sm leading-7 text-white/70">
                                    Entre no sistema e acompanhe sua operacao com mais previsibilidade.
                                </p>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row">
                                @auth
                                    <a href="{{ route('dashboard') }}"
                                        class="inline-flex items-center justify-center rounded-2xl bg-white px-6 py-4 text-sm font-semibold uppercase tracking-[0.18em] text-slate-900 transition hover:bg-teal-50">
                                        Ir para o painel
                                    </a>
                                @else
                                    <a href="{{ route('login') }}"
                                        class="inline-flex items-center justify-center rounded-2xl bg-white px-6 py-4 text-sm font-semibold uppercase tracking-[0.18em] text-slate-900 transition hover:bg-teal-50">
                                        Entrar no sistema
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</body>

</html>
