<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;
    protected $fillable = [
        'alvara_id',
        'nome_arquivo',
        'caminho',
        'tipo',
        'tamanho',
    ];

    public function alvara()
    {
        return $this->belongsTo(Alvara::class);
    }

    /**
     * Get the public URL for the document.
     */
    public function getUrlAttribute(): string
    {
        return \Illuminate\Support\Facades\Storage::disk(config('filesystems.default'))->url($this->caminho);
    }

    /**
     * Get the formatted file size.
     */
    public function getTamanhoFormatadoAttribute(): string
    {
        $bytes = $this->tamanho;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
