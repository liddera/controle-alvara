<?php

namespace App\Services;

use App\Models\Personalizacao;
use App\Models\User;
use App\DTOs\PersonalizacaoDTO;
use App\DTOs\ProfilePhotoDTO;
use Illuminate\Http\UploadedFile;
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

        if ($dto->header_logo) {
            $this->deleteIfExists($personalizacao->header_logo_path, $disk);

            $path = $this->storeHeaderLogo($dto->header_logo, (int) $dto->owner_id, $disk);
            $personalizacao->header_logo_path = $path;
            $personalizacao->logo_path = $path;
        }

        if ($dto->sidebar_compact_logo) {
            $this->deleteIfExists($personalizacao->sidebar_compact_logo_path, $disk);

            $personalizacao->sidebar_compact_logo_path = $this->storeSidebarCompactLogo(
                $dto->sidebar_compact_logo,
                (int) $dto->owner_id,
                $disk
            );
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
        $this->removerHeaderLogo($personalizacao);
    }

    public function removerHeaderLogo(Personalizacao $personalizacao): void
    {
        if (!$personalizacao->header_logo_path && !$personalizacao->logo_path) {
            return;
        }

        $disk = config('filesystems.default');
        $previousHeaderPath = $personalizacao->header_logo_path;

        $this->deleteIfExists($previousHeaderPath, $disk);

        if (
            $personalizacao->logo_path
            && (!$previousHeaderPath || $personalizacao->logo_path === $previousHeaderPath)
        ) {
            $this->deleteIfExists($personalizacao->logo_path, $disk);
            $personalizacao->logo_path = null;
        }

        $personalizacao->header_logo_path = null;
        $personalizacao->save();
    }

    public function removerSidebarCompactLogo(Personalizacao $personalizacao): void
    {
        if (!$personalizacao->sidebar_compact_logo_path) {
            return;
        }

        $this->deleteIfExists($personalizacao->sidebar_compact_logo_path, config('filesystems.default'));
        $personalizacao->sidebar_compact_logo_path = null;
        $personalizacao->save();
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

    private function storeHeaderLogo(UploadedFile $file, int $ownerId, string $disk): string
    {
        $image = $this->manager->read($file->getRealPath());
        $image->scale(height: 96);
        $encoded = $image->toPng();

        $path = 'personalizacao/header_logo_' . $ownerId . '.png';
        Storage::disk($disk)->put($path, (string) $encoded);

        return $path;
    }

    private function storeSidebarCompactLogo(UploadedFile $file, int $ownerId, string $disk): string
    {
        $image = $this->manager->read($file->getRealPath());
        $image->scaleDown(width: 72, height: 72);
        $encoded = $image->toPng();

        $path = 'personalizacao/sidebar_compact_logo_' . $ownerId . '.png';
        Storage::disk($disk)->put($path, (string) $encoded);

        return $path;
    }

    private function deleteIfExists(?string $path, string $disk): void
    {
        if ($path) {
            Storage::disk($disk)->delete($path);
        }
    }
}
