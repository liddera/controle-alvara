<?php

namespace App\Services;

use App\Models\Documento;
use App\DTOs\DocumentoDTO;
use Illuminate\Support\Facades\Storage;

class DocumentoService
{
    /**
     * Store a document in the configured disk and create a database record.
     */
    public function store(DocumentoDTO $dto): Documento
    {
        $disk = config('filesystems.default');
        $path = 'documentos/' . $dto->alvara_id;
        
        $caminho = $dto->arquivo->storePublicly($path, $disk);

        return Documento::create([
            'alvara_id' => $dto->alvara_id,
            'nome_arquivo' => $dto->arquivo->getClientOriginalName(),
            'caminho' => $caminho,
            'tipo' => $dto->arquivo->getMimeType(),
            'tamanho' => $dto->arquivo->getSize(),
        ]);
    }

    /**
     * Delete a document from storage and database.
     */
    public function delete(Documento $documento): void
    {
        $disk = config('filesystems.default');
        Storage::disk($disk)->delete($documento->caminho);
        $documento->delete();
    }

    /**
     * Get the public URL for a document.
     */
    public function getUrl(Documento $documento): string
    {
        $disk = config('filesystems.default');
        return Storage::disk($disk)->url($documento->caminho);
    }
}
