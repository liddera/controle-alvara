<?php

namespace App\Http\Controllers;

use App\Models\AlertConfig;
use App\Models\TipoAlvara;
use App\Services\AlertConfigService;
use App\Actions\Alerts\UpsertAlertConfigAction;
use App\Http\Requests\StoreAlertConfigRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Notifications\DatabaseNotification;

class AlertSettingsController extends Controller
{
    public function __construct(private AlertConfigService $service) {}

    public function index(): View
    {
        return view('profile.alerts', [
            'configs' => $this->service->listarPorUsuario(auth()->id()),
            'tiposAlvara' => TipoAlvara::all(),
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
