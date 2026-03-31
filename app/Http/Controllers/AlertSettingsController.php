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
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Gate;
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
        $ownerId = auth()->user()->owner_id ?? auth()->id();
        $googleCalendarStatus = $this->googleCalendarService->getConnectionStatus(auth()->user());
        $whatsAppStatus = $this->whatsAppInstanceService->getConnectionStatus($ownerId);
        $whatsAppInstance = $this->whatsAppInstanceService->findForOwner($ownerId);

        return view('profile.alerts', [
            'configs' => $this->service->listarPorUsuario(auth()->id()),
            'tiposAlvara' => TipoAlvara::all(),
            'ownerAlertEmail' => auth()->user()->email,
            'googleCalendarStatus' => $googleCalendarStatus,
            'whatsAppStatus' => $whatsAppStatus,
            'whatsAppInstance' => $whatsAppInstance,
            'personalizacao' => $this->personalizacaoService->obterPorOwner($ownerId),
        ]);
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
