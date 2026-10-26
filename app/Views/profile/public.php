<?php
/** @var array<string, mixed> $user */
/** @var array<int, array<string, mixed>> $cases */
/** @var array<int, array<string, mixed>> $reports */
/** @var array<int, array<string, mixed>> $opportunities */
?>
<section class="page-hero page-hero-compact">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <a href="<?= e(url('/universidades')) ?>">Universidades</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <span><?= e($user['name']) ?></span>
        </nav>
        <div class="profile-hero">
            <?= avatar_tag((string) $user['name'], $user['avatar_url'] ?? null, 'avatar-xl') ?>
            <div class="profile-hero-main">
                <div class="case-kicker">
                    <span class="badge badge-success"><?= icon('check-circle', 'icon icon-xs') ?> Cuenta verificada</span>
                    <span class="badge badge-soft"><?= e((string) config('app.roles.' . $user['role'], $user['role'])) ?></span>
                </div>
                <h1><?= e($user['name']) ?></h1>
                <p><?= e($user['university_name'] ?? 'Universidad no indicada') ?> · <?= e($user['career'] ?: 'Agronegocios') ?><?php if (!empty($user['semester'])): ?> · <?= e(semester_label((int) $user['semester'])) ?><?php endif; ?></p>
                <?php if (!empty($user['bio'])): ?><p class="profile-bio"><?= e($user['bio']) ?></p><?php endif; ?>
            </div>
            <div class="profile-hero-stats">
                <div><strong><?= count($cases) ?></strong><span>casos</span></div>
                <div><strong><?= count($reports) ?></strong><span>reportes</span></div>
                <div><strong><?= count($opportunities) ?></strong><span>oportunidades</span></div>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">Aportaciones públicas</span><h2>Casos</h2></div>
        </div>
        <?php if ($cases === []): ?>
            <p class="muted">Aún no hay casos publicados.</p>
        <?php else: ?>
            <div class="case-grid">
                <?php foreach ($cases as $case): ?>
                    <?php \App\Core\View::partial('partials/case-card', ['case' => $case]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($reports !== []): ?>
            <div class="section-head section-head-spaced"><div><span class="eyebrow">Finanzas</span><h2>Reportes</h2></div></div>
            <div class="report-grid">
                <?php foreach ($reports as $report): ?>
                    <?php \App\Core\View::partial('partials/report-card', ['report' => $report]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($opportunities !== []): ?>
            <div class="section-head section-head-spaced"><div><span class="eyebrow">Bolsa</span><h2>Oportunidades</h2></div></div>
            <div class="opp-grid">
                <?php foreach ($opportunities as $opportunity): ?>
                    <?php \App\Core\View::partial('partials/opp-card', ['opportunity' => $opportunity]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
