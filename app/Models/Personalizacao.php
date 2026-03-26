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
        'favicon_path',
        'sidebar_bg_color',
        'sidebar_text_color',
        'sidebar_hover_color',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::disk(config('filesystems.default'))->url($this->logo_path) : null;
    }

    public function getFaviconUrlAttribute(): ?string
    {
        return $this->favicon_path ? Storage::disk(config('filesystems.default'))->url($this->favicon_path) : null;
    }
}
