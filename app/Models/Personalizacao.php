<?php

namespace App\Models;

use App\Traits\HasOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Personalizacao extends Model
{
    use HasOwner;

    protected $table = 'personalizacoes';

    protected $fillable = [
        'owner_id',
        'logo_path',
        'header_logo_path',
        'sidebar_compact_logo_path',
        'favicon_path',
        'sidebar_bg_color',
        'sidebar_text_color',
        'sidebar_hover_color',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        return $this->pathToUrl($this->header_logo_path ?: $this->logo_path);
    }

    public function getHeaderLogoUrlAttribute(): ?string
    {
        return $this->pathToUrl($this->header_logo_path ?: $this->logo_path);
    }

    public function getSidebarCompactLogoUrlAttribute(): ?string
    {
        return $this->pathToUrl($this->sidebar_compact_logo_path ?: $this->header_logo_path ?: $this->logo_path);
    }

    public function getFaviconUrlAttribute(): ?string
    {
        return $this->pathToUrl($this->favicon_path);
    }

    private function pathToUrl(?string $path): ?string
    {
        return $path ? Storage::disk(config('filesystems.default'))->url($path) : null;
    }
}
