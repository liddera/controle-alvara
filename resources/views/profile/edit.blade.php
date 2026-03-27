<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Perfil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-gradient-to-br from-white via-white to-slate-50 shadow-sm">
                <div class="flex flex-col gap-8 px-6 py-7 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-slate-400">
                            {{ __('Minha conta') }}
                        </p>
                        <h3 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
                            {{ __('Gerencie seus dados com mais clareza e segurança') }}
                        </h3>
                        <p class="mt-3 max-w-xl text-sm leading-6 text-slate-600 sm:text-base">
                            {{ __('Atualize suas informações, mantenha sua foto de perfil em dia e revise a segurança da sua conta em um único espaço.') }}
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3 lg:min-w-[24rem]">
                        <div class="rounded-2xl border border-slate-200 bg-white/90 px-4 py-4 shadow-sm">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">
                                {{ __('Nome') }}
                            </p>
                            <p class="mt-2 text-sm font-semibold text-slate-900">
                                {{ auth()->user()->name }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white/90 px-4 py-4 shadow-sm">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">
                                {{ __('E-mail') }}
                            </p>
                            <p class="mt-2 truncate text-sm font-semibold text-slate-900">
                                {{ auth()->user()->email }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white/90 px-4 py-4 shadow-sm">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">
                                {{ __('Status da conta') }}
                            </p>
                            <p class="mt-2 text-sm font-semibold text-slate-900">
                                {{ auth()->user()->hasVerifiedEmail() ? __('E-mail verificado') : __('Verificação pendente') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(22rem,0.75fr)]">
                <div class="rounded-[2rem] border border-slate-200/80 bg-white p-6 shadow-sm sm:p-8">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <div class="rounded-[2rem] border border-slate-200/80 bg-white p-6 shadow-sm sm:p-8">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="rounded-[2rem] border border-red-100 bg-gradient-to-br from-white via-white to-red-50/60 p-6 shadow-sm sm:p-8">
                <div class="max-w-3xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
