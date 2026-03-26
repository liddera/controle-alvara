<?php

namespace App\DTOs;

use Illuminate\Http\UploadedFile;

class DocumentoDTO
{
    public function __construct(
        public int $alvara_id,
        public UploadedFile $arquivo
    ) {}

    public static function fromRequest(int $alvara_id, UploadedFile $arquivo): self
    {
        return new self($alvara_id, $arquivo);
    }
}
