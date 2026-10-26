<?php
/** @var string $type */
/** @var string $status */
/** @var string $q */
/** @var array<int, array<string, mixed>> $items */
/** @var int $total */
/** @var \App\Core\Paginator $paginator */
?>
<div class="admin-page-head">
    <div>
        <span class="eyebrow">Editorial</span>
        <h1>Contenido</h1>
        <p>Revisa, publica o solicita cambios. Filtra por tipo y estado.</p>
    </div>
    <div class="admin-page-actions"><span class="badge badge-soft"><?= number_format($total) ?> resultados</span></div>
</div>

<nav class="admin-tabs" aria-label="Tipo de contenido">
    <?php foreach (['caso' => 'Casos', 'reporte' => 'Reportes', 'oportunidad' => 'Oportunidades'] as $value => $label): ?>
        <a class="<?= $type === $value ? 'is-active' : '' ?>" href="<?= e(url('/admin/contenidos?tipo=' . $value . '&estado=' . $status)) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</nav>

<form class="admin-filter" method="get" action="<?= e(url('/admin/contenidos')) ?>">
    <input type="hidden" name="tipo" value="<?= e($type) ?>">
    <label>
        <span>Estado</span>
        <select name="estado">
            <?php foreach (['en_revision' => 'En revisión', 'publicado' => 'Publicado', 'borrador' => 'Borrador', 'cambios_solicitados' => 'Cambios solicitados', 'rechazado' => 'Rechazado', 'archivado' => 'Archivado'] as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="filter-search">
        <?= icon('search', 'icon icon-sm') ?>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar por título…">
    </label>
    <button class="btn btn-dark btn-sm" type="submit">Filtrar</button>
</form>

<section class="admin-card">
    <?php if ($items === []): ?>
        <div class="admin-empty"><span class="empty-icon"><?= icon('folder', 'icon') ?></span><p>No hay contenido con esos filtros.</p></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>Título</th><th>Autor</th><th>Sector</th><th>Estado</th><th>Actualizado</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="table-title"><a href="<?= e(url('/admin/revision/' . $type . '/' . $item['id'])) ?>"><?= e($item['title']) ?></a></td>
                            <td class="table-user"><?= e($item['author_name'] ?? '') ?></td>
                            <td><?= e($item['sector_name'] ?? '—') ?></td>
                            <td><span class="badge badge-<?= e(status_tone($item['status'])) ?>"><?= e(status_label($item['status'])) ?></span></td>
                            <td class="table-muted"><?= e(time_ago($item['updated_at'])) ?></td>
                            <td class="table-actions"><a class="btn btn-outline btn-sm" href="<?= e(url('/admin/revision/' . $type . '/' . $item['id'])) ?>">Revisar</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php \App\Core\View::partial('partials/pagination', ['paginator' => $paginator]); ?>
    <?php endif; ?>
</section>
