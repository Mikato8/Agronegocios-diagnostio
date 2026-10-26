<?php

declare(strict_types=1);

namespace App\Support;

final class OpportunityPdf
{
    /** @param array<string, mixed> $opportunity */
    public static function render(array $opportunity): string
    {
        $pdf = new SimplePdf((string) $opportunity['title']);
        $pdf->title((string) $opportunity['title']);
        $pdf->label('Resumen de la oportunidad');
        $pdf->paragraph((string) $opportunity['summary'], 12, 17);
        $pdf->small('Folio ' . $opportunity['folio'] . ' · Publicado ' . format_date($opportunity['published_at'] ?? null, 'd/m/Y'));
        $pdf->space(6);

        $pdf->heading('Ficha de la oportunidad');
        $pdf->paragraph('Tipo: ' . opportunity_type_label($opportunity['opportunity_type'] ?? null)
            . ' · Etapa: ' . opportunity_stage_label($opportunity['stage'] ?? null));
        $pdf->paragraph('Sector: ' . ($opportunity['sector_name'] ?? '—') . ' · Cadena: ' . ($opportunity['chain_name'] ?? 'No especificada'));
        $pdf->paragraph('Ubicación: ' . ($opportunity['state_name'] ?? 'México') . (($opportunity['municipality'] ?? '') !== '' ? ', ' . $opportunity['municipality'] : ''));
        $pdf->paragraph('Inversión estimada: ' . investment_range($opportunity['investment_min'] ?? null, $opportunity['investment_max'] ?? null, (string) $opportunity['currency']));
        if (($opportunity['expected_return_pct'] ?? null) !== null) {
            $pdf->paragraph('Retorno esperado: ' . pct($opportunity['expected_return_pct']));
        }
        if (($opportunity['payback_months'] ?? null) !== null) {
            $pdf->paragraph('Periodo de recuperación estimado: ' . (int) $opportunity['payback_months'] . ' meses');
        }

        self::section($pdf, 'Descripción', (string) $opportunity['description']);
        self::section($pdf, 'Propuesta de valor', (string) $opportunity['value_proposition']);
        self::section($pdf, 'Requisitos para participar', (string) $opportunity['requirements']);
        self::section($pdf, 'Riesgos y consideraciones', (string) $opportunity['risks']);
        self::section($pdf, 'Tamaño y contexto de mercado', (string) $opportunity['market_size']);

        $pdf->heading('Contacto');
        if (!empty($opportunity['is_confidential'])) {
            $pdf->paragraph('El contacto de esta oportunidad es reservado. Envía una expresión de interés desde el portal para que el autor pueda contactarte.');
        } else {
            $pdf->paragraph('Nombre: ' . ($opportunity['contact_name'] ?? '—'));
            if (!empty($opportunity['contact_email'])) {
                $pdf->paragraph('Correo: ' . $opportunity['contact_email']);
            }
            if (!empty($opportunity['contact_phone'])) {
                $pdf->paragraph('Teléfono: ' . $opportunity['contact_phone']);
            }
        }

        $pdf->heading('Aviso');
        $pdf->paragraph('Esta oportunidad es una publicación de un estudiante o docente y no una oferta de valores ni una recomendación de inversión. Verifica la información, la situación legal de la empresa y los riesgos antes de comprometer recursos.');
        $pdf->paragraph('Publicado en AgroNegocios MX · ' . url('/oportunidades/' . $opportunity['slug']));
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
