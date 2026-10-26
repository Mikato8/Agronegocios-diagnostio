<?php
/** @var array<string, mixed> $report */
/** @var array<string, float|null> $ratios */
/** @var array<string, array{tone: string, label: string, value: string}> $health */
/** @var array<int, array<string, mixed>> $files */
/** @var array<int, array<string, mixed>> $comments */
/** @var array<int, array<string, mixed>> $reviews */
/** @var bool $reacted */
/** @var bool $bookmarked */
/** @var int $reactionsCount */
/** @var bool $preview */
/** @var bool $canEdit */
$url = url('/reportes/' . $report['slug']);
$publishedYear = $report['published_at'] ? date('Y', strtotime((string) $report['published_at'])) : date('Y');
$citation = $report['author_name'] . ' (' . $publishedYear . '). ' . $report['title'] . '. AgroNegocios MX. ' . $url;
$currency = (string) ($report['currency'] ?? 'MXN');
$status = (string) $report['status'];
?>
<section class="case-header">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <a href="<?= e(url('/reportes')) ?>">Reportes</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <span><?= e(excerpt((string) $report['title'], 60)) ?></span>
        </nav>

        <?php if ($preview): ?>
            <div class="notice notice-warning">
                <?= icon('eye', 'icon') ?>
                <div><strong>Vista previa.</strong><p>Este reporte no es público. Estado: <?= e(status_label($status)) ?>.</p></div>
                <?php if (can('moderar')): ?><a class="btn btn-sm btn-dark" href="<?= e(url('/admin/revision/reporte/' . $report['id'])) ?>">Revisar</a><?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($report['is_demo']): ?>
            <div class="notice notice-info">
                <?= icon('info', 'icon') ?>
                <div><strong>Contenido demostrativo.</strong><p>Cifras y empresa ficticias, elaboradas para mostrar el flujo del portal.</p></div>
            </div>
        <?php endif; ?>

        <div class="case-title-row">
            <div class="case-title-main">
                <div class="case-kicker">
                    <span class="crops-pill"><?= e($report['sector_emoji'] ?? '📊') ?> Reporte financiero</span>
                    <span class="badge badge-soft"><?= e($report['sector_name'] ?? '') ?></span>
                    <span class="badge badge-neutral"><?= e(format_date($report['period_start'] ?? null, 'M Y')) ?> – <?= e(format_date($report['period_end'] ?? null, 'M Y')) ?></span>
                </div>
                <h1><?= e($report['title']) ?></h1>
                <p class="case-deck"><?= e($report['summary']) ?></p>
                <div class="case-byline">
                    <?= avatar_tag((string) $report['author_name'], $report['author_avatar_url'] ?? null, 'avatar-sm') ?>
                    <div>
                        <strong><?= e($report['author_name']) ?></strong>
                        <span><?= e($report['university_name'] ?? 'Estudiante de agronegocios') ?><?php if ($report['author_status'] === 'verificado'): ?> · <span class="verified"><?= icon('check-circle', 'icon icon-xs') ?> Verificado</span><?php endif; ?></span>
                    </div>
                    <span class="byline-divider"></span>
                    <span class="byline-meta"><?= icon('calendar', 'icon icon-xs') ?> <?= e(format_date($report['published_at'] ?? null, 'd M Y')) ?></span>
                    <span class="byline-meta"><?= icon('eye', 'icon icon-xs') ?> <?= number_format((int) $report['views']) ?> vistas</span>
                    <span class="byline-meta"><?= icon('download', 'icon icon-xs') ?> <?= count($files) ?> anexos</span>
                </div>
            </div>
            <div class="case-title-actions">
                <?php if ($canEdit): ?><a class="btn btn-outline btn-sm" href="<?= e($url . '/editar') ?>"><?= icon('edit', 'icon icon-xs') ?> Editar</a><?php endif; ?>
                <button class="btn btn-ghost btn-sm" type="button" data-copy="<?= e($citation) ?>"><?= icon('copy', 'icon icon-xs') ?> Citar</button>
                <a class="btn btn-ghost btn-sm" href="<?= e($url . '/pdf') ?>"><?= icon('download', 'icon icon-xs') ?> PDF</a>
                <form method="post" action="<?= e($url . '/guardar') ?>">
                    <?= csrf_field() ?>
                    <button class="btn <?= $bookmarked ? 'btn-primary' : 'btn-ghost' ?> btn-sm" type="submit"><?= icon('bookmark', 'icon icon-xs') ?> <?= $bookmarked ? 'Guardado' : 'Guardar' ?></button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container case-layout">
        <article class="case-article">
            <div class="finance-hero">
                <div>
                    <span>Ingresos</span>
                    <strong><?= e(money($report['revenue'] ?? null, $currency)) ?></strong>
                </div>
                <div>
                    <span>Utilidad neta</span>
                    <strong class="<?= ($report['net_income'] ?? 0) >= 0 ? 'is-positive' : 'is-negative' ?>"><?= e(money($report['net_income'] ?? null, $currency)) ?></strong>
                </div>
                <div>
                    <span>Margen neto</span>
                    <strong><?= e($ratios['net_margin'] === null ? '—' : pct($ratios['net_margin'])) ?></strong>
                </div>
                <div>
                    <span>Activo total</span>
                    <strong><?= e(compact_money($report['total_assets'] ?? null, $currency)) ?></strong>
                </div>
            </div>

            <section class="case-section">
                <h2><?= icon('activity', 'icon icon-sm') ?> Semáforo financiero</h2>
                <div class="health-grid">
                    <?php foreach ($health as $key => $item): ?>
                        <div class="health-card health-<?= e($item['tone']) ?>">
                            <span><?= e($item['label']) ?></span>
                            <strong><?= e($item['value']) ?></strong>
                            <small><?= e(tone_label($item['tone'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="case-disclaimer">Los criterios del semáforo son orientativos y académicos: margen bruto ≥ 30% saludable; margen neto ≥ 10%; razón circulante ≥ 1.5; pasivo/activo ≤ 40%; ROE ≥ 15%. No sustituyen un análisis sectorial.</p>
            </section>

            <section class="case-section">
                <h2><span class="section-index">01</span> Estado de resultados</h2>
                <div class="table-wrap">
                    <table class="data-table">
                        <tbody>
                            <tr><th>Ingresos totales</th><td><?= e(money($report['revenue'] ?? null, $currency)) ?></td></tr>
                            <tr><th>Costo de ventas</th><td><?= e(money($report['cogs'] ?? null, $currency)) ?></td></tr>
                            <tr class="is-subtotal"><th>Utilidad bruta</th><td><?= e(money($ratios['gross_profit'], $currency)) ?></td></tr>
                            <tr><th>Gastos de operación</th><td><?= e(money($report['opex'] ?? null, $currency)) ?></td></tr>
                            <tr><th>Otros ingresos</th><td><?= e(money($report['other_income'] ?? null, $currency)) ?></td></tr>
                            <tr class="is-subtotal"><th>Utilidad de operación</th><td><?= e(money($ratios['operating_profit'], $currency)) ?></td></tr>
                            <tr class="is-total"><th>Utilidad neta</th><td><?= e(money($report['net_income'] ?? null, $currency)) ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="case-section">
                <h2><span class="section-index">02</span> Balance general</h2>
                <div class="table-wrap">
                    <table class="data-table">
                        <tbody>
                            <tr><th>Activo total</th><td><?= e(money($report['total_assets'] ?? null, $currency)) ?></td></tr>
                            <tr><th>Activo circulante</th><td><?= e(money($report['current_assets'] ?? null, $currency)) ?></td></tr>
                            <tr><th>Efectivo y equivalentes</th><td><?= e(money($report['cash'] ?? null, $currency)) ?></td></tr>
                            <tr><th>Cuentas por cobrar</th><td><?= e(money($report['receivables'] ?? null, $currency)) ?></td></tr>
                            <tr><th>Inventario</th><td><?= e(money($report['inventory'] ?? null, $currency)) ?></td></tr>
                            <tr><th>Pasivo total</th><td><?= e(money($report['total_liabilities'] ?? null, $currency)) ?></td></tr>
                            <tr><th>Pasivo circulante</th><td><?= e(money($report['current_liabilities'] ?? null, $currency)) ?></td></tr>
                            <tr><th>Deuda financiera</th><td><?= e(money($report['debt'] ?? null, $currency)) ?></td></tr>
                            <tr class="is-total"><th>Capital contable</th><td><?= e(money($report['equity'] ?? null, $currency)) ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="case-section">
                <h2><span class="section-index">03</span> Indicadores calculados</h2>
                <div class="ratio-grid">
                    <div class="ratio-card"><span>Margen bruto</span><strong><?= e($ratios['gross_margin'] === null ? '—' : pct($ratios['gross_margin'])) ?></strong><small>Utilidad bruta / ingresos</small></div>
                    <div class="ratio-card"><span>Margen operativo</span><strong><?= e($ratios['operating_margin'] === null ? '—' : pct($ratios['operating_margin'])) ?></strong><small>Utilidad de operación / ingresos</small></div>
                    <div class="ratio-card"><span>Margen neto</span><strong><?= e($ratios['net_margin'] === null ? '—' : pct($ratios['net_margin'])) ?></strong><small>Utilidad neta / ingresos</small></div>
                    <div class="ratio-card"><span>Razón circulante</span><strong><?= e(ratio($ratios['current_ratio'])) ?></strong><small>Activo circulante / pasivo circulante</small></div>
                    <div class="ratio-card"><span>Pasivo / activo</span><strong><?= e($ratios['debt_ratio'] === null ? '—' : pct($ratios['debt_ratio'])) ?></strong><small>Apalancamiento total</small></div>
                    <div class="ratio-card"><span>ROE</span><strong><?= e($ratios['roe'] === null ? '—' : pct($ratios['roe'])) ?></strong><small>Utilidad neta / capital</small></div>
                    <div class="ratio-card"><span>ROA</span><strong><?= e($ratios['roa'] === null ? '—' : pct($ratios['roa'])) ?></strong><small>Utilidad neta / activo</small></div>
                    <div class="ratio-card"><span>Gastos / ingresos</span><strong><?= e($ratios['expense_ratio'] === null ? '—' : pct($ratios['expense_ratio'])) ?></strong><small>Eficiencia operativa</small></div>
                </div>
            </section>

            <section class="case-section">
                <h2><span class="section-index">04</span> Análisis financiero</h2>
                <div class="prose"><?= nl2br(e($report['analysis'])) ?></div>
            </section>

            <section class="case-section">
                <h2><span class="section-index">05</span> Hallazgos</h2>
                <div class="prose"><?= nl2br(e($report['findings'])) ?></div>
            </section>

            <section class="case-section case-section-highlight">
                <h2><span class="section-index">06</span> Recomendaciones</h2>
                <div class="prose"><?= nl2br(e($report['recommendations'])) ?></div>
            </section>

            <?php if (trim((string) $report['assumptions']) !== '' || trim((string) $report['references_text']) !== ''): ?>
                <section class="case-section">
                    <h2><span class="section-index">07</span> Supuestos, límites y referencias</h2>
                    <?php if (trim((string) $report['assumptions']) !== ''): ?><div class="prose"><?= nl2br(e($report['assumptions'])) ?></div><?php endif; ?>
                    <?php if (trim((string) $report['references_text']) !== ''): ?><div class="prose prose-sm"><?= nl2br(e($report['references_text'])) ?></div><?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if ($files !== []): ?>
                <section class="case-section">
                    <h2><span class="section-index">08</span> Anexos</h2>
                    <div class="attachment-list">
                        <?php foreach ($files as $file): ?>
                            <a class="attachment-row" href="<?= e(url('/uploads/archivos/' . $file['path'])) ?>" target="_blank" rel="noopener">
                                <span class="attachment-icon"><?= icon('file-text', 'icon') ?></span>
                                <div>
                                    <strong><?= e($file['original_name']) ?></strong>
                                    <small><?= e(strtoupper((string) pathinfo($file['original_name'], PATHINFO_EXTENSION))) ?> · <?= e(number_format((int) $file['size_bytes'] / 1024, 0)) ?> KB</small>
                                </div>
                                <?= icon('download', 'icon icon-sm') ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <p class="case-disclaimer">Los anexos son responsabilidad del autor. Verifica su contenido antes de reutilizarlo.</p>
                </section>
            <?php endif; ?>

            <div class="citation-box">
                <div>
                    <span class="summary-label">Cómo citar este reporte</span>
                    <p><?= e($citation) ?></p>
                    <p class="case-muted">Licencia: <?= e($report['license']) ?></p>
                </div>
                <button class="btn btn-outline btn-sm" type="button" data-copy="<?= e($citation) ?>"><?= icon('copy', 'icon icon-xs') ?> Copiar</button>
            </div>

            <section class="comments" id="comentarios">
                <div class="comments-head">
                    <h2>Discusión <span class="count-badge"><?= count($comments) ?></span></h2>
                    <p>Comentarios de estudiantes verificados. Discute supuestos, metodología y decisiones.</p>
                </div>
                <?php if ($comments === []): ?>
                    <div class="comments-empty"><?= icon('message-circle', 'icon') ?><p>Aún no hay comentarios.</p></div>
                <?php else: ?>
                    <ol class="comment-list">
                        <?php foreach ($comments as $comment): ?>
                            <li class="comment <?= $comment['status'] === 'oculto' ? 'is-hidden' : '' ?>">
                                <?= avatar_tag((string) $comment['author_name'], $comment['author_avatar_url'] ?? null, 'avatar-sm') ?>
                                <div class="comment-body">
                                    <div class="comment-head">
                                        <strong><?= e($comment['author_name']) ?></strong>
                                        <?php if (!empty($comment['university_acronym'])): ?><span class="badge badge-soft"><?= e($comment['university_acronym']) ?></span><?php endif; ?>
                                        <time datetime="<?= e($comment['created_at']) ?>"><?= e(time_ago($comment['created_at'])) ?></time>
                                    </div>
                                    <p><?= nl2br(e($comment['body'])) ?></p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
                <?php if ($currentUser === null): ?>
                    <div class="comment-cta"><p>Inicia sesión para comentar.</p><a class="btn btn-primary btn-sm" href="<?= e(url('/acceder')) ?>">Acceder</a></div>
                <?php elseif ($currentUser['status'] === 'verificado'): ?>
                    <form class="comment-form" method="post" action="<?= e($url . '/comentarios') ?>">
                        <?= csrf_field() ?>
                        <label for="comment-body">Añadir un comentario</label>
                        <textarea id="comment-body" name="body" rows="4" maxlength="2000" required placeholder="Pregunta o aporta sobre las cifras, los supuestos o las recomendaciones…"></textarea>
                        <div class="comment-form-foot"><span>Máximo 2,000 caracteres.</span><button class="btn btn-primary btn-sm" type="submit">Publicar</button></div>
                    </form>
                <?php else: ?>
                    <div class="comment-cta"><p>Tu cuenta está en revisión. Podrás comentar cuando esté verificada.</p><a class="btn btn-outline btn-sm" href="<?= e(url('/perfil')) ?>">Ver mi perfil</a></div>
                <?php endif; ?>
            </section>
        </article>

        <aside class="case-sidebar">
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('file-text', 'icon icon-sm') ?> Ficha del reporte</h2>
                <dl class="plot-data">
                    <div><dt>Periodo</dt><dd><?= e(format_date($report['period_start'] ?? null, 'd/m/Y')) ?> – <?= e(format_date($report['period_end'] ?? null, 'd/m/Y')) ?></dd></div>
                    <div><dt>Moneda</dt><dd><?= e($currency) ?></dd></div>
                    <div><dt>Sector</dt><dd><?= e($report['sector_name'] ?? '—') ?></dd></div>
                    <div><dt>Cadena</dt><dd><?= e($report['chain_name'] ?? 'No especificada') ?></dd></div>
                    <div><dt>Estado</dt><dd><?= e($report['state_name'] ?? 'México') ?></dd></div>
                    <div><dt>Organización</dt><dd><?= e(company_type_label($report['company_type'] ?? null)) ?></dd></div>
                    <div><dt>Folio</dt><dd><?= e($report['folio']) ?></dd></div>
                </dl>
            </div>
            <div class="sidebar-card author-card">
                <?= avatar_tag((string) $report['author_name'], $report['author_avatar_url'] ?? null, 'avatar-lg') ?>
                <h2><a href="<?= e(url('/estudiante/' . $report['author_id'])) ?>"><?= e($report['author_name']) ?></a></h2>
                <p class="author-inst"><?= e($report['university_name'] ?? 'Estudiante de agronegocios') ?></p>
                <?php if ($report['author_status'] === 'verificado'): ?><span class="badge badge-success"><?= icon('check-circle', 'icon icon-xs') ?> Cuenta verificada</span><?php endif; ?>
                <?php if (!empty($report['author_bio'])): ?><p class="author-bio"><?= e(excerpt($report['author_bio'], 140)) ?></p><?php endif; ?>
            </div>
            <div class="sidebar-card utility-card">
                <h2 class="sidebar-title"><?= icon('check-circle', 'icon icon-sm') ?> ¿Te resultó útil?</h2>
                <form method="post" action="<?= e($url . '/util') ?>" data-reaction-form>
                    <?= csrf_field() ?>
                    <button class="btn <?= $reacted ? 'btn-primary' : 'btn-outline' ?> btn-block" type="submit" data-reaction-button>
                        <?= icon('check-circle', 'icon icon-sm') ?> <span data-reaction-label><?= $reacted ? 'Marcado como útil' : 'Me resultó útil' ?></span>
                        <span class="reaction-count" data-reaction-count><?= $reactionsCount ?></span>
                    </button>
                </form>
            </div>
            <div class="sidebar-card utility-card">
                <h2 class="sidebar-title"><?= icon('share', 'icon icon-sm') ?> Compartir</h2>
                <div class="share-grid">
                    <a class="share-link" href="https://wa.me/?text=<?= e(rawurlencode($report['title'] . ' ' . $url)) ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                    <a class="share-link" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= e(rawurlencode($url)) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
                    <button class="share-link" type="button" data-copy="<?= e($url) ?>">Copiar enlace</button>
                </div>
            </div>
            <?php if (!empty($reviews)): ?>
                <div class="sidebar-card">
                    <h2 class="sidebar-title"><?= icon('message-circle', 'icon icon-sm') ?> Revisiones</h2>
                    <?php foreach (array_slice($reviews, 0, 3) as $review): ?>
                        <div class="review-note">
                            <span class="badge badge-<?= $review['decision'] === 'publicar' ? 'success' : ($review['decision'] === 'cambios' ? 'warning' : 'danger') ?>"><?= e(ucfirst($review['decision'])) ?></span>
                            <p><?= e($review['notes']) ?></p>
                            <small><?= e($review['reviewer_name']) ?> · <?= e(time_ago($review['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</section>
