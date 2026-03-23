<?php

namespace App\Filament\Resources\AlvaraResource\Pages;

use App\Filament\Resources\AlvaraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAlvaras extends ListRecords
{
    protected static string $resource = AlvaraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
