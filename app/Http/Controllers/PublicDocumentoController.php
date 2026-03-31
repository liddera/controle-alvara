<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicDocumentoController extends Controller
{
    public function show(Documento $documento): StreamedResponse
    {
        $disk = config('filesystems.default');

        return Storage::disk($disk)->response(
            $documento->caminho,
            $documento->nome_arquivo,
            ['Content-Type' => $documento->tipo]
        );
    }
}

