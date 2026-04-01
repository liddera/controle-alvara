<?php

namespace App\Providers;

use App\Contracts\WhatsApp\WhatsAppGateway;
use App\Integrations\WhatsAppGateway\HttpV2\WhatsAppGatewayHttpV2Client;
use App\Integrations\WhatsAppGateway\NullWhatsAppGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(120)->by($this->rateLimitKey($request));
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($this->rateLimitKey($request));
        });

        RateLimiter::for('whatsapp-webhook', function (Request $request) {
            $instanceKey = $request->input('instance') ?? $request->input('data.instance');
            $key = is_string($instanceKey) && $instanceKey !== '' ? $instanceKey : $request->ip();

            return Limit::perMinute(300)->by($key);
        });

        RateLimiter::for('whatsapp-refresh', function (Request $request) {
            return Limit::perMinute(20)->by($this->rateLimitKey($request));
        });

        RateLimiter::for('dispatch-send', function (Request $request) {
            return Limit::perMinute(10)->by($this->rateLimitKey($request));
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            if (Auth::check()) {
                $ownerId = Auth::user()->owner_id ?? Auth::id();
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

    private function rateLimitKey(Request $request): string
    {
        $userId = $request->user()?->getAuthIdentifier();
        $ip = (string) $request->ip();

        if ($userId) {
            return $userId.'|'.$ip;
        }

        return $ip;
    }
}
