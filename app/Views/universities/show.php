<?php
/** @var array<string, mixed> $university */
/** @var array<int, array<string, mixed>> $students */
/** @var array<int, array<string, mixed>> $cases */
/** @var array<int, array<string, mixed>> $reports */
/** @var array<int, array<string, mixed>> $opportunities */
?>
<section class="page-hero page-hero-compact">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <a href="<?= e(url('/universidades')) ?>">Universidades</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <span><?= e($university['acronym'] ?: $university['name']) ?></span>
        </nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow"><?= e($university['type'] === 'publica' ? 'Universidad pública' : 'Universidad privada') ?> · <?= e($university['state_name'] ?? 'México') ?></span>
                <h1><?= e($university['name']) ?></h1>
                <p>Estudiantes verificados que publican casos, reportes y oportunidades en AgroNegocios MX.</p>
            </div>
            <div class="page-hero-aside">
                <span class="big-number"><?= count($students) ?></span>
                <span><?= plural(count($students), 'estudiante verificado', 'estudiantes verificados') ?></span>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <div class="university-layout">
            <section>
                <h2 class="section-title-sm"><?= icon('users', 'icon icon-sm') ?> Estudiantes</h2>
                <?php if ($students === []): ?>
                    <div class="empty-inline">Aún no hay estudiantes verificados de esta institución. <a href="<?= e(url('/registro')) ?>">Sé la primera persona</a>.</div>
                <?php else: ?>
                    <div class="student-grid">
                        <?php foreach ($students as $student): ?>
                            <a class="student-card" href="<?= e(url('/estudiante/' . $student['id'])) ?>">
                                <?= avatar_tag((string) $student['name'], $student['avatar_url'] ?? null, 'avatar-md') ?>
                                <div>
                                    <strong><?= e($student['name']) ?></strong>
                                    <span><?= e($student['career'] ?: 'Agronegocios') ?><?php if (!empty($student['semester'])): ?> · <?= e(semester_label((int) $student['semester'])) ?><?php endif; ?></span>
                                    <small><?= (int) $student['published_cases'] ?> casos · <?= (int) $student['published_reports'] ?> reportes · <?= (int) $student['published_opportunities'] ?> oportunidades</small>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
            <aside class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('info', 'icon icon-sm') ?> Sobre la institución</h2>
                <dl class="plot-data">
                    <div><dt>Tipo</dt><dd><?= e($university['type'] === 'publica' ? 'Pública' : 'Privada') ?></dd></div>
                    <div><dt>Estado</dt><dd><?= e($university['state_name'] ?? 'México') ?></dd></div>
                    <?php if (!empty($university['website'])): ?><div><dt>Sitio</dt><dd><a href="<?= e($university['website']) ?>" target="_blank" rel="noopener noreferrer"><?= e(parse_url($university['website'], PHP_URL_HOST) ?: $university['website']) ?></a></dd></div><?php endif; ?>
                </dl>
                <p class="field-help">La verificación se hace por correo institucional o constancia de estudios. No se solicita cédula profesional.</p>
            </aside>
        </div>

        <?php if ($cases !== []): ?>
            <div class="section-head section-head-spaced"><div><span class="eyebrow">Casos</span><h2>Publicaciones recientes</h2></div></div>
            <div class="case-grid">
                <?php foreach ($cases as $case): ?>
                    <?php \App\Core\View::partial('partials/case-card', ['case' => $case]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
