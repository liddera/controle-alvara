<?php

namespace App\Http\Controllers;

use App\Actions\Alerts\UpsertAlertConfigAction;
use App\Http\Requests\StoreAlertConfigRequest;
use App\Models\AlertConfig;
use App\Models\TipoAlvara;
use App\Services\AlertConfigService;
use App\Services\GoogleCalendarService;
use App\Services\PersonalizacaoService;
use App\Services\WhatsApp\OwnerWhatsAppInstanceService;
use App\Services\WhatsApp\WhatsAppStatusPresenter;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AlertSettingsController extends Controller
{
    public function __construct(
        private AlertConfigService $service,
        private PersonalizacaoService $personalizacaoService,
        private GoogleCalendarService $googleCalendarService,
        private OwnerWhatsAppInstanceService $whatsAppInstanceService,
    ) {}

    public function index(): View
    {
        $user = auth()->user();

        Log::info('Profile alerts: request started.', [
            'user_id' => auth()->id(),
            'has_user' => (bool) $user,
        ]);

        try {
            $ownerId = $user?->owner_id ?? auth()->id();
            Log::info('Profile alerts: owner resolved.', ['owner_id' => $ownerId]);

            $googleCalendarStatus = $this->googleCalendarService->getConnectionStatus($user);
            Log::info('Profile alerts: Google status loaded.', ['status' => $googleCalendarStatus]);

            $whatsAppStatus = $this->whatsAppInstanceService->getConnectionStatus((int) $ownerId);
            Log::info('Profile alerts: WhatsApp status loaded.', ['status' => $whatsAppStatus]);

            $whatsAppInstance = $this->whatsAppInstanceService->findForOwner((int) $ownerId);
            Log::info('Profile alerts: WhatsApp instance loaded.', [
                'has_instance' => (bool) $whatsAppInstance,
            ]);

            $whatsAppStatusView = app(WhatsAppStatusPresenter::class)->present($whatsAppStatus);
            Log::info('Profile alerts: WhatsApp presenter built.');

            $configs = $this->service->listarPorUsuario((int) auth()->id());
            Log::info('Profile alerts: alert configs loaded.', ['count' => $configs->count()]);

            $tiposAlvara = TipoAlvara::all();
            Log::info('Profile alerts: tipos alvara loaded.', ['count' => $tiposAlvara->count()]);

            $personalizacao = $this->personalizacaoService->obterPorOwner((int) $ownerId);
            Log::info('Profile alerts: personalization loaded.');

            return view('profile.alerts', [
                'configs' => $configs,
                'tiposAlvara' => $tiposAlvara,
                'ownerAlertEmail' => $user?->email,
                'googleCalendarStatus' => $googleCalendarStatus,
                'whatsAppStatus' => $whatsAppStatus,
                'whatsAppStatusView' => $whatsAppStatusView,
                'whatsAppInstance' => $whatsAppInstance,
                'personalizacao' => $personalizacao,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Profile alerts: request failed.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            throw $exception;
        }
    }

    public function store(StoreAlertConfigRequest $request, UpsertAlertConfigAction $action)
    {
        $action->execute($request);

        return back()->with('success', 'Configuração de alerta salva!');
    }

    public function destroy(AlertConfig $config)
    {
        Gate::authorize('delete', $config);
        $this->service->excluir($config);

        return back()->with('success', 'Alerta removido.');
    }

    public function readAndRedirect(DatabaseNotification $notification)
    {
        $notification->markAsRead();

        $alvaraId = $notification->data['alvara_id'] ?? null;

        if ($alvaraId) {
            return redirect()->route('alvaras.show', $alvaraId);
        }

        return back();
    }
}
