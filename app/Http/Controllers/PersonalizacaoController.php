<?php

namespace App\Http\Controllers;

use App\Actions\Settings\UpdatePersonalizacaoAction;
use App\Actions\Profile\UpdateProfilePhotoAction;
use Illuminate\Http\Request;

class PersonalizacaoController extends Controller
{
    public function index()
    {
        $ownerId = auth()->user()->owner_id ?? auth()->id();
        $personalizacao = app(\App\Services\PersonalizacaoService::class)->obterPorOwner($ownerId);

        return view('profile.personalization', compact('personalizacao'));
    }

    public function updateSettings(Request $request, UpdatePersonalizacaoAction $action)
    {
        $request->validate([
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:1024'],
            'sidebar_bg_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'sidebar_text_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'sidebar_hover_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ]);

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
        $ownerId = auth()->user()->owner_id ?? auth()->id();
        $personalizacao = $service->obterPorOwner($ownerId);
        $service->removerLogo($personalizacao);
        return back()->with('success', 'Logotipo removido.');
    }

    public function destroyFavicon(\App\Services\PersonalizacaoService $service)
    {
        $ownerId = auth()->user()->owner_id ?? auth()->id();
        $personalizacao = $service->obterPorOwner($ownerId);
        $service->removerFavicon($personalizacao);
        return back()->with('success', 'Favicon removido.');
    }
}
