<?php

namespace App\Actions\Alvaras;

use App\DTOs\DocumentoDTO;
use App\Services\DocumentoService;
use Illuminate\Http\Request;
use App\Models\Alvara;

class UploadDocumentosAction
{
    public function __construct(
        protected DocumentoService $documentoService
    ) {}

    /**
     * Handle the upload of multiple documents for an Alvara.
     */
    public function execute(Alvara $alvara, Request $request): void
    {
        if (!$request->hasFile('documentos')) {
            return;
        }

        foreach ($request->file('documentos') as $arquivo) {
            $dto = DocumentoDTO::fromRequest($alvara->id, $arquivo);
            $this->documentoService->store($dto);
        }
    }
}
