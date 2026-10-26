<?php

declare(strict_types=1);

namespace App\Support;

final class Financials
{
    /**
     * Calcula indicadores a partir de las cifras capturadas. No inventa datos:
     * si falta una cifra, el indicador queda en null.
     *
     * @param array<string, mixed> $report
     * @return array<string, float|null>
     */
    public static function ratios(array $report): array
    {
        $revenue = self::number($report['revenue'] ?? null);
        $cogs = self::number($report['cogs'] ?? null);
        $opex = self::number($report['opex'] ?? null);
        $other = self::number($report['other_income'] ?? 0);
        $net = self::number($report['net_income'] ?? null);
        $assets = self::number($report['total_assets'] ?? null);
        $currentAssets = self::number($report['current_assets'] ?? null);
        $currentLiabilities = self::number($report['current_liabilities'] ?? null);
        $liabilities = self::number($report['total_liabilities'] ?? null);
        $equity = self::number($report['equity'] ?? null);

        $gross = ($revenue !== null && $cogs !== null) ? $revenue - $cogs : null;
        $operating = ($gross !== null && $opex !== null) ? $gross - $opex + ($other ?? 0) : null;

        return [
            'gross_profit' => $gross,
            'operating_profit' => $operating,
            'gross_margin' => self::percent($gross, $revenue),
            'operating_margin' => self::percent($operating, $revenue),
            'net_margin' => self::percent($net, $revenue),
            'current_ratio' => self::divide($currentAssets, $currentLiabilities),
            'debt_ratio' => self::percent($liabilities, $assets),
            'roe' => self::percent($net, $equity),
            'roa' => self::percent($net, $assets),
            'expense_ratio' => self::percent($opex, $revenue),
        ];
    }

    /**
     * @param array<string, mixed> $report
     * @return array<string, array{tone: string, label: string, value: string}>
     */
    public static function health(array $report): array
    {
        $r = self::ratios($report);
        $gross = $r['gross_margin'];
        $net = $r['net_margin'];
        $current = $r['current_ratio'];
        $debt = $r['debt_ratio'];
        $roe = $r['roe'];

        return [
            'margen_bruto' => [
                'tone' => ratio_tone($gross, 30, 15),
                'label' => 'Margen bruto',
                'value' => $gross === null ? '—' : pct($gross),
            ],
            'margen_neto' => [
                'tone' => ratio_tone($net, 10, 3),
                'label' => 'Margen neto',
                'value' => $net === null ? '—' : pct($net),
            ],
            'liquidez' => [
                'tone' => ratio_tone($current, 1.5, 1.0),
                'label' => 'Razón circulante',
                'value' => $current === null ? '—' : ratio($current),
            ],
            'endeudamiento' => [
                'tone' => ratio_tone($debt, 40, 60, false),
                'label' => 'Pasivo / activo',
                'value' => $debt === null ? '—' : pct($debt),
            ],
            'roe' => [
                'tone' => ratio_tone($roe, 15, 5),
                'label' => 'ROE',
                'value' => $roe === null ? '—' : pct($roe),
            ],
        ];
    }

    public static function number(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value)) {
            $value = str_replace([',', ' ', '$'], '', $value);
        }
        return is_numeric($value) ? (float) $value : null;
    }

    private static function percent(?float $numerator, ?float $denominator): ?float
    {
        $ratio = self::divide($numerator, $denominator);
        return $ratio === null ? null : $ratio * 100;
    }

    private static function divide(?float $numerator, ?float $denominator): ?float
    {
        if ($numerator === null || $denominator === null || abs($denominator) < 0.0000001) {
            return null;
        }
        return $numerator / $denominator;
    }
}
