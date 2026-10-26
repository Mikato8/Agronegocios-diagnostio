<?php
/** @var array<int, array<string, mixed>> $universities */
/** @var array<string, array<int, array<string, mixed>>> $grouped */
/** @var int $total */
?>
<section class="page-hero page-hero-compact">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Universidades</span>
        </nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow">Red académica</span>
                <h1>Universidades y estudiantes</h1>
                <p>Instituciones donde se estudian agronegocios en México. Consulta quiénes publican y qué están documentando.</p>
            </div>
            <div class="page-hero-aside">
                <span class="big-number"><?= number_format($total) ?></span>
                <span>instituciones registradas</span>
                <a class="btn btn-outline btn-sm" href="<?= e(url('/registro')) ?>">Sumar mi universidad</a>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <div class="university-grid">
            <?php foreach ($grouped as $stateName => $items): ?>
                <section class="university-group">
                    <h2><?= e($stateName) ?> <span class="count-badge"><?= count($items) ?></span></h2>
                    <div class="university-list">
                        <?php foreach ($items as $university): ?>
                            <a class="university-item" href="<?= e(url('/universidades/' . $university['slug'])) ?>">
                                <span class="university-mark"><?= e(mb_substr($university['acronym'] ?: $university['name'], 0, 4)) ?></span>
                                <span class="university-item-main">
                                    <strong><?= e($university['name']) ?></strong>
                                    <small><?= e($university['type'] === 'publica' ? 'Pública' : 'Privada') ?><?= $university['acronym'] !== '' ? ' · ' . e($university['acronym']) : '' ?></small>
                                </span>
                                <span class="university-count"><?= (int) $university['students_count'] ?> <?= icon('users', 'icon icon-xs') ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
</section>
