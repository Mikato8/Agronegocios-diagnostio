<?php
/** @var string $type */
/** @var array<string, mixed> $content */
/** @var array<int, array<string, mixed>> $reviews */
/** @var array<int, array<string, mixed>> $comments */
/** @var array<int, array<string, mixed>> $files */
/** @var array<int, array<string, mixed>> $interests */
$route = content_route($type, (string) $content['slug']);
$status = (string) $content['status'];
?>
<div class="admin-page-head">
    <div>
        <a class="text-link" href="<?= e(url('/admin/contenidos?tipo=' . $type . '&estado=en_revision')) ?>"><?= icon('arrow-left', 'icon icon-xs') ?> Volver a la cola</a>
        <h1><?= e($content['title']) ?></h1>
        <p><?= e(content_type_label($type)) ?> · <?= e($content['author_name'] ?? '') ?> · <?= e($content['university_name'] ?? '') ?> · <span class="badge badge-<?= e(status_tone($status)) ?>"><?= e(status_label($status)) ?></span></p>
    </div>
    <div class="admin-page-actions">
        <a class="btn btn-outline btn-sm" href="<?= e($route) ?>" target="_blank" rel="noopener">Ver en el sitio <?= icon('external-link', 'icon icon-xs') ?></a>
        <?php if ($type === 'caso'): ?><a class="btn btn-ghost btn-sm" href="<?= e($route . '/pdf') ?>">PDF</a><?php endif; ?>
    </div>
</div>

<div class="admin-review-layout">
    <div class="admin-review-main">
        <section class="admin-card">
            <div class="admin-card-head"><h2 class="admin-card-title">Resumen</h2></div>
            <p class="review-summary"><?= e($content['summary'] ?? '') ?></p>
            <dl class="plot-data plot-data-wide">
                <div><dt>Sector</dt><dd><?= e($content['sector_name'] ?? '—') ?></dd></div>
                <div><dt>Cadena</dt><dd><?= e($content['chain_name'] ?? 'No especificada') ?></dd></div>
                <div><dt>Estado</dt><dd><?= e($content['state_name'] ?? '—') ?></dd></div>
                <?php if ($type === 'caso'): ?>
                    <div><dt>Tipo</dt><dd><?= e(company_type_label($content['company_type'] ?? null)) ?></dd></div>
                    <div><dt>Etapa</dt><dd><?= e(business_stage_label($content['stage'] ?? null)) ?></dd></div>
                    <div><dt>Año</dt><dd><?= (int) $content['year'] ?></dd></div>
                <?php elseif ($type === 'reporte'): ?>
                    <div><dt>Periodo</dt><dd><?= e(format_date($content['period_start'] ?? null, 'd/m/Y')) ?> – <?= e(format_date($content['period_end'] ?? null, 'd/m/Y')) ?></dd></div>
                    <div><dt>Moneda</dt><dd><?= e($content['currency'] ?? 'MXN') ?></dd></div>
                    <div><dt>Ingresos</dt><dd><?= e(money($content['revenue'] ?? null, (string) ($content['currency'] ?? 'MXN'))) ?></dd></div>
                <?php else: ?>
                    <div><dt>Tipo</dt><dd><?= e(opportunity_type_label($content['opportunity_type'] ?? null)) ?></dd></div>
                    <div><dt>Inversión</dt><dd><?= e(investment_range($content['investment_min'] ?? null, $content['investment_max'] ?? null, (string) ($content['currency'] ?? 'MXN'))) ?></dd></div>
                    <div><dt>Contacto</dt><dd><?= e($content['contact_name'] ?? '') ?></dd></div>
                <?php endif; ?>
            </dl>
        </section>

        <section class="admin-card">
            <div class="admin-card-head"><h2 class="admin-card-title">Texto para revisión</h2></div>
            <div class="review-text">
                <?php if ($type === 'caso'): ?>
                    <?php foreach (['Contexto' => 'context', 'Reto' => 'challenge', 'Diagnóstico' => 'diagnosis', 'Estrategia' => 'strategy', 'Resultados' => 'results', 'Aprendizajes' => 'lessons', 'Cifras' => 'financial_highlights', 'KPI' => 'kpis', 'Referencias' => 'references_text'] as $label => $field): ?>
                        <?php if (trim((string) ($content[$field] ?? '')) !== ''): ?>
                            <h3><?= e($label) ?></h3>
                            <div class="prose prose-sm"><?= nl2br(e($content[$field])) ?></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php elseif ($type === 'reporte'): ?>
                    <?php foreach (['Análisis' => 'analysis', 'Hallazgos' => 'findings', 'Recomendaciones' => 'recommendations', 'Supuestos' => 'assumptions', 'Referencias' => 'references_text'] as $label => $field): ?>
                        <?php if (trim((string) ($content[$field] ?? '')) !== ''): ?>
                            <h3><?= e($label) ?></h3>
                            <div class="prose prose-sm"><?= nl2br(e($content[$field])) ?></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach (['Descripción' => 'description', 'Propuesta de valor' => 'value_proposition', 'Mercado' => 'market_size', 'Requisitos' => 'requirements', 'Riesgos' => 'risks'] as $label => $field): ?>
                        <?php if (trim((string) ($content[$field] ?? '')) !== ''): ?>
                            <h3><?= e($label) ?></h3>
                            <div class="prose prose-sm"><?= nl2br(e($content[$field])) ?></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($files !== []): ?>
            <section class="admin-card">
                <div class="admin-card-head"><h2 class="admin-card-title">Anexos</h2></div>
                <div class="attachment-list">
                    <?php foreach ($files as $file): ?>
                        <a class="attachment-row" href="<?= e(url('/uploads/archivos/' . $file['path'])) ?>" target="_blank" rel="noopener">
                            <span class="attachment-icon"><?= icon('file-text', 'icon') ?></span>
                            <div><strong><?= e($file['original_name']) ?></strong><small><?= e(strtoupper((string) pathinfo($file['original_name'], PATHINFO_EXTENSION))) ?> · <?= e(number_format((int) $file['size_bytes'] / 1024, 0)) ?> KB</small></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($interests !== []): ?>
            <section class="admin-card">
                <div class="admin-card-head"><h2 class="admin-card-title">Interesados (<?= count($interests) ?>)</h2></div>
                <div class="interest-list">
                    <?php foreach ($interests as $interest): ?>
                        <div class="interest-row">
                            <div><strong><?= e($interest['student_name']) ?></strong><span><?= e($interest['university_acronym'] ?? '') ?> · <?= e($interest['student_email']) ?></span><p><?= e($interest['message']) ?></p></div>
                            <span class="badge badge-soft"><?= e($interest['status']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="admin-card">
            <div class="admin-card-head"><h2 class="admin-card-title">Comentarios (<?= count($comments) ?>)</h2></div>
            <?php if ($comments === []): ?>
                <div class="admin-empty"><p>Sin comentarios.</p></div>
            <?php else: ?>
                <div class="admin-list">
                    <?php foreach ($comments as $comment): ?>
                        <div class="admin-list-row">
                            <span class="admin-list-main"><strong><?= e(excerpt($comment['body'], 120)) ?></strong><small><?= e($comment['author_name']) ?> · <?= e($comment['status']) ?> · <?= e(time_ago($comment['created_at'])) ?></small></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <aside class="admin-review-side">
        <section class="admin-card">
            <div class="admin-card-head"><h2 class="admin-card-title">Decisión editorial</h2></div>
            <form method="post" action="<?= e(url('/admin/revision/' . $type . '/' . $content['id'])) ?>">
                <?= csrf_field() ?>
                <div class="field">
                    <label for="decision">Decisión</label>
                    <select id="decision" name="decision" required>
                        <option value="">Selecciona…</option>
                        <option value="publicar">Publicar</option>
                        <option value="cambios">Solicitar cambios</option>
                        <option value="rechazar">Rechazar</option>
                    </select>
                </div>
                <div class="field">
                    <label for="notes">Notas para el autor</label>
                    <textarea id="notes" name="notes" rows="6" maxlength="3000" placeholder="Explica la decisión, los cambios necesarios y los criterios aplicados…"></textarea>
                    <span class="field-help">Obligatorio para solicitar cambios o rechazar.</span>
                </div>
                <label class="check-line">
                    <input type="checkbox" name="featured" value="1" <?= !empty($content['featured']) ? 'checked' : '' ?>>
                    <span>Destacar en la portada si se publica</span>
                </label>
                <button class="btn btn-primary btn-block" type="submit">Registrar decisión</button>
            </form>
        </section>

        <section class="admin-card">
            <div class="admin-card-head"><h2 class="admin-card-title">Historial</h2></div>
            <?php if ($reviews === []): ?>
                <div class="admin-empty"><p>Sin revisiones previas.</p></div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-note">
                        <span class="badge badge-<?= $review['decision'] === 'publicar' ? 'success' : ($review['decision'] === 'cambios' ? 'warning' : 'danger') ?>"><?= e(status_label($review['decision'] === 'publicar' ? 'publicado' : ($review['decision'] === 'cambios' ? 'cambios_solicitados' : 'rechazado'))) ?></span>
                        <p><?= e($review['notes'] ?: 'Sin notas.') ?></p>
                        <small><?= e($review['reviewer_name']) ?> · <?= e(time_ago($review['created_at'])) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <section class="admin-card">
            <div class="admin-card-head"><h2 class="admin-card-title">Datos del autor</h2></div>
            <p><strong><?= e($content['author_name'] ?? '') ?></strong><br><span class="muted"><?= e($content['university_name'] ?? '') ?></span><br><span class="muted"><?= e($content['author_email'] ?? '') ?></span></p>
            <?php if (!empty($content['author_id'])): ?><a class="btn btn-outline btn-block btn-sm" href="<?= e(url('/admin/usuarios/' . $content['author_id'])) ?>">Ver cuenta</a><?php endif; ?>
        </section>
    </aside>
</div>
