<?php
/** @var array<string, mixed> $report */
use App\Support\Financials;

$href = content_route('reporte', (string) $report['slug']);
$ratios = Financials::ratios($report);
$netMargin = $ratios['net_margin'];
?>
<article class="report-card">
    <div class="report-card-top">
        <span class="report-icon"><?= icon('trending-up', 'icon') ?></span>
        <div class="report-card-head">
            <span class="badge badge-soft"><?= e($report['sector_name'] ?? 'Finanzas') ?></span>
            <?php if (!empty($report['is_demo'])): ?><span class="badge badge-demo">Demostrativo</span><?php endif; ?>
            <span class="report-period"><?= e(format_date($report['period_start'] ?? null, 'M Y')) ?> – <?= e(format_date($report['period_end'] ?? null, 'M Y')) ?></span>
        </div>
    </div>
    <h3><a href="<?= e($href) ?>"><?= e($report['title']) ?></a></h3>
    <p><?= e(excerpt((string) ($report['summary'] ?? ''), 120)) ?></p>
    <div class="report-metrics">
        <div>
            <span>Ingresos</span>
            <strong><?= e(compact_money($report['revenue'] ?? null, (string) ($report['currency'] ?? 'MXN'))) ?></strong>
        </div>
        <div>
            <span>Utilidad neta</span>
            <strong><?= e(compact_money($report['net_income'] ?? null, (string) ($report['currency'] ?? 'MXN'))) ?></strong>
        </div>
        <div>
            <span>Margen neto</span>
            <strong class="<?= e($netMargin !== null && $netMargin >= 0 ? 'is-positive' : 'is-negative') ?>"><?= e($netMargin === null ? '—' : pct($netMargin)) ?></strong>
        </div>
    </div>
    <div class="report-card-footer">
        <span class="case-author">
            <span class="avatar avatar-xs"><?= e(initials((string) ($report['author_name'] ?? 'AN'))) ?></span>
            <span><?= e($report['author_name'] ?? 'Autor') ?></span>
        </span>
        <span class="case-card-data">
            <?= icon('download', 'icon icon-xs') ?> <?= (int) ($report['files_count'] ?? 0) ?> <?= (int) ($report['files_count'] ?? 0) === 1 ? 'anexo' : 'anexos' ?>
            <span class="case-stat"><?= icon('eye', 'icon icon-xs') ?> <?= (int) ($report['views'] ?? 0) ?></span>
        </span>
    </div>
</article>
