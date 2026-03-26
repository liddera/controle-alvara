<?php

namespace App\DTOs;

use Illuminate\Http\UploadedFile;

class ProfilePhotoDTO
{
    public function __construct(
        public UploadedFile $photo,
        public int $user_id,
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        return new self(
            photo: $request->file('profile_photo'),
            user_id: auth()->id(),
        );
    }
}
