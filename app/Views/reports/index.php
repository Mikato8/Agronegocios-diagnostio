<?php
/** @var array<int, array<string, mixed>> $reports */
/** @var int $total */
/** @var array<string, mixed> $filters */
/** @var \App\Core\Paginator $paginator */
/** @var array<int, array<string, mixed>> $sectorsList */
/** @var array<int, array<string, mixed>> $chainsList */
/** @var array<int, array<string, mixed>> $states */
/** @var array<int, int> $years */
$hasFilters = $filters['q'] !== '' || $filters['sector'] !== '' || $filters['chain'] !== '' || $filters['state'] !== '' || $filters['company_type'] !== '' || $filters['year'] > 0;
?>
<section class="page-hero page-hero-compact">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Reportes financieros</span>
        </nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow">Finanzas del campo</span>
                <h1>Reportes financieros</h1>
                <p>Estados de resultados, balances y análisis de empresas agropecuarias y agroindustriales. Indicadores calculados y explicados por sus autores.</p>
            </div>
            <div class="page-hero-aside">
                <span class="big-number"><?= number_format($total) ?></span>
                <span><?= plural($total, 'reporte publicado', 'reportes publicados') ?></span>
                <a class="btn btn-primary btn-sm" href="<?= e(url('/reportes/nuevo')) ?>"><?= icon('plus', 'icon icon-xs') ?> Subir reporte</a>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <form class="filter-bar" method="get" action="<?= e(url('/reportes')) ?>">
            <div class="filter-search">
                <?= icon('search', 'icon icon-sm') ?>
                <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Empresa, sector, indicador…" aria-label="Buscar reportes">
            </div>
            <label class="filter-select">
                <span>Sector</span>
                <select name="sector">
                    <option value="">Todos</option>
                    <?php foreach ($sectorsList as $sector): ?>
                        <option value="<?= e($sector['code']) ?>" <?= $filters['sector'] === $sector['code'] ? 'selected' : '' ?>><?= e($sector['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="filter-select">
                <span>Estado</span>
                <select name="state">
                    <option value="">Todos</option>
                    <?php foreach ($states as $state): ?>
                        <option value="<?= e($state['code']) ?>" <?= $filters['state'] === $state['code'] ? 'selected' : '' ?>><?= e($state['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="filter-select">
                <span>Año</span>
                <select name="year">
                    <option value="">Todos</option>
                    <?php foreach ($years as $year): ?>
                        <option value="<?= $year ?>" <?= $filters['year'] === $year ? 'selected' : '' ?>><?= $year ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="filter-select">
                <span>Orden</span>
                <select name="sort">
                    <option value="recientes" <?= $filters['sort'] === 'recientes' ? 'selected' : '' ?>>Más recientes</option>
                    <option value="vistos" <?= $filters['sort'] === 'vistos' ? 'selected' : '' ?>>Más vistos</option>
                    <option value="relevancia" <?= $filters['sort'] === 'relevancia' ? 'selected' : '' ?>>Relevancia</option>
                </select>
            </label>
            <button class="btn btn-dark btn-sm" type="submit">Aplicar</button>
            <?php if ($hasFilters): ?><a class="filter-clear" href="<?= e(url('/reportes')) ?>">Limpiar</a><?php endif; ?>
        </form>

        <?php if ($reports === []): ?>
            <div class="empty-state">
                <span class="empty-icon"><?= icon('trending-up', 'icon') ?></span>
                <h2>No hay reportes con esos filtros</h2>
                <p>Prueba otro sector o sé la primera persona en compartir un reporte financiero.</p>
                <div class="empty-actions">
                    <a class="btn btn-primary" href="<?= e(url('/reportes')) ?>">Ver todos</a>
                    <a class="btn btn-outline" href="<?= e(url('/reportes/nuevo')) ?>">Subir reporte</a>
                </div>
            </div>
        <?php else: ?>
            <div class="results-meta">
                <p>Mostrando <strong><?= $paginator->from() ?>–<?= $paginator->to() ?></strong> de <strong><?= number_format($total) ?></strong> reportes</p>
            </div>
            <div class="report-grid">
                <?php foreach ($reports as $report): ?>
                    <?php \App\Core\View::partial('partials/report-card', ['report' => $report]); ?>
                <?php endforeach; ?>
            </div>
            <?php \App\Core\View::partial('partials/pagination', ['paginator' => $paginator]); ?>
        <?php endif; ?>
    </div>
</section>
