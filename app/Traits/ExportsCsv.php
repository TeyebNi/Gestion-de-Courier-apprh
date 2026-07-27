<?php

namespace App\Traits;

trait ExportsCsv
{
    /**
     * Stream a collection as a CSV file (opens directly in Excel).
     *
     * @param  \Illuminate\Support\Collection|array  $items
     * @param  array  $headers  Column titles, e.g. ['N°', 'Nom', 'Date']
     * @param  callable  $rowMapper  function($item, $index): array
     * @param  string  $filenamePrefix
     */
    protected function streamCsv($items, array $headers, callable $rowMapper, string $filenamePrefix)
    {
        $filename = $filenamePrefix . '_' . now()->format('Y-m-d_His') . '.csv';

        $responseHeaders = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($items, $headers, $rowMapper) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 pour un bon affichage des accents dans Excel
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers, ';');

            foreach ($items as $index => $item) {
                fputcsv($handle, $rowMapper($item, $index), ';');
            }

            fclose($handle);
        }, 200, $responseHeaders);
    }
}