<?php

namespace App\DTOs;

use Illuminate\Http\UploadedFile;

class PersonalizacaoDTO
{
    public function __construct(
        public ?UploadedFile $header_logo = null,
        public ?UploadedFile $sidebar_compact_logo = null,
        public ?UploadedFile $favicon = null,
        public ?string $sidebar_bg_color = null,
        public ?string $sidebar_text_color = null,
        public ?string $sidebar_hover_color = null,
        public ?int $owner_id = null,
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        return new self(
            header_logo: $request->file('header_logo'),
            sidebar_compact_logo: $request->file('sidebar_compact_logo'),
            favicon: $request->file('favicon'),
            sidebar_bg_color: $request->input('sidebar_bg_color'),
            sidebar_text_color: $request->input('sidebar_text_color'),
            sidebar_hover_color: $request->input('sidebar_hover_color'),
            owner_id: auth()->user()->owner_id ?? auth()->id(),
        );
    }
}
