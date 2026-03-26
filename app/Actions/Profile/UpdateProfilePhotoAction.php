<?php

namespace App\Actions\Profile;

use App\DTOs\ProfilePhotoDTO;
use App\Models\User;
use App\Services\PersonalizacaoService;
use Illuminate\Http\Request;

class UpdateProfilePhotoAction
{
    public function __construct(private PersonalizacaoService $service) {}

    public function execute(Request $request): User
    {
        $dto = ProfilePhotoDTO::fromRequest($request);
        return $this->service->atualizarFotoPerfil($dto);
    }
}
