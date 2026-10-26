<?php
/** @var array<string, mixed> $opportunity */
$href = content_route('oportunidad', (string) $opportunity['slug']);
$closing = !empty($opportunity['expires_at']) ? format_date($opportunity['expires_at'], 'd M Y') : null;
?>
<article class="opp-card">
    <div class="opp-card-top">
        <span class="badge badge-accent"><?= e(opportunity_type_label($opportunity['opportunity_type'] ?? null)) ?></span>
        <span class="opp-stage"><?= e(opportunity_stage_label($opportunity['stage'] ?? null)) ?></span>
    </div>
    <h3><a href="<?= e($href) ?>"><?= e($opportunity['title']) ?></a></h3>
    <p><?= e(excerpt((string) ($opportunity['summary'] ?? ''), 110)) ?></p>
    <dl class="opp-facts">
        <div>
            <dt>Inversión</dt>
            <dd><?= e(investment_range($opportunity['investment_min'] ?? null, $opportunity['investment_max'] ?? null, (string) ($opportunity['currency'] ?? 'MXN'))) ?></dd>
        </div>
        <div>
            <dt>Ubicación</dt>
            <dd><?= e($opportunity['state_name'] ?? 'México') ?></dd>
        </div>
        <div>
            <dt>Interesados</dt>
            <dd><?= (int) ($opportunity['interests_count'] ?? 0) ?></dd>
        </div>
    </dl>
    <div class="opp-card-footer">
        <span class="case-author">
            <span class="avatar avatar-xs"><?= e(initials((string) ($opportunity['author_name'] ?? 'AN'))) ?></span>
            <span><?= e($opportunity['author_name'] ?? 'Autor') ?></span>
        </span>
        <span class="case-card-data">
            <?php if ($closing): ?><?= icon('clock', 'icon icon-xs') ?> Cierra <?= e($closing) ?><?php endif; ?>
        </span>
    </div>
</article>
