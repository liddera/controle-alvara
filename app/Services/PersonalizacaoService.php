<?php

namespace App\Services;

use App\Models\Personalizacao;
use App\Models\User;
use App\DTOs\PersonalizacaoDTO;
use App\DTOs\ProfilePhotoDTO;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PersonalizacaoService
{
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    public function salvar(PersonalizacaoDTO $dto): Personalizacao
    {
        $personalizacao = Personalizacao::firstOrNew(['owner_id' => $dto->owner_id]);
        $disk = config('filesystems.default');

        if ($dto->logo) {
            // Process logo with Intervention
            $image = $this->manager->read($dto->logo->getRealPath());
            $image->scale(height: 100); // Scale to 100px height maintain aspect ratio
            $encoded = $image->toPng();
            
            $path = 'personalizacao/logo_' . $dto->owner_id . '.png';
            Storage::disk($disk)->put($path, (string) $encoded);
            $personalizacao->logo_path = $path;
        }

        if ($dto->favicon) {
            // Process favicon
            $image = $this->manager->read($dto->favicon->getRealPath());
            $image->cover(32, 32); // Square 32x32
            $encoded = $image->toPng();
            
            $path = 'personalizacao/favicon_' . $dto->owner_id . '.png';
            Storage::disk($disk)->put($path, (string) $encoded);
            $personalizacao->favicon_path = $path;
        }

        if ($dto->sidebar_bg_color) {
            $personalizacao->sidebar_bg_color = $dto->sidebar_bg_color;
        }

        if ($dto->sidebar_text_color) {
            $personalizacao->sidebar_text_color = $dto->sidebar_text_color;
        }

        if ($dto->sidebar_hover_color) {
            $personalizacao->sidebar_hover_color = $dto->sidebar_hover_color;
        }

        $personalizacao->save();

        return $personalizacao;
    }

    public function atualizarFotoPerfil(ProfilePhotoDTO $dto): User
    {
        $user = User::findOrFail($dto->user_id);
        $disk = config('filesystems.default');

        // Process profile photo
        $image = $this->manager->read($dto->photo->getRealPath());
        $image->cover(200, 200); // Square 200x200
        $encoded = $image->toWebp();
        
        $path = 'perfil/foto_' . $dto->user_id . '.webp';
        Storage::disk($disk)->put($path, (string) $encoded);
        
        $user->profile_photo_path = $path;
        $user->save();

        return $user;
    }

    public function obterPorOwner(int $ownerId): Personalizacao
    {
        return Personalizacao::firstOrNew(['owner_id' => $ownerId]);
    }

    public function removerLogo(Personalizacao $personalizacao): void
    {
        if ($personalizacao->logo_path) {
            Storage::disk(config('filesystems.default'))->delete($personalizacao->logo_path);
            $personalizacao->logo_path = null;
            $personalizacao->save();
        }
    }

    public function removerFavicon(Personalizacao $personalizacao): void
    {
        if ($personalizacao->favicon_path) {
            Storage::disk(config('filesystems.default'))->delete($personalizacao->favicon_path);
            $personalizacao->favicon_path = null;
            $personalizacao->save();
        }
    }

    public function removerFotoPerfil(User $user): void
    {
        if ($user->profile_photo_path) {
            Storage::disk(config('filesystems.default'))->delete($user->profile_photo_path);
            $user->profile_photo_path = null;
            $user->save();
        }
    }
}
