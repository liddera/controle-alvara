<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the user's API tokens.
     */
    public function tokens(Request $request): View
    {
        return view('profile.tokens', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(
        ProfileUpdateRequest $request,
        \App\Services\PersonalizacaoService $personalizacaoService
    ): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        if ($request->hasFile('profile_photo')) {
            $dto = \App\DTOs\ProfilePhotoDTO::fromRequest($request);
            $personalizacaoService->atualizarFotoPerfil($dto);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Create a new API token for the user.
     */
    public function storeToken(Request $request): RedirectResponse
    {
        $request->validate([
            'token_name' => ['required', 'string', 'max:255'],
        ]);

        $token = $request->user()->createToken($request->token_name);

        return Redirect::route('profile.tokens')->with('status', 'token-created')->with('plainTextToken', $token->plainTextToken);
    }

    /**
     * Delete an API token.
     */
    public function destroyToken(Request $request, $tokenId): RedirectResponse
    {
        $request->user()->tokens()->where('id', $tokenId)->delete();

        return Redirect::route('profile.tokens')->with('status', 'token-deleted');
    }
}
