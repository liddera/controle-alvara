<?php

namespace App\Http\Controllers;

use App\Actions\Settings\UpdatePersonalizacaoAction;
use App\Actions\Profile\UpdateProfilePhotoAction;
use App\Http\Requests\UpdatePersonalizacaoRequest;
use Illuminate\Http\Request;

class PersonalizacaoController extends Controller
{
    public function index()
    {
        $ownerId = auth()->user()->owner_id ?? auth()->id();
        $personalizacao = app(\App\Services\PersonalizacaoService::class)->obterPorOwner($ownerId);

        return view('profile.personalization', compact('personalizacao'));
    }

    public function updateSettings(UpdatePersonalizacaoRequest $request, UpdatePersonalizacaoAction $action)
    {
        $action->execute($request);

        return back()->with('success', 'Personalização atualizada com sucesso!');
    }

    public function updateProfilePhoto(Request $request, UpdateProfilePhotoAction $action)
    {
        $request->validate([
            'profile_photo' => 'required|image|max:2048',
        ]);

        $action->execute($request);

        return back()->with('success', 'Foto de perfil atualizada!');
    }

    public function destroyProfilePhoto(\App\Services\PersonalizacaoService $service)
    {
        $service->removerFotoPerfil(auth()->user());
        return back()->with('success', 'Foto de perfil removida.');
    }

    public function destroyLogo(\App\Services\PersonalizacaoService $service)
    {
        return $this->destroyHeaderLogo($service);
    }

    public function destroyHeaderLogo(\App\Services\PersonalizacaoService $service)
    {
        $ownerId = auth()->user()->owner_id ?? auth()->id();
        $personalizacao = $service->obterPorOwner($ownerId);
        $service->removerHeaderLogo($personalizacao);
        return back()->with('success', 'Logo do header removida.');
    }

    public function destroySidebarCompactLogo(\App\Services\PersonalizacaoService $service)
    {
        $ownerId = auth()->user()->owner_id ?? auth()->id();
        $personalizacao = $service->obterPorOwner($ownerId);
        $service->removerSidebarCompactLogo($personalizacao);
        return back()->with('success', 'Logo compacta da sidebar removida.');
    }

    public function destroyFavicon(\App\Services\PersonalizacaoService $service)
    {
        $ownerId = auth()->user()->owner_id ?? auth()->id();
        $personalizacao = $service->obterPorOwner($ownerId);
        $service->removerFavicon($personalizacao);
        return back()->with('success', 'Favicon removido.');
    }
}
