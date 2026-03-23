<?php

namespace App\Filament\Resources\AlvaraResource\Pages;

use App\Filament\Resources\AlvaraResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAlvara extends EditRecord
{
    protected static string $resource = AlvaraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
