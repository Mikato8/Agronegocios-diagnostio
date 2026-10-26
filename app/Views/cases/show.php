<?php
/** @var array<string, mixed> $case */
/** @var array<int, array<string, mixed>> $comments */
/** @var array<int, array<string, mixed>> $reviews */
/** @var array<int, array<string, mixed>> $similar */
/** @var bool $reacted */
/** @var bool $bookmarked */
/** @var int $reactionsCount */
/** @var bool $preview */
/** @var bool $canEdit */
/** @var bool $canModerate */
$caseUrl = url('/casos/' . $case['slug']);
$publishedYear = $case['published_at'] ? date('Y', strtotime((string) $case['published_at'])) : date('Y');
$citation = $case['author_name'] . ' (' . $publishedYear . '). ' . $case['title'] . '. AgroNegocios MX. ' . $caseUrl;
$status = (string) $case['status'];
?>
<section class="case-header">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <a href="<?= e(url('/casos')) ?>">Casos</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <span><?= e(excerpt((string) $case['title'], 60)) ?></span>
        </nav>

        <?php if ($preview): ?>
            <div class="notice notice-warning">
                <?= icon('eye', 'icon') ?>
                <div>
                    <strong>Vista previa.</strong>
                    <p>Este caso no es público. Estado actual: <?= e(status_label($status)) ?>. Solo el autor y el comité editorial pueden verlo.</p>
                </div>
                <?php if ($canModerate): ?>
                    <a class="btn btn-sm btn-dark" href="<?= e(url('/admin/revision/caso/' . $case['id'])) ?>">Revisar en el panel</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($case['is_demo']): ?>
            <div class="notice notice-info">
                <?= icon('info', 'icon') ?>
                <div>
                    <strong>Contenido demostrativo.</strong>
                    <p>Este caso se elaboró para mostrar el flujo editorial. Los nombres, empresas y cifras son ficticios.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($status === 'cambios_solicitados' && !empty($reviews)): ?>
            <div class="notice notice-warning">
                <?= icon('alert-triangle', 'icon') ?>
                <div>
                    <strong>El comité solicitó cambios.</strong>
                    <p><?= e($reviews[0]['notes']) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="case-title-row">
            <div class="case-title-main">
                <div class="case-kicker">
                    <span class="crops-pill"><?= e($case['sector_emoji'] ?? '🌱') ?> <?= e($case['sector_name'] ?? 'Agronegocios') ?></span>
                    <?php if (!empty($case['chain_name'])): ?><span class="badge badge-soft"><?= e($case['chain_emoji'] . ' ' . $case['chain_name']) ?></span><?php endif; ?>
                    <span class="badge badge-neutral"><?= e(business_stage_label($case['stage'] ?? null)) ?></span>
                    <?php if (!empty($case['featured'])): ?><span class="badge badge-accent"><?= icon('sparkles', 'icon icon-xs') ?> Destacado</span><?php endif; ?>
                </div>
                <h1><?= e($case['title']) ?></h1>
                <p class="case-deck"><?= e($case['summary']) ?></p>
                <div class="case-byline">
                    <?= avatar_tag((string) $case['author_name'], $case['author_avatar_url'] ?? null, 'avatar-sm') ?>
                    <div>
                        <strong><?= e($case['author_name']) ?></strong>
                        <span><?= e($case['university_name'] ?? ($case['author_career'] ?? 'Estudiante de agronegocios')) ?><?php if ($case['author_status'] === 'verificado'): ?> · <span class="verified"><?= icon('check-circle', 'icon icon-xs') ?> Verificado</span><?php endif; ?></span>
                    </div>
                    <span class="byline-divider"></span>
                    <span class="byline-meta"><?= icon('calendar', 'icon icon-xs') ?> <?= e(format_date($case['published_at'] ?? null, 'd M Y')) ?></span>
                    <span class="byline-meta"><?= icon('eye', 'icon icon-xs') ?> <?= number_format((int) $case['views']) ?> vistas</span>
                    <span class="byline-meta"><?= icon('message-circle', 'icon icon-xs') ?> <?= (int) $case['comments_count'] ?> comentarios</span>
                </div>
            </div>
            <div class="case-title-actions">
                <?php if ($canEdit): ?>
                    <a class="btn btn-outline btn-sm" href="<?= e(url('/casos/' . $case['slug'] . '/editar')) ?>"><?= icon('edit', 'icon icon-xs') ?> Editar</a>
                <?php endif; ?>
                <button class="btn btn-ghost btn-sm" type="button" data-copy="<?= e($citation) ?>"><?= icon('copy', 'icon icon-xs') ?> Citar</button>
                <a class="btn btn-ghost btn-sm" href="<?= e(url('/casos/' . $case['slug'] . '/pdf')) ?>"><?= icon('download', 'icon icon-xs') ?> PDF</a>
                <form method="post" action="<?= e(url('/casos/' . $case['slug'] . '/guardar')) ?>">
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
            <div class="case-summary">
                <div>
                    <span class="summary-label">El reto</span>
                    <p><?= e(excerpt((string) $case['challenge'], 420)) ?></p>
                </div>
                <div>
                    <span class="summary-label">Resultado principal</span>
                    <p class="summary-diagnosis"><?= e(excerpt((string) $case['results'], 420)) ?></p>
                </div>
            </div>

            <div class="case-facts">
                <div><span>Sector</span><strong><?= e($case['sector_name'] ?? '—') ?></strong></div>
                <div><span>Organización</span><strong><?= e(company_type_label($case['company_type'] ?? null)) ?></strong></div>
                <div><span>Ubicación</span><strong><?= e(($case['municipality'] ?? '') !== '' ? $case['municipality'] . ', ' : '') . ($case['state_name'] ?? 'México') ?></strong></div>
                <div><span>Año</span><strong><?= (int) $case['year'] ?></strong></div>
            </div>

            <?php if (!empty($case['images'])): ?>
                <section class="case-section case-gallery-section">
                    <h2><?= icon('folder', 'icon icon-sm') ?> Imágenes del proyecto</h2>
                    <div class="case-gallery">
                        <?php foreach ($case['images'] as $image): ?>
                            <figure>
                                <img src="<?= e(url('/uploads/casos/' . $image['path'])) ?>" alt="<?= e(($image['caption'] ?? '') !== '' ? $image['caption'] : 'Imagen del caso') ?>" loading="lazy">
                                <?php if (!empty($image['caption'])): ?><figcaption><?= e($image['caption']) ?></figcaption><?php endif; ?>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                    <p class="case-disclaimer">Imágenes publicadas con autorización y fines educativos. No permiten identificar a personas o predios sin consentimiento.</p>
                </section>
            <?php endif; ?>

            <section class="case-section">
                <h2><span class="section-index">01</span> Contexto de la empresa o proyecto</h2>
                <div class="prose"><?= nl2br(e($case['context'])) ?></div>
            </section>

            <section class="case-section">
                <h2><span class="section-index">02</span> El reto y la decisión</h2>
                <div class="prose"><?= nl2br(e($case['challenge'])) ?></div>
            </section>

            <section class="case-section">
                <h2><span class="section-index">03</span> Diagnóstico y análisis</h2>
                <div class="prose"><?= nl2br(e($case['diagnosis'])) ?></div>
            </section>

            <section class="case-section case-section-highlight">
                <h2><span class="section-index">04</span> Estrategia implementada</h2>
                <div class="prose"><?= nl2br(e($case['strategy'])) ?></div>
            </section>

            <section class="case-section">
                <h2><span class="section-index">05</span> Resultados</h2>
                <div class="prose"><?= nl2br(e($case['results'])) ?></div>
            </section>

            <?php if (trim((string) $case['financial_highlights']) !== '' || trim((string) $case['kpis']) !== ''): ?>
                <section class="case-section">
                    <h2><span class="section-index">06</span> Cifras e indicadores</h2>
                    <div class="split-prose">
                        <?php if (trim((string) $case['financial_highlights']) !== ''): ?>
                            <div>
                                <span class="summary-label">Cifras financieras</span>
                                <div class="prose prose-sm"><?= nl2br(e($case['financial_highlights'])) ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (trim((string) $case['kpis']) !== ''): ?>
                            <div>
                                <span class="summary-label">Indicadores clave</span>
                                <div class="prose prose-sm"><?= nl2br(e($case['kpis'])) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>

            <section class="case-section">
                <h2><span class="section-index">07</span> Aprendizajes</h2>
                <div class="prose prose-discussion"><?= nl2br(e($case['lessons'])) ?></div>
            </section>

            <?php if (trim((string) $case['references_text']) !== ''): ?>
                <section class="case-section case-references">
                    <h2><span class="section-index">08</span> Referencias y fuentes</h2>
                    <div class="prose"><?= nl2br(e($case['references_text'])) ?></div>
                </section>
            <?php endif; ?>

            <div class="citation-box">
                <div>
                    <span class="summary-label">Cómo citar este caso</span>
                    <p><?= e($citation) ?></p>
                    <p class="case-muted">Licencia: <?= e($case['license']) ?></p>
                </div>
                <button class="btn btn-outline btn-sm" type="button" data-copy="<?= e($citation) ?>"><?= icon('copy', 'icon icon-xs') ?> Copiar</button>
            </div>

            <section class="comments" id="comentarios">
                <div class="comments-head">
                    <h2>Discusión <span class="count-badge"><?= count($comments) ?></span></h2>
                    <p>Comentarios de estudiantes verificados. Aporta evidencia, pregunta o comparte una experiencia comparable.</p>
                </div>

                <?php if ($comments === []): ?>
                    <div class="comments-empty">
                        <?= icon('message-circle', 'icon') ?>
                        <p>Aún no hay comentarios. Inicia la conversación.</p>
                    </div>
                <?php else: ?>
                    <ol class="comment-list">
                        <?php foreach ($comments as $comment): ?>
                            <li class="comment <?= $comment['status'] === 'oculto' ? 'is-hidden' : '' ?>" id="comentario-<?= (int) $comment['id'] ?>">
                                <?= avatar_tag((string) $comment['author_name'], $comment['author_avatar_url'] ?? null, 'avatar-sm') ?>
                                <div class="comment-body">
                                    <div class="comment-head">
                                        <strong><?= e($comment['author_name']) ?></strong>
                                        <?php if (!empty($comment['university_acronym'])): ?><span class="badge badge-soft"><?= e($comment['university_acronym']) ?></span><?php endif; ?>
                                        <?php if ($comment['status'] === 'oculto'): ?><span class="badge badge-warning">Oculto</span><?php endif; ?>
                                        <time datetime="<?= e($comment['created_at']) ?>"><?= e(time_ago($comment['created_at'])) ?></time>
                                    </div>
                                    <p><?= nl2br(e($comment['body'])) ?></p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>

                <?php if ($currentUser === null): ?>
                    <div class="comment-cta">
                        <p><strong>¿Quieres aportar a la discusión?</strong> Inicia sesión con tu cuenta verificada para comentar.</p>
                        <a class="btn btn-primary btn-sm" href="<?= e(url('/acceder')) ?>">Acceder</a>
                    </div>
                <?php elseif ($currentUser['status'] !== 'verificado'): ?>
                    <div class="comment-cta">
                        <p><strong>Tu cuenta está en revisión.</strong> Podrás comentar cuando verifiquemos tu correo institucional o tu constancia de estudios.</p>
                        <a class="btn btn-outline btn-sm" href="<?= e(url('/perfil')) ?>">Ver mi perfil</a>
                    </div>
                <?php else: ?>
                    <form class="comment-form" method="post" action="<?= e(url('/casos/' . $case['slug'] . '/comentarios')) ?>">
                        <?= csrf_field() ?>
                        <label for="comment-body">Añadir un comentario</label>
                        <textarea id="comment-body" name="body" rows="4" maxlength="2000" required placeholder="Comparte una observación, una pregunta o una experiencia comparable…"></textarea>
                        <div class="comment-form-foot">
                            <span>Máximo 2,000 caracteres. No incluyas datos personales.</span>
                            <button class="btn btn-primary btn-sm" type="submit">Publicar comentario</button>
                        </div>
                    </form>
                <?php endif; ?>
            </section>
        </article>

        <aside class="case-sidebar">
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('file-text', 'icon icon-sm') ?> Ficha del caso</h2>
                <dl class="plot-data">
                    <div><dt>Sector</dt><dd><?= e($case['sector_name'] ?? '—') ?></dd></div>
                    <div><dt>Cadena</dt><dd><?= e($case['chain_name'] ?? 'No especificada') ?></dd></div>
                    <div><dt>Organización</dt><dd><?= e(company_type_label($case['company_type'] ?? null)) ?></dd></div>
                    <div><dt>Etapa</dt><dd><?= e(business_stage_label($case['stage'] ?? null)) ?></dd></div>
                    <div><dt>Ubicación</dt><dd><?= e(($case['municipality'] ?? '') !== '' ? $case['municipality'] . ', ' : '') . ($case['state_name'] ?? 'México') ?></dd></div>
                    <div><dt>Año</dt><dd><?= (int) $case['year'] ?></dd></div>
                    <div><dt>Folio</dt><dd><?= e($case['folio']) ?></dd></div>
                </dl>
            </div>

            <div class="sidebar-card author-card">
                <?= avatar_tag((string) $case['author_name'], $case['author_avatar_url'] ?? null, 'avatar-lg') ?>
                <h2><a href="<?= e(url('/estudiante/' . $case['author_id'])) ?>"><?= e($case['author_name']) ?></a></h2>
                <p class="author-inst"><?= e($case['university_name'] ?? 'Estudiante de agronegocios') ?></p>
                <?php if (!empty($case['author_career'])): ?><p class="author-bio"><?= e($case['author_career']) ?><?php if (!empty($case['author_semester'])): ?> · <?= e(semester_label((int) $case['author_semester'])) ?><?php endif; ?></p><?php endif; ?>
                <?php if ($case['author_status'] === 'verificado'): ?>
                    <span class="badge badge-success"><?= icon('check-circle', 'icon icon-xs') ?> Cuenta verificada</span>
                <?php endif; ?>
                <?php if (!empty($case['author_bio'])): ?><p class="author-bio"><?= e(excerpt($case['author_bio'], 140)) ?></p><?php endif; ?>
            </div>

            <div class="sidebar-card utility-card">
                <h2 class="sidebar-title"><?= icon('check-circle', 'icon icon-sm') ?> ¿Te resultó útil?</h2>
                <p>Reconoce el trabajo del autor y ayuda a destacar los casos con valor para la comunidad.</p>
                <form method="post" action="<?= e(url('/casos/' . $case['slug'] . '/util')) ?>" data-reaction-form>
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
                    <a class="share-link" href="https://wa.me/?text=<?= e(rawurlencode($case['title'] . ' ' . $caseUrl)) ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                    <a class="share-link" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= e(rawurlencode($caseUrl)) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
                    <a class="share-link" href="mailto:?subject=<?= e(rawurlencode($case['title'])) ?>&body=<?= e(rawurlencode($caseUrl)) ?>">Correo</a>
                    <button class="share-link" type="button" data-copy="<?= e($caseUrl) ?>">Copiar enlace</button>
                </div>
                <p class="license-note"><?= icon('shield', 'icon icon-xs') ?> <?= e($case['license']) ?></p>
            </div>

            <?php if ($similar !== []): ?>
                <div class="sidebar-card">
                    <h2 class="sidebar-title"><?= icon('book-open', 'icon icon-sm') ?> Casos similares</h2>
                    <div class="similar-list">
                        <?php foreach ($similar as $item): ?>
                            <a href="<?= e(url('/casos/' . $item['slug'])) ?>">
                                <span class="similar-crops"><?= e($item['sector_emoji'] ?? '🌱') ?></span>
                                <span>
                                    <strong><?= e(excerpt((string) $item['title'], 70)) ?></strong>
                                    <small><?= e($item['sector_name'] ?? '') ?> · <?= e($item['state_name'] ?? 'México') ?></small>
                                </span>
                                <?= icon('chevron-right', 'icon icon-xs') ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($reviews) && ($canModerate || (int) $case['author_id'] === (int) ($currentUser['id'] ?? 0))): ?>
                <div class="sidebar-card">
                    <h2 class="sidebar-title"><?= icon('message-circle', 'icon icon-sm') ?> Revisiones editoriales</h2>
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
