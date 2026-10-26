<?php

declare(strict_types=1);

namespace App\Support;

final class CasePdf
{
    /** @param array<string, mixed> $case */
    public static function render(array $case): string
    {
        $pdf = new SimplePdf((string) $case['title']);
        $pdf->title((string) $case['title']);
        $pdf->label('Resumen ejecutivo');
        $pdf->paragraph((string) $case['summary'], 12, 17);
        $pdf->small('Folio ' . $case['folio'] . ' · ' . format_date($case['published_at'] ?? null, 'd/m/Y') . ' · ' . ($case['author_name'] ?? ''));
        if (!empty($case['university_name'])) {
            $pdf->small('Universidad: ' . $case['university_name']);
        }
        $pdf->space(6);

        $pdf->heading('Ficha del caso');
        $ficha = 'Sector: ' . ($case['sector_name'] ?? '—') . ' · Cadena: ' . ($case['chain_name'] ?? 'No especificada');
        $ficha .= ' · Estado: ' . ($case['state_name'] ?? 'No especificado');
        if (!empty($case['municipality'])) {
            $ficha .= ' · Municipio: ' . $case['municipality'];
        }
        $pdf->paragraph($ficha);
        $pdf->paragraph('Tipo de empresa: ' . company_type_label($case['company_type'] ?? null)
            . ' · Etapa: ' . business_stage_label($case['stage'] ?? null)
            . ' · Año: ' . ($case['year'] ?? '—'));

        if (!empty($case['images'])) {
            $pdf->heading('Imágenes del caso');
            foreach ($case['images'] as $image) {
                $path = BASE_PATH . '/public/uploads/casos/' . ltrim((string) $image['path'], '/');
                $pdf->image($path, (string) ($image['caption'] ?? ''));
            }
        }

        self::section($pdf, 'Contexto de la empresa o proyecto', (string) $case['context']);
        self::section($pdf, 'El reto', (string) $case['challenge']);
        self::section($pdf, 'Diagnóstico y análisis', (string) $case['diagnosis']);
        self::section($pdf, 'Estrategia implementada', (string) $case['strategy']);
        self::section($pdf, 'Resultados e indicadores', (string) $case['results']);
        if (!empty($case['financial_highlights'])) {
            self::section($pdf, 'Cifras financieras relevantes', (string) $case['financial_highlights']);
        }
        if (!empty($case['kpis'])) {
            self::section($pdf, 'Indicadores clave (KPI)', (string) $case['kpis']);
        }
        self::section($pdf, 'Aprendizajes', (string) $case['lessons']);
        self::section($pdf, 'Referencias', (string) $case['references_text']);

        $pdf->heading('Cómo citar este caso');
        $year = !empty($case['published_at']) ? date('Y', strtotime((string) $case['published_at'])) : date('Y');
        $pdf->paragraph(($case['author_name'] ?? 'Autor') . ' (' . $year . '). ' . $case['title'] . '. AgroNegocios MX. ' . url('/casos/' . $case['slug']));
        $pdf->paragraph('Licencia: ' . $case['license']);
        $pdf->space(8);
        $pdf->small('Documento generado desde AgroNegocios MX. El contenido es educativo y no sustituye la asesoría profesional, financiera ni legal. Los datos fueron anonimizados por el autor cuando correspondía.');
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

