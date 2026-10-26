<?php
/** @var array<string, mixed> $case */
$href = content_route('caso', (string) $case['slug']);
$cover = $case['cover_image'] ?? null;
?>
<article class="case-card">
    <a class="case-card-media" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true">
        <?php if (!empty($cover)): ?>
            <img src="<?= e(url('/uploads/casos/' . $cover)) ?>" alt="" loading="lazy">
        <?php else: ?>
            <span class="case-card-emoji"><?= e($case['sector_emoji'] ?? '🌱') ?></span>
            <span class="case-card-pattern"></span>
        <?php endif; ?>
        <span class="case-card-crop"><?= e($case['sector_name'] ?? 'Agronegocios') ?></span>
        <?php if (!empty($case['is_demo'])): ?><span class="badge badge-demo">Demostrativo</span><?php endif; ?>
    </a>
    <div class="case-card-body">
        <div class="case-card-meta">
            <span class="badge badge-soft"><?= e(business_stage_label($case['stage'] ?? null)) ?></span>
            <?php if (!empty($case['state_name'])): ?>
                <span class="meta-dot"><?= icon('map-pin', 'icon icon-xs') ?> <?= e($case['state_name']) ?></span>
            <?php endif; ?>
        </div>
        <h3><a href="<?= e($href) ?>"><?= e($case['title']) ?></a></h3>
        <p class="case-card-diagnosis"><?= e(excerpt((string) ($case['summary'] ?? ''), 118)) ?></p>
        <div class="case-card-footer">
            <span class="case-author">
                <span class="avatar avatar-xs"><?= e(initials((string) ($case['author_name'] ?? 'AN'))) ?></span>
                <span><?= e($case['author_name'] ?? 'Autor') ?></span>
            </span>
            <span class="case-card-data">
                <?= icon('calendar', 'icon icon-xs') ?> <?= e(format_date($case['published_at'] ?? null, 'd/m/Y')) ?>
                <span class="case-stat"><?= icon('eye', 'icon icon-xs') ?> <?= (int) ($case['views'] ?? 0) ?></span>
            </span>
        </div>
    </div>
</article>
