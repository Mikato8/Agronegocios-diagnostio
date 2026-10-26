<?php
/** @var array<int, array<string, mixed>> $opportunities */
/** @var int $total */
/** @var array<string, mixed> $filters */
/** @var \App\Core\Paginator $paginator */
/** @var array<int, array<string, mixed>> $sectorsList */
/** @var array<int, array<string, mixed>> $chainsList */
/** @var array<int, array<string, mixed>> $states */
$hasFilters = $filters['q'] !== '' || $filters['sector'] !== '' || $filters['chain'] !== '' || $filters['state'] !== '' || $filters['type'] !== '' || $filters['stage'] !== '' || ($filters['inversion'] ?? '') !== '';
?>
<section class="page-hero page-hero-compact">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Oportunidades</span>
        </nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow">Bolsa de agronegocios</span>
                <h1>Oportunidades para conectar</h1>
                <p>Proyectos que buscan socios, proveedores, compradores o talento. Lee el mercado, la inversión y los riesgos antes de expresar tu interés.</p>
            </div>
            <div class="page-hero-aside">
                <span class="big-number"><?= number_format($total) ?></span>
                <span><?= plural($total, 'oportunidad activa', 'oportunidades activas') ?></span>
                <a class="btn btn-primary btn-sm" href="<?= e(url('/oportunidades/nueva')) ?>"><?= icon('plus', 'icon icon-xs') ?> Publicar oportunidad</a>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <form class="filter-bar" method="get" action="<?= e(url('/oportunidades')) ?>">
            <div class="filter-search">
                <?= icon('search', 'icon icon-sm') ?>
                <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Producto, mercado, inversión…" aria-label="Buscar oportunidades">
            </div>
            <label class="filter-select">
                <span>Tipo</span>
                <select name="type">
                    <option value="">Todos</option>
                    <?php foreach (['produccion' => 'Producción', 'comercializacion' => 'Comercialización', 'agroindustria' => 'Agroindustria', 'servicios' => 'Servicios', 'exportacion' => 'Exportación', 'turismo_rural' => 'Turismo rural', 'financiamiento' => 'Financiamiento', 'tecnologia' => 'Tecnología'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $filters['type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
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
                <span>Inversión</span>
                <select name="inversion">
                    <option value="">Cualquiera</option>
                    <option value="0-250000" <?= ($filters['inversion'] ?? '') === '0-250000' ? 'selected' : '' ?>>Hasta $250 mil</option>
                    <option value="250000-1000000" <?= ($filters['inversion'] ?? '') === '250000-1000000' ? 'selected' : '' ?>>$250 mil – $1 M</option>
                    <option value="1000000-5000000" <?= ($filters['inversion'] ?? '') === '1000000-5000000' ? 'selected' : '' ?>>$1 M – $5 M</option>
                    <option value="5000000-" <?= ($filters['inversion'] ?? '') === '5000000-' ? 'selected' : '' ?>>Más de $5 M</option>
                </select>
            </label>
            <label class="filter-select">
                <span>Orden</span>
                <select name="sort">
                    <option value="recientes" <?= $filters['sort'] === 'recientes' ? 'selected' : '' ?>>Más recientes</option>
                    <option value="inversion" <?= $filters['sort'] === 'inversion' ? 'selected' : '' ?>>Menor inversión</option>
                    <option value="cierre" <?= $filters['sort'] === 'cierre' ? 'selected' : '' ?>>Próximas a cerrar</option>
                </select>
            </label>
            <button class="btn btn-dark btn-sm" type="submit">Aplicar</button>
            <?php if ($hasFilters): ?><a class="filter-clear" href="<?= e(url('/oportunidades')) ?>">Limpiar</a><?php endif; ?>
        </form>

        <div class="sector-chips">
            <a class="tag <?= $filters['type'] === '' ? 'is-active' : '' ?>" href="<?= e(url('/oportunidades')) ?>">Todas</a>
            <?php foreach (array_slice(array_keys(['produccion' => 'Producción', 'comercializacion' => 'Comercialización', 'agroindustria' => 'Agroindustria', 'exportacion' => 'Exportación', 'tecnologia' => 'Tecnología']), 0) as $value): ?>
                <a class="tag <?= $filters['type'] === $value ? 'is-active' : '' ?>" href="<?= e(url('/oportunidades?type=' . $value)) ?>"><?= e(opportunity_type_label($value)) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if ($opportunities === []): ?>
            <div class="empty-state">
                <span class="empty-icon"><?= icon('sparkles', 'icon') ?></span>
                <h2>No hay oportunidades con esos filtros</h2>
                <p>Prueba otro sector o publica la primera oportunidad.</p>
                <div class="empty-actions">
                    <a class="btn btn-primary" href="<?= e(url('/oportunidades')) ?>">Ver todas</a>
                    <a class="btn btn-outline" href="<?= e(url('/oportunidades/nueva')) ?>">Publicar</a>
                </div>
            </div>
        <?php else: ?>
            <div class="results-meta">
                <p>Mostrando <strong><?= $paginator->from() ?>–<?= $paginator->to() ?></strong> de <strong><?= number_format($total) ?></strong> oportunidades</p>
            </div>
            <div class="opp-grid">
                <?php foreach ($opportunities as $opportunity): ?>
                    <?php \App\Core\View::partial('partials/opp-card', ['opportunity' => $opportunity]); ?>
                <?php endforeach; ?>
            </div>
            <?php \App\Core\View::partial('partials/pagination', ['paginator' => $paginator]); ?>
        <?php endif; ?>
    </div>
</section>
