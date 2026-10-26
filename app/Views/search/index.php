<?php
/** @var string $q */
/** @var array<int, array{type: string, item: array<string, mixed>}> $results */
/** @var int $total */
/** @var array<int, array<string, mixed>> $sectors */
?>
<section class="page-hero page-hero-compact">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Buscar</span>
        </nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow">Buscador</span>
                <h1><?= $q !== '' ? 'Resultados para “' . e($q) . '”' : '¿Qué quieres aprender hoy?' ?></h1>
                <p>Busca en casos, reportes financieros y oportunidades. También puedes filtrar por sector desde cada sección.</p>
            </div>
            <div class="page-hero-aside">
                <span class="big-number"><?= number_format($total) ?></span>
                <span><?= plural($total, 'coincidencia', 'coincidencias') ?></span>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <form class="search-band" method="get" action="<?= e(url('/buscar')) ?>">
            <div class="filter-search">
                <?= icon('search', 'icon') ?>
                <input type="search" name="q" value="<?= e($q) ?>" placeholder="Ej. aguacate, lácteos, exportación, flujo de efectivo" aria-label="Buscar" autofocus>
            </div>
            <button class="btn btn-primary" type="submit">Buscar</button>
        </form>

        <?php if (mb_strlen($q) < 2): ?>
            <div class="search-suggestions">
                <h2>Prueba con</h2>
                <div class="tag-row">
                    <?php foreach (['aguacate', 'café', 'lácteos', 'exportación', 'financiamiento', 'agroindustria', 'riego', 'maíz'] as $term): ?>
                        <a class="tag" href="<?= e(url('/buscar?q=' . rawurlencode($term))) ?>"><?= e($term) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php elseif ($results === []): ?>
            <div class="empty-state">
                <span class="empty-icon"><?= icon('search', 'icon') ?></span>
                <h2>No encontramos coincidencias</h2>
                <p>Prueba con un término más general, revisa la ortografía o explora las secciones.</p>
                <div class="empty-actions">
                    <a class="btn btn-primary" href="<?= e(url('/casos')) ?>">Ver casos</a>
                    <a class="btn btn-outline" href="<?= e(url('/reportes')) ?>">Ver reportes</a>
                </div>
            </div>
        <?php else: ?>
            <div class="search-results">
                <?php foreach ($results as $result): ?>
                    <?php $item = $result['item']; $type = $result['type']; ?>
                    <a class="result-card" href="<?= e(content_route($type, (string) $item['slug'])) ?>">
                        <span class="result-icon"><?= icon($type === 'caso' ? 'folder' : ($type === 'reporte' ? 'trending-up' : 'sparkles'), 'icon') ?></span>
                        <div class="result-body">
                            <span class="badge badge-soft"><?= e(content_type_label($type)) ?></span>
                            <h2><?= e($item['title']) ?></h2>
                            <p><?= e(excerpt((string) ($item['summary'] ?? ''), 160)) ?></p>
                            <span class="result-meta">
                                <?= e($item['sector_name'] ?? '') ?>
                                <?php if (!empty($item['state_name'])): ?> · <?= e($item['state_name']) ?><?php endif; ?>
                                · <?= e($item['author_name'] ?? '') ?>
                                <?php if (!empty($item['university_acronym'])): ?> · <?= e($item['university_acronym']) ?><?php endif; ?>
                            </span>
                        </div>
                        <?= icon('arrow-up-right', 'icon') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
