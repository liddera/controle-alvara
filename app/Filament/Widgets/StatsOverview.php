<?php

namespace App\Filament\Widgets;

use App\Models\Alvara;
use App\Models\Empresa;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Clientes Cadastrados', Empresa::count())
                ->description('Número total de clientes')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('info'),
            Stat::make('Total Documentos Cadastrados', Alvara::count())
                ->description('Número total de documentos')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),
        ];
    }
}
