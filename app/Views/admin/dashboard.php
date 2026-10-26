<?php
/** @var array<string, int> $stats */
/** @var array<int, array<string, mixed>> $queue */
/** @var array<int, array<string, mixed>> $comments */
/** @var array<int, array<string, mixed>> $audit */
/** @var array<string, int> $counts */
?>
<div class="admin-page-head">
    <div>
        <span class="eyebrow">Panel editorial</span>
        <h1>Resumen</h1>
        <p>Contenido pendiente, cuentas por verificar y actividad reciente de la comunidad.</p>
    </div>
    <div class="admin-page-actions">
        <a class="btn btn-primary btn-sm" href="<?= e(url('/admin/contenidos')) ?>">Revisar contenido</a>
        <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/usuarios')) ?>">Verificar cuentas</a>
    </div>
</div>

<div class="admin-stat-grid">
    <div class="admin-stat">
        <span class="admin-stat-icon"><?= icon('users', 'icon') ?></span>
        <div class="admin-stat-info"><span>Cuentas pendientes</span><strong><?= (int) $stats['pending_users'] ?></strong></div>
        <a href="<?= e(url('/admin/usuarios?status=pendiente')) ?>">Ver</a>
    </div>
    <div class="admin-stat">
        <span class="admin-stat-icon"><?= icon('file-text', 'icon') ?></span>
        <div class="admin-stat-info"><span>Contenido en revisión</span><strong><?= (int) $stats['pending_cases'] + (int) $stats['pending_reports'] + (int) $stats['pending_opportunities'] ?></strong></div>
        <a href="<?= e(url('/admin/contenidos')) ?>">Revisar</a>
    </div>
    <div class="admin-stat">
        <span class="admin-stat-icon"><?= icon('message-circle', 'icon') ?></span>
        <div class="admin-stat-info"><span>Comentarios visibles</span><strong><?= (int) $stats['comments'] ?></strong></div>
        <a href="<?= e(url('/admin/comentarios')) ?>">Moderar</a>
    </div>
    <div class="admin-stat">
        <span class="admin-stat-icon"><?= icon('sparkles', 'icon') ?></span>
        <div class="admin-stat-info"><span>Intereses recibidos</span><strong><?= (int) $stats['interests'] ?></strong></div>
        <span class="admin-stat-note">En oportunidades</span>
    </div>
</div>

<div class="admin-grid-2">
    <section class="admin-card">
        <div class="admin-card-head">
            <h2 class="admin-card-title"><?= icon('clock', 'icon icon-sm') ?> Cola de revisión</h2>
            <a class="text-link" href="<?= e(url('/admin/contenidos')) ?>">Ver todo</a>
        </div>
        <?php if ($queue === []): ?>
            <div class="admin-empty"><?= icon('check-circle', 'icon') ?><p>No hay contenido pendiente. Buen trabajo.</p></div>
        <?php else: ?>
            <div class="admin-list">
                <?php foreach ($queue as $item): ?>
                    <a class="admin-list-row" href="<?= e(url('/admin/revision/' . $item['type'] . '/' . $item['id'])) ?>">
                        <span class="admin-list-emoji"><?= icon($item['type'] === 'caso' ? 'folder' : ($item['type'] === 'reporte' ? 'trending-up' : 'sparkles'), 'icon') ?></span>
                        <span class="admin-list-main">
                            <strong><?= e($item['title']) ?></strong>
                            <small><?= e(content_type_short($item['type'])) ?> · <?= e($item['author_name']) ?> · <?= e($item['sector_name'] ?? '') ?></small>
                        </span>
                        <span class="admin-list-time"><?= e(time_ago($item['updated_at'])) ?></span>
                        <?= icon('chevron-right', 'icon icon-xs') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="admin-card">
        <div class="admin-card-head">
            <h2 class="admin-card-title"><?= icon('message-circle', 'icon icon-sm') ?> Comentarios recientes</h2>
            <a class="text-link" href="<?= e(url('/admin/comentarios')) ?>">Moderar</a>
        </div>
        <?php if ($comments === []): ?>
            <div class="admin-empty"><p>Sin comentarios todavía.</p></div>
        <?php else: ?>
            <div class="admin-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="admin-list-row">
                        <?= avatar_tag((string) $comment['author_name'], null, 'avatar-xs') ?>
                        <span class="admin-list-main">
                            <strong><?= e(excerpt($comment['body'], 90)) ?></strong>
                            <small><?= e($comment['author_name']) ?> en <?= e($comment['target_title'] ?? 'contenido') ?> · <?= e(time_ago($comment['created_at'])) ?></small>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<div class="admin-grid-2">
    <section class="admin-card">
        <div class="admin-card-head"><h2 class="admin-card-title"><?= icon('activity', 'icon icon-sm') ?> Publicado</h2></div>
        <div class="admin-content-counts">
            <div><strong><?= (int) $stats['published_cases'] ?></strong><span>casos</span></div>
            <div><strong><?= (int) $stats['published_reports'] ?></strong><span>reportes</span></div>
            <div><strong><?= (int) $stats['published_opportunities'] ?></strong><span>oportunidades</span></div>
            <div><strong><?= (int) $stats['verified_users'] ?></strong><span>cuentas verificadas</span></div>
        </div>
        <div class="admin-quick-links">
            <a href="<?= e(url('/admin/contenidos?tipo=caso&estado=publicado')) ?>">Casos publicados</a>
            <a href="<?= e(url('/admin/contenidos?tipo=reporte&estado=publicado')) ?>">Reportes publicados</a>
            <a href="<?= e(url('/admin/contenidos?tipo=oportunidad&estado=publicado')) ?>">Oportunidades publicadas</a>
        </div>
    </section>

    <section class="admin-card">
        <div class="admin-card-head">
            <h2 class="admin-card-title"><?= icon('shield', 'icon icon-sm') ?> Auditoría reciente</h2>
            <?php if (can('administrar')): ?><a class="text-link" href="<?= e(url('/admin/auditoria')) ?>">Ver todo</a><?php endif; ?>
        </div>
        <?php if ($audit === []): ?>
            <div class="admin-empty"><p>Sin actividad registrada.</p></div>
        <?php else: ?>
            <div class="admin-list">
                <?php foreach ($audit as $entry): ?>
                    <div class="admin-list-row">
                        <span class="admin-list-emoji"><?= icon('activity', 'icon icon-sm') ?></span>
                        <span class="admin-list-main"><strong><?= e($entry['action']) ?></strong><small><?= e($entry['actor_name'] ?? 'Sistema') ?> · <?= e(time_ago($entry['created_at'])) ?></small></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
