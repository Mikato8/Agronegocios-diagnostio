<?php
/** @var array<string, mixed> $user */
/** @var array<string, int> $activity */
/** @var array<int, array<string, mixed>> $myCases */
/** @var array<int, array<string, mixed>> $myReports */
/** @var array<int, array<string, mixed>> $myOpportunities */
/** @var array<int, array<string, mixed>> $bookmarks */
/** @var array<int, array<string, mixed>> $interests */
/** @var array<int, array<string, mixed>> $feedback */
/** @var array<string, int> $totals */
/** @var int $completeness */
$hasContent = $totals['cases'] + $totals['reports'] + $totals['opportunities'] > 0;
?>
<section class="page-hero page-hero-compact dashboard-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Mi panel</span>
        </nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow"><?= icon('dashboard', 'icon icon-xs') ?> Panel del estudiante</span>
                <h1>Hola, <?= e(first_name((string) $user['name'])) ?>.</h1>
                <p>Este es tu espacio para documentar, revisar y compartir. Tu cuenta está <?= $user['status'] === 'verificado' ? 'verificada' : 'pendiente de verificación' ?>.</p>
            </div>
            <div class="dashboard-actions">
                <a class="btn btn-primary btn-sm" href="<?= e(url('/casos/nuevo')) ?>"><?= icon('plus', 'icon icon-xs') ?> Caso</a>
                <a class="btn btn-outline btn-sm" href="<?= e(url('/reportes/nuevo')) ?>"><?= icon('plus', 'icon icon-xs') ?> Reporte</a>
                <a class="btn btn-outline btn-sm" href="<?= e(url('/oportunidades/nueva')) ?>"><?= icon('plus', 'icon icon-xs') ?> Oportunidad</a>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <?php if ($completeness < 100): ?>
            <div class="dashboard-banner">
                <div>
                    <strong>Completa tu perfil (<?= (int) $completeness ?>%)</strong>
                    <p>Una semblanza y tu universidad ayudan a que otros estudiantes confíen en tu trabajo.<?= $user['status'] !== 'verificado' ? ' Sube tu constancia para poder publicar y comentar.' : '' ?></p>
                </div>
                <a class="btn btn-outline btn-sm" href="<?= e(url('/perfil')) ?>">Completar perfil</a>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card"><span class="stat-icon"><?= icon('folder', 'icon') ?></span><strong><?= (int) $totals['cases'] ?></strong><span>casos</span><small><?= (int) $activity['cases_published'] ?> <?= plural((int) $activity['cases_published'], 'publicado', 'publicados') ?></small></div>
            <div class="stat-card"><span class="stat-icon"><?= icon('trending-up', 'icon') ?></span><strong><?= (int) $totals['reports'] ?></strong><span>reportes</span><small><?= (int) $activity['reports_published'] ?> <?= plural((int) $activity['reports_published'], 'publicado', 'publicados') ?></small></div>
            <div class="stat-card"><span class="stat-icon"><?= icon('sparkles', 'icon') ?></span><strong><?= (int) $totals['opportunities'] ?></strong><span>oportunidades</span><small><?= (int) $activity['opportunities_published'] ?> <?= plural((int) $activity['opportunities_published'], 'publicada', 'publicadas') ?></small></div>
            <div class="stat-card"><span class="stat-icon"><?= icon('eye', 'icon') ?></span><strong><?= number_format((int) $activity['views']) ?></strong><span>vistas</span><small>en tu contenido</small></div>
            <div class="stat-card"><span class="stat-icon"><?= icon('message-circle', 'icon') ?></span><strong><?= (int) $activity['comments'] ?></strong><span>comentarios</span><small>aportados</small></div>
            <div class="stat-card"><span class="stat-icon"><?= icon('bookmark', 'icon') ?></span><strong><?= (int) $activity['bookmarks'] ?></strong><span>guardados</span><small>para después</small></div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-main">
                <section class="admin-card">
                    <div class="admin-card-head">
                        <h2 class="admin-card-title"><?= icon('folder', 'icon icon-sm') ?> Mis casos</h2>
                        <a class="text-link" href="<?= e(url('/casos/nuevo')) ?>">Nuevo caso <?= icon('plus', 'icon icon-xs') ?></a>
                    </div>
                    <?php if ($myCases === []): ?>
                        <div class="empty-inline">Aún no has creado casos. <a href="<?= e(url('/casos/nuevo')) ?>">Empieza con uno</a>.</div>
                    <?php else: ?>
                        <div class="content-list">
                            <?php foreach ($myCases as $item): ?>
                                <div class="content-row">
                                    <span class="content-emoji"><?= e($item['sector_emoji'] ?? '🌱') ?></span>
                                    <div class="content-main">
                                        <a href="<?= e(url('/casos/' . $item['slug'])) ?>"><?= e($item['title']) ?></a>
                                        <span><?= e($item['sector_name'] ?? '') ?> · <?= e(format_date($item['updated_at'], 'd/m/Y')) ?> · <?= (int) ($item['views'] ?? 0) ?> vistas</span>
                                    </div>
                                    <span class="badge badge-<?= e(status_tone($item['status'])) ?>"><?= e(status_label($item['status'])) ?></span>
                                    <?php if (in_array($item['status'], ['borrador', 'cambios_solicitados', 'rechazado'], true)): ?>
                                        <a class="btn btn-ghost btn-sm" href="<?= e(url('/casos/' . $item['slug'] . '/editar')) ?>">Editar</a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="admin-card">
                    <div class="admin-card-head">
                        <h2 class="admin-card-title"><?= icon('trending-up', 'icon icon-sm') ?> Mis reportes</h2>
                        <a class="text-link" href="<?= e(url('/reportes/nuevo')) ?>">Nuevo reporte <?= icon('plus', 'icon icon-xs') ?></a>
                    </div>
                    <?php if ($myReports === []): ?>
                        <div class="empty-inline">Aún no has subido reportes. <a href="<?= e(url('/reportes/nuevo')) ?>">Sube el primero</a>.</div>
                    <?php else: ?>
                        <div class="content-list">
                            <?php foreach ($myReports as $item): ?>
                                <div class="content-row">
                                    <span class="content-emoji"><?= icon('trending-up', 'icon') ?></span>
                                    <div class="content-main">
                                        <a href="<?= e(url('/reportes/' . $item['slug'])) ?>"><?= e($item['title']) ?></a>
                                        <span><?= e($item['sector_name'] ?? '') ?> · <?= e(format_date($item['period_end'] ?? null, 'M Y')) ?> · <?= (int) ($item['views'] ?? 0) ?> vistas</span>
                                    </div>
                                    <span class="badge badge-<?= e(status_tone($item['status'])) ?>"><?= e(status_label($item['status'])) ?></span>
                                    <?php if (in_array($item['status'], ['borrador', 'cambios_solicitados', 'rechazado'], true)): ?>
                                        <a class="btn btn-ghost btn-sm" href="<?= e(url('/reportes/' . $item['slug'] . '/editar')) ?>">Editar</a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="admin-card">
                    <div class="admin-card-head">
                        <h2 class="admin-card-title"><?= icon('sparkles', 'icon icon-sm') ?> Mis oportunidades</h2>
                        <a class="text-link" href="<?= e(url('/oportunidades/nueva')) ?>">Nueva oportunidad <?= icon('plus', 'icon icon-xs') ?></a>
                    </div>
                    <?php if ($myOpportunities === []): ?>
                        <div class="empty-inline">Aún no has publicado oportunidades. <a href="<?= e(url('/oportunidades/nueva')) ?>">Publica una</a>.</div>
                    <?php else: ?>
                        <div class="content-list">
                            <?php foreach ($myOpportunities as $item): ?>
                                <div class="content-row">
                                    <span class="content-emoji"><?= icon('sparkles', 'icon') ?></span>
                                    <div class="content-main">
                                        <a href="<?= e(url('/oportunidades/' . $item['slug'])) ?>"><?= e($item['title']) ?></a>
                                        <span><?= e($item['sector_name'] ?? '') ?> · <?= (int) ($item['interests_count'] ?? 0) ?> interesados</span>
                                    </div>
                                    <span class="badge badge-<?= e(status_tone($item['status'])) ?>"><?= e(status_label($item['status'])) ?></span>
                                    <?php if (in_array($item['status'], ['borrador', 'cambios_solicitados', 'rechazado'], true)): ?>
                                        <a class="btn btn-ghost btn-sm" href="<?= e(url('/oportunidades/' . $item['slug'] . '/editar')) ?>">Editar</a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

            <aside class="dashboard-aside">
                <div class="sidebar-card">
                    <h2 class="sidebar-title"><?= icon('users', 'icon icon-sm') ?> Intereses recibidos</h2>
                    <?php if ($interests === []): ?>
                        <p class="muted">Cuando alguien se interese en tus oportunidades, aparecerá aquí.</p>
                    <?php else: ?>
                        <?php foreach ($interests as $interest): ?>
                            <div class="side-activity">
                                <strong><?= e($interest['student_name']) ?></strong>
                                <span><?= e($interest['title']) ?></span>
                                <small><?= e($interest['university_acronym'] ?? '') ?> · <?= e(time_ago($interest['created_at'])) ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="sidebar-card">
                    <h2 class="sidebar-title"><?= icon('message-circle', 'icon icon-sm') ?> Comentarios recientes</h2>
                    <?php if ($feedback === []): ?>
                        <p class="muted">Los comentarios sobre tu contenido aparecerán aquí.</p>
                    <?php else: ?>
                        <?php foreach ($feedback as $comment): ?>
                            <div class="side-activity">
                                <strong><?= e($comment['author_name']) ?></strong>
                                <span><?= e(excerpt($comment['body'], 90)) ?></span>
                                <small><?= e(time_ago($comment['created_at'])) ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="sidebar-card">
                    <h2 class="sidebar-title"><?= icon('bookmark', 'icon icon-sm') ?> Guardados</h2>
                    <?php if ($bookmarks === []): ?>
                        <p class="muted">Guarda casos, reportes y oportunidades para leerlos después.</p>
                    <?php else: ?>
                        <?php foreach ($bookmarks as $bookmark): ?>
                            <a class="side-save" href="<?= e(content_route($bookmark['target_type'], (string) $bookmark['slug'])) ?>">
                                <span class="badge badge-soft"><?= e(content_type_short($bookmark['target_type'])) ?></span>
                                <strong><?= e(excerpt((string) $bookmark['title'], 70)) ?></strong>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="sidebar-card">
                    <h2 class="sidebar-title"><?= icon('book-open', 'icon icon-sm') ?> Siguiente paso</h2>
                    <p>Documenta una práctica, una visita o un proyecto. Un caso bien escrito también es una carta de presentación.</p>
                    <a class="btn btn-primary btn-block btn-sm" href="<?= e(url('/guias')) ?>">Ver guías</a>
                </div>
            </aside>
        </div>
    </div>
</section>
