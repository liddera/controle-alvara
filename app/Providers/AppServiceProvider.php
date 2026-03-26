<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            if (auth()->check()) {
                $ownerId = auth()->user()->owner_id ?? auth()->id();
                $personalizacao = app(\App\Services\PersonalizacaoService::class)->obterPorOwner($ownerId);
                $view->with('personalizacao', $personalizacao);
            } else {
                // Default fallback for guest views
                $view->with('personalizacao', new \App\Models\Personalizacao([
                    'sidebar_bg_color' => '#1f2937',
                    'sidebar_text_color' => '#ffffff',
                ]));
            }
        });
    }
}
