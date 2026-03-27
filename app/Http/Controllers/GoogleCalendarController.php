<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class GoogleCalendarController extends Controller
{
    public function __construct(private GoogleCalendarService $googleCalendarService) {}

    public function redirect(): RedirectResponse
    {
        try {
            return $this->googleCalendarService->redirectToProvider();
        } catch (\Throwable $exception) {
            return redirect()
                ->route('profile.alerts')
                ->with('error', $exception->getMessage());
        }
    }

    public function callback(): RedirectResponse
    {
        try {
            $user = $this->googleCalendarService->handleCallback(auth()->user());
            Auth::login($user);
            request()->session()->regenerate();

            return redirect()
                ->route('profile.alerts')
                ->with('success', 'Google Agenda conectada com sucesso!');
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('profile.alerts')
                ->with('error', 'Nao foi possivel concluir a conexao com o Google. '.$exception->getMessage());
        }
    }

    public function disconnect(): RedirectResponse
    {
        $this->googleCalendarService->disconnect(auth()->user());

        return redirect()
            ->route('profile.alerts')
            ->with('success', 'Google Agenda desconectada com sucesso!');
    }
}
