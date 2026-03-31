<?php

namespace App\Providers;

use App\Contracts\WhatsApp\WhatsAppGateway;
use App\Integrations\WhatsAppGateway\HttpV2\WhatsAppGatewayHttpV2Client;
use App\Integrations\WhatsAppGateway\NullWhatsAppGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WhatsAppGateway::class, function () {
            $config = config('services.whatsapp_gateway', []);
            $baseUrl = $config['base_url'] ?? null;
            $apiKey = $config['api_key'] ?? null;

            if (! filled($baseUrl) || ! filled($apiKey)) {
                return new NullWhatsAppGateway();
            }

            $provider = (string) ($config['provider'] ?? 'http-v2');

            return match ($provider) {
                'http-v2' => new WhatsAppGatewayHttpV2Client((string) $baseUrl, (string) $apiKey),
                default => new NullWhatsAppGateway(),
            };
        });
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
