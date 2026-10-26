<?php

declare(strict_types=1);

namespace App\Support;

final class ReportPdf
{
    /**
     * @param array<string, mixed> $report
     * @param array<int, array<string, mixed>> $files
     */
    public static function render(array $report, array $files = []): string
    {
        $pdf = new SimplePdf((string) $report['title']);
        $ratios = Financials::ratios($report);

        $pdf->title((string) $report['title']);
        $pdf->label('Resumen');
        $pdf->paragraph((string) $report['summary'], 12, 17);
        $pdf->small('Folio ' . $report['folio'] . ' · Periodo ' . format_date($report['period_start'] ?? null, 'd/m/Y')
            . ' al ' . format_date($report['period_end'] ?? null, 'd/m/Y') . ' · Moneda ' . $report['currency']);
        if (!empty($report['university_name'])) {
            $pdf->small('Autor: ' . ($report['author_name'] ?? '') . ' · ' . $report['university_name']);
        }
        $pdf->space(6);

        $pdf->heading('Ficha del reporte');
        $pdf->paragraph('Sector: ' . ($report['sector_name'] ?? '—') . ' · Cadena: ' . ($report['chain_name'] ?? 'No especificada')
            . ' · Estado: ' . ($report['state_name'] ?? 'No especificado'));
        $pdf->paragraph('Tipo de empresa: ' . company_type_label($report['company_type'] ?? null));

        $pdf->heading('Estado de resultados y balance (cifras principales)');
        $rows = [
            'Ingresos totales' => money($report['revenue'] ?? null, (string) $report['currency']),
            'Costo de ventas' => money($report['cogs'] ?? null, (string) $report['currency']),
            'Utilidad bruta' => money($ratios['gross_profit'], (string) $report['currency']),
            'Gastos de operación' => money($report['opex'] ?? null, (string) $report['currency']),
            'Utilidad de operación' => money($ratios['operating_profit'], (string) $report['currency']),
            'Utilidad neta' => money($report['net_income'] ?? null, (string) $report['currency']),
            'Activo total' => money($report['total_assets'] ?? null, (string) $report['currency']),
            'Pasivo total' => money($report['total_liabilities'] ?? null, (string) $report['currency']),
            'Capital contable' => money($report['equity'] ?? null, (string) $report['currency']),
        ];
        foreach ($rows as $label => $value) {
            $pdf->paragraph($label . ': ' . $value, 9.5, 13);
        }

        $pdf->heading('Indicadores calculados');
        $pdf->paragraph('Margen bruto: ' . pct($ratios['gross_margin']) . ' · Margen de operación: ' . pct($ratios['operating_margin'])
            . ' · Margen neto: ' . pct($ratios['net_margin']));
        $pdf->paragraph('Razón circulante: ' . ratio($ratios['current_ratio']) . ' · Pasivo/activo: ' . pct(($ratios['debt_ratio'] ?? null) !== null ? $ratios['debt_ratio'] * 100 : null)
            . ' · ROE: ' . pct($ratios['roe']) . ' · ROA: ' . pct($ratios['roa']));

        self::section($pdf, 'Análisis financiero', (string) $report['analysis']);
        self::section($pdf, 'Hallazgos', (string) $report['findings']);
        self::section($pdf, 'Recomendaciones', (string) $report['recommendations']);
        self::section($pdf, 'Supuestos y limitaciones', (string) $report['assumptions']);
        self::section($pdf, 'Referencias', (string) $report['references_text']);

        if ($files !== []) {
            $pdf->heading('Anexos');
            foreach ($files as $file) {
                $pdf->paragraph('· ' . ($file['original_name'] ?? 'Archivo') . ' (' . strtoupper((string) pathinfo((string) ($file['original_name'] ?? ''), PATHINFO_EXTENSION)) . ')', 9.5, 13);
            }
        }

        $pdf->heading('Cómo citar este reporte');
        $year = !empty($report['published_at']) ? date('Y', strtotime((string) $report['published_at'])) : date('Y');
        $pdf->paragraph(($report['author_name'] ?? 'Autor') . ' (' . $year . '). ' . $report['title'] . '. AgroNegocios MX. ' . url('/reportes/' . $report['slug']));
        $pdf->paragraph('Licencia: ' . $report['license']);
        $pdf->space(8);
        $pdf->small('Documento educativo. Las cifras provienen de la captura del autor y no han sido auditadas por AgroNegocios MX. No constituye asesoría financiera, fiscal ni legal.');
        $pdf->small('Generado el ' . date('d/m/Y H:i') . ' · ' . config('app.url'));

        return $pdf->output();
    }

    private static function section(SimplePdf $pdf, string $title, string $content): void
    {
        $content = trim($content);
        if ($content === '') {
            return;
        }
        $pdf->heading($title);
        $pdf->paragraph($content);
    }
}
