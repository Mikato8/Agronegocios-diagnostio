<?php
/** @var array<string, mixed> $opportunity */
/** @var array<int, array<string, mixed>> $related */
/** @var array<int, array<string, mixed>> $comments */
/** @var array<int, array<string, mixed>> $reviews */
/** @var array<int, array<string, mixed>> $interests */
/** @var bool $reacted */
/** @var bool $bookmarked */
/** @var int $reactionsCount */
/** @var bool $hasInterest */
/** @var bool $canManage */
/** @var bool $preview */
/** @var bool $canEdit */
$url = url('/oportunidades/' . $opportunity['slug']);
$currency = (string) ($opportunity['currency'] ?? 'MXN');
$status = (string) $opportunity['status'];
$isOwner = (int) ($currentUser['id'] ?? 0) === (int) $opportunity['author_id'];
?>
<section class="case-header">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <a href="<?= e(url('/oportunidades')) ?>">Oportunidades</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <span><?= e(excerpt((string) $opportunity['title'], 60)) ?></span>
        </nav>

        <?php if ($preview): ?>
            <div class="notice notice-warning">
                <?= icon('eye', 'icon') ?>
                <div><strong>Vista previa.</strong><p>Esta oportunidad no es pública. Estado: <?= e(status_label($status)) ?>.</p></div>
                <?php if (can('moderar')): ?><a class="btn btn-sm btn-dark" href="<?= e(url('/admin/revision/oportunidad/' . $opportunity['id'])) ?>">Revisar</a><?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($opportunity['is_demo']): ?>
            <div class="notice notice-info">
                <?= icon('info', 'icon') ?>
                <div><strong>Oportunidad demostrativa.</strong><p>Contenido ficticio para mostrar el funcionamiento de la bolsa.</p></div>
            </div>
        <?php endif; ?>

        <div class="case-title-row">
            <div class="case-title-main">
                <div class="case-kicker">
                    <span class="crops-pill"><?= icon('sparkles', 'icon icon-xs') ?> <?= e(opportunity_type_label($opportunity['opportunity_type'] ?? null)) ?></span>
                    <span class="badge badge-soft"><?= e($opportunity['sector_name'] ?? '') ?></span>
                    <span class="badge badge-neutral"><?= e(opportunity_stage_label($opportunity['stage'] ?? null)) ?></span>
                    <?php if (!empty($opportunity['featured'])): ?><span class="badge badge-accent">Destacada</span><?php endif; ?>
                    <?php if (!empty($opportunity['is_confidential'])): ?><span class="badge badge-warning"><?= icon('lock', 'icon icon-xs') ?> Contacto reservado</span><?php endif; ?>
                </div>
                <h1><?= e($opportunity['title']) ?></h1>
                <p class="case-deck"><?= e($opportunity['summary']) ?></p>
                <div class="case-byline">
                    <?= avatar_tag((string) $opportunity['author_name'], $opportunity['author_avatar_url'] ?? null, 'avatar-sm') ?>
                    <div>
                        <strong><?= e($opportunity['author_name']) ?></strong>
                        <span><?= e($opportunity['university_name'] ?? 'Estudiante de agronegocios') ?><?php if ($opportunity['author_status'] === 'verificado'): ?> · <span class="verified"><?= icon('check-circle', 'icon icon-xs') ?> Verificado</span><?php endif; ?></span>
                    </div>
                    <span class="byline-divider"></span>
                    <span class="byline-meta"><?= icon('calendar', 'icon icon-xs') ?> <?= e(format_date($opportunity['published_at'] ?? null, 'd M Y')) ?></span>
                    <span class="byline-meta"><?= icon('users', 'icon icon-xs') ?> <?= (int) $opportunity['interests_count'] ?> interesados</span>
                    <span class="byline-meta"><?= icon('eye', 'icon icon-xs') ?> <?= number_format((int) $opportunity['views']) ?> vistas</span>
                </div>
            </div>
            <div class="case-title-actions">
                <?php if ($canEdit): ?><a class="btn btn-outline btn-sm" href="<?= e($url . '/editar') ?>"><?= icon('edit', 'icon icon-xs') ?> Editar</a><?php endif; ?>
                <a class="btn btn-ghost btn-sm" href="<?= e($url . '/pdf') ?>"><?= icon('download', 'icon icon-xs') ?> PDF</a>
                <form method="post" action="<?= e($url . '/guardar') ?>">
                    <?= csrf_field() ?>
                    <button class="btn <?= $bookmarked ? 'btn-primary' : 'btn-ghost' ?> btn-sm" type="submit"><?= icon('bookmark', 'icon icon-xs') ?> <?= $bookmarked ? 'Guardada' : 'Guardar' ?></button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container case-layout">
        <article class="case-article">
            <div class="opp-hero-facts">
                <div><span>Inversión</span><strong><?= e(investment_range($opportunity['investment_min'] ?? null, $opportunity['investment_max'] ?? null, $currency)) ?></strong></div>
                <div><span>Retorno esperado</span><strong><?= e($opportunity['expected_return_pct'] === null ? 'Por definir' : pct($opportunity['expected_return_pct'])) ?></strong></div>
                <div><span>Recuperación</span><strong><?= e($opportunity['payback_months'] === null ? 'Por definir' : (int) $opportunity['payback_months'] . ' meses') ?></strong></div>
                <div><span>Cierra</span><strong><?= e($opportunity['expires_at'] ? format_date($opportunity['expires_at'], 'd M Y') : 'Abierta') ?></strong></div>
            </div>

            <section class="case-section">
                <h2><span class="section-index">01</span> Descripción de la oportunidad</h2>
                <div class="prose"><?= nl2br(e($opportunity['description'])) ?></div>
            </section>

            <section class="case-section case-section-highlight">
                <h2><span class="section-index">02</span> Propuesta de valor</h2>
                <div class="prose"><?= nl2br(e($opportunity['value_proposition'])) ?></div>
            </section>

            <?php if (trim((string) $opportunity['market_size']) !== ''): ?>
                <section class="case-section">
                    <h2><span class="section-index">03</span> Mercado</h2>
                    <div class="prose"><?= nl2br(e($opportunity['market_size'])) ?></div>
                </section>
            <?php endif; ?>

            <section class="case-section">
                <h2><span class="section-index">04</span> Requisitos para participar</h2>
                <div class="prose"><?= nl2br(e($opportunity['requirements'])) ?></div>
            </section>

            <?php if (trim((string) $opportunity['risks']) !== ''): ?>
                <section class="case-section">
                    <h2><span class="section-index">05</span> Riesgos y consideraciones</h2>
                    <div class="prose"><?= nl2br(e($opportunity['risks'])) ?></div>
                </section>
            <?php endif; ?>

            <section class="case-section" id="interes">
                <h2><span class="section-index">06</span> ¿Te interesa?</h2>
                <?php if ($opportunity['is_confidential']): ?>
                    <div class="interest-box">
                        <?= icon('lock', 'icon') ?>
                        <div>
                            <strong>El contacto es reservado.</strong>
                            <p>Envía una expresión de interés con tu perfil y el autor decidirá si comparte sus datos. No publiques información confidencial en el mensaje.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="contact-grid">
                        <div><span>Contacto</span><strong><?= e($opportunity['contact_name']) ?></strong></div>
                        <?php if ($currentUser !== null && $currentUser['status'] === 'verificado'): ?>
                            <div><span>Correo</span><strong><a href="mailto:<?= e($opportunity['contact_email']) ?>"><?= e($opportunity['contact_email']) ?></a></strong></div>
                            <?php if (!empty($opportunity['contact_phone'])): ?><div><span>Teléfono</span><strong><?= e($opportunity['contact_phone']) ?></strong></div><?php endif; ?>
                        <?php else: ?>
                            <div><span>Correo</span><strong>Inicia sesión con una cuenta verificada para verlo</strong></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($isOwner): ?>
                    <p class="case-muted">Eres quien publica esta oportunidad. Las expresiones de interés aparecen en el panel lateral.</p>
                <?php elseif ($currentUser === null): ?>
                    <div class="comment-cta">
                        <p><strong>¿Quieres conectar?</strong> Crea una cuenta verificada para enviar tu interés.</p>
                        <a class="btn btn-primary btn-sm" href="<?= e(url('/registro')) ?>">Crear cuenta</a>
                    </div>
                <?php elseif ($currentUser['status'] !== 'verificado'): ?>
                    <div class="comment-cta"><p>Tu cuenta está en revisión. Podrás enviar tu interés cuando esté verificada.</p><a class="btn btn-outline btn-sm" href="<?= e(url('/perfil')) ?>">Ver mi perfil</a></div>
                <?php elseif ($hasInterest): ?>
                    <div class="notice notice-success">
                        <?= icon('check-circle', 'icon') ?>
                        <div><strong>Ya enviaste tu interés.</strong><p>El autor tiene tus datos de contacto y podrá escribirte. Puedes actualizar tu mensaje abajo.</p></div>
                    </div>
                <?php endif; ?>

                <?php if ($currentUser !== null && $currentUser['status'] === 'verificado' && !$isOwner): ?>
                    <form class="interest-form" method="post" action="<?= e($url . '/interes') ?>">
                        <?= csrf_field() ?>
                        <label for="message"><?= $hasInterest ? 'Actualiza tu mensaje' : 'Cuéntale al autor por qué te interesa' ?></label>
                        <textarea id="message" name="message" rows="4" maxlength="1200" required placeholder="Tu formación, experiencia, qué puedes aportar y cómo te gustaría participar…"></textarea>
                        <div class="comment-form-foot">
                            <span>Mínimo 20 caracteres. Tu correo se comparte sólo con el autor.</span>
                            <button class="btn btn-primary btn-sm" type="submit"><?= $hasInterest ? 'Actualizar interés' : 'Enviar interés' ?></button>
                        </div>
                    </form>
                <?php endif; ?>
            </section>

            <?php if ($canManage && $interests !== []): ?>
                <section class="case-section">
                    <h2><?= icon('users', 'icon icon-sm') ?> Personas interesadas <span class="count-badge"><?= count($interests) ?></span></h2>
                    <div class="interest-list">
                        <?php foreach ($interests as $interest): ?>
                            <div class="interest-row">
                                <div>
                                    <strong><?= e($interest['student_name']) ?></strong>
                                    <span><?= e($interest['university_acronym'] ?? '') ?> · <?= e($interest['career'] ?? '') ?> · <?= e(semester_label($interest['semester'] !== null ? (int) $interest['semester'] : null)) ?></span>
                                    <p><?= e($interest['message']) ?></p>
                                    <small><?= e($interest['student_email']) ?> · <?= e(time_ago($interest['created_at'])) ?></small>
                                </div>
                                <form method="post" action="<?= e($url . '/interes/' . $interest['id']) ?>">
                                    <?= csrf_field() ?>
                                    <select name="status" data-auto-submit>
                                        <option value="nuevo" <?= $interest['status'] === 'nuevo' ? 'selected' : '' ?>>Nuevo</option>
                                        <option value="contactado" <?= $interest['status'] === 'contactado' ? 'selected' : '' ?>>Contactado</option>
                                        <option value="cerrado" <?= $interest['status'] === 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
                                    </select>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <div class="citation-box">
                <div>
                    <span class="summary-label">Aviso importante</span>
                    <p>Esta oportunidad es una publicación de un estudiante o docente. No es una oferta de valores ni una recomendación de inversión. Verifica la información y los riesgos antes de comprometer recursos.</p>
                    <p class="case-muted">Licencia: <?= e($opportunity['license']) ?> · Folio <?= e($opportunity['folio']) ?></p>
                </div>
            </div>

            <section class="comments" id="comentarios">
                <div class="comments-head">
                    <h2>Preguntas de la comunidad <span class="count-badge"><?= count($comments) ?></span></h2>
                    <p>Pregunta sobre el mercado, los requisitos o los riesgos. No publiques datos personales.</p>
                </div>
                <?php if ($comments === []): ?>
                    <div class="comments-empty"><?= icon('message-circle', 'icon') ?><p>Aún no hay preguntas. Sé la primera persona en preguntar.</p></div>
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
                    <div class="comment-cta"><p>Inicia sesión para preguntar.</p><a class="btn btn-primary btn-sm" href="<?= e(url('/acceder')) ?>">Acceder</a></div>
                <?php elseif ($currentUser['status'] === 'verificado'): ?>
                    <form class="comment-form" method="post" action="<?= e($url . '/comentarios') ?>">
                        <?= csrf_field() ?>
                        <label for="comment-body">Añadir una pregunta</label>
                        <textarea id="comment-body" name="body" rows="4" maxlength="2000" required placeholder="¿Qué necesitas saber antes de participar?"></textarea>
                        <div class="comment-form-foot"><span>Máximo 2,000 caracteres.</span><button class="btn btn-primary btn-sm" type="submit">Publicar</button></div>
                    </form>
                <?php else: ?>
                    <div class="comment-cta"><p>Tu cuenta está en revisión.</p></div>
                <?php endif; ?>
            </section>
        </article>

        <aside class="case-sidebar">
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('file-text', 'icon icon-sm') ?> Ficha</h2>
                <dl class="plot-data">
                    <div><dt>Tipo</dt><dd><?= e(opportunity_type_label($opportunity['opportunity_type'] ?? null)) ?></dd></div>
                    <div><dt>Etapa</dt><dd><?= e(opportunity_stage_label($opportunity['stage'] ?? null)) ?></dd></div>
                    <div><dt>Sector</dt><dd><?= e($opportunity['sector_name'] ?? '—') ?></dd></div>
                    <div><dt>Cadena</dt><dd><?= e($opportunity['chain_name'] ?? 'No especificada') ?></dd></div>
                    <div><dt>Ubicación</dt><dd><?= e(($opportunity['municipality'] ?? '') !== '' ? $opportunity['municipality'] . ', ' : '') . ($opportunity['state_name'] ?? 'México') ?></dd></div>
                    <div><dt>Folio</dt><dd><?= e($opportunity['folio']) ?></dd></div>
                </dl>
            </div>
            <div class="sidebar-card author-card">
                <?= avatar_tag((string) $opportunity['author_name'], $opportunity['author_avatar_url'] ?? null, 'avatar-lg') ?>
                <h2><a href="<?= e(url('/estudiante/' . $opportunity['author_id'])) ?>"><?= e($opportunity['author_name']) ?></a></h2>
                <p class="author-inst"><?= e($opportunity['university_name'] ?? 'Estudiante de agronegocios') ?></p>
                <?php if ($opportunity['author_status'] === 'verificado'): ?><span class="badge badge-success"><?= icon('check-circle', 'icon icon-xs') ?> Cuenta verificada</span><?php endif; ?>
            </div>
            <div class="sidebar-card utility-card">
                <h2 class="sidebar-title"><?= icon('check-circle', 'icon icon-sm') ?> ¿Te resultó útil?</h2>
                <form method="post" action="<?= e($url . '/util') ?>" data-reaction-form>
                    <?= csrf_field() ?>
                    <button class="btn <?= $reacted ? 'btn-primary' : 'btn-outline' ?> btn-block" type="submit" data-reaction-button>
                        <?= icon('check-circle', 'icon icon-sm') ?> <span data-reaction-label><?= $reacted ? 'Marcada' : 'Me interesa' ?></span>
                        <span class="reaction-count" data-reaction-count><?= $reactionsCount ?></span>
                    </button>
                </form>
            </div>
            <div class="sidebar-card utility-card">
                <h2 class="sidebar-title"><?= icon('share', 'icon icon-sm') ?> Compartir</h2>
                <div class="share-grid">
                    <a class="share-link" href="https://wa.me/?text=<?= e(rawurlencode($opportunity['title'] . ' ' . $url)) ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                    <a class="share-link" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= e(rawurlencode($url)) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
                    <button class="share-link" type="button" data-copy="<?= e($url) ?>">Copiar enlace</button>
                </div>
            </div>
            <?php if ($related !== []): ?>
                <div class="sidebar-card">
                    <h2 class="sidebar-title"><?= icon('sparkles', 'icon icon-sm') ?> Oportunidades similares</h2>
                    <div class="similar-list">
                        <?php foreach ($related as $item): ?>
                            <a href="<?= e(url('/oportunidades/' . $item['slug'])) ?>">
                                <span class="similar-crops"><?= e($item['sector_emoji'] ?? '🌱') ?></span>
                                <span><strong><?= e(excerpt((string) $item['title'], 70)) ?></strong><small><?= e($item['state_name'] ?? 'México') ?> · <?= e(investment_range($item['investment_min'] ?? null, $item['investment_max'] ?? null)) ?></small></span>
                                <?= icon('chevron-right', 'icon icon-xs') ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</section>
