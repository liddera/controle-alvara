<?php

namespace App\Actions;

use App\Models\Alvara;
use App\DTOs\AlvaraFilterDTO;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportAlvarasAction
{
    public function execute(AlvaraFilterDTO $dto): StreamedResponse
    {
        $alvaras = Alvara::with('empresa')
            ->filterByDto($dto)
            ->latest()
            ->get();

        $filename = "relatorio_alvaras_" . now()->format('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'Empresa', 'Tipo de Alvará', 'Número', 'Data Vencimento', 'Status', 'Observações'];

        $callback = function () use ($alvaras, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns, ';');

            foreach ($alvaras as $alvara) {
                fputcsv($file, [
                    $alvara->id,
                    $alvara->empresa->nome ?? 'N/A',
                    $alvara->tipo,
                    $alvara->numero ?? '-',
                    $alvara->data_vencimento->format('d/m/Y'),
                    ucfirst($alvara->status),
                    $alvara->observacoes ?? '-'
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
