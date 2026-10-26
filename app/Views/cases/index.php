<?php
/** @var array<int, array<string, mixed>> $cases */
/** @var int $total */
/** @var array<string, mixed> $filters */
/** @var \App\Core\Paginator $paginator */
/** @var array<int, array<string, mixed>> $sectorsList */
/** @var array<int, array<string, mixed>> $chainsList */
/** @var array<int, array<string, mixed>> $states */
/** @var array<int, int> $years */
$hasFilters = $filters['q'] !== '' || $filters['sector'] !== '' || $filters['chain'] !== '' || $filters['state'] !== '' || $filters['stage'] !== '' || $filters['company_type'] !== '' || $filters['year'] > 0;
?>
<section class="page-hero page-hero-compact">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Casos</span>
        </nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow">Archivo de agronegocios</span>
                <h1>Casos de empresas y proyectos</h1>
                <p>Decisiones reales del campo mexicano contadas por estudiantes: qué pasó, qué se analizó, qué se hizo y qué se aprendió.</p>
            </div>
            <div class="page-hero-aside">
                <span class="big-number"><?= number_format($total) ?></span>
                <span><?= plural($total, 'caso encontrado', 'casos encontrados') ?></span>
                <a class="btn btn-primary btn-sm" href="<?= e(url('/casos/nuevo')) ?>"><?= icon('plus', 'icon icon-xs') ?> Compartir caso</a>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <form class="filter-bar" method="get" action="<?= e(url('/casos')) ?>" data-filter-form>
            <div class="filter-search">
                <?= icon('search', 'icon icon-sm') ?>
                <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Empresa, reto, sector…" aria-label="Buscar casos">
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
                <span>Cadena</span>
                <select name="chain">
                    <option value="">Todas</option>
                    <?php foreach ($chainsList as $chain): ?>
                        <option value="<?= e($chain['code']) ?>" <?= $filters['chain'] === $chain['code'] ? 'selected' : '' ?>><?= e($chain['name']) ?></option>
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
                <span>Etapa</span>
                <select name="stage">
                    <option value="">Todas</option>
                    <?php foreach (['idea' => 'Idea', 'arranque' => 'Arranque', 'operacion' => 'En operación', 'crecimiento' => 'Crecimiento', 'consolidacion' => 'Consolidación', 'crisis' => 'Crisis'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $filters['stage'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
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
                    <option value="relevancia" <?= $filters['sort'] === 'relevancia' ? 'selected' : '' ?>>Relevancia</option>
                    <option value="vistos" <?= $filters['sort'] === 'vistos' ? 'selected' : '' ?>>Más vistos</option>
                    <option value="antiguos" <?= $filters['sort'] === 'antiguos' ? 'selected' : '' ?>>Más antiguos</option>
                </select>
            </label>
            <button class="btn btn-dark btn-sm" type="submit">Aplicar</button>
            <?php if ($hasFilters): ?><a class="filter-clear" href="<?= e(url('/casos')) ?>">Limpiar</a><?php endif; ?>
        </form>

        <div class="sector-chips">
            <a class="tag <?= $filters['sector'] === '' ? 'is-active' : '' ?>" href="<?= e(url('/casos')) ?>">Todos</a>
            <?php foreach (array_slice($sectorsList, 0, 7) as $sector): ?>
                <a class="tag <?= $filters['sector'] === $sector['code'] ? 'is-active' : '' ?>" href="<?= e(url('/casos?sector=' . rawurlencode($sector['code']))) ?>"><?= e($sector['emoji'] . ' ' . $sector['name']) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if ($cases === []): ?>
            <div class="empty-state">
                <span class="empty-icon"><?= icon('search', 'icon') ?></span>
                <h2>No encontramos casos con esos filtros</h2>
                <p>Prueba con otro sector, un término más general o limpia los filtros.</p>
                <div class="empty-actions">
                    <a class="btn btn-primary" href="<?= e(url('/casos')) ?>">Ver todos los casos</a>
                    <a class="btn btn-outline" href="<?= e(url('/casos/nuevo')) ?>">Compartir un caso</a>
                </div>
            </div>
        <?php else: ?>
            <div class="results-meta">
                <p>Mostrando <strong><?= $paginator->from() ?>–<?= $paginator->to() ?></strong> de <strong><?= number_format($total) ?></strong> casos</p>
            </div>
            <div class="case-grid">
                <?php foreach ($cases as $case): ?>
                    <?php \App\Core\View::partial('partials/case-card', ['case' => $case]); ?>
                <?php endforeach; ?>
            </div>
            <?php \App\Core\View::partial('partials/pagination', ['paginator' => $paginator]); ?>
        <?php endif; ?>
    </div>
</section>
