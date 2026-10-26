<?php /** @var array<string, mixed> $payload */ ?>
<section class="page-hero page-hero-compact">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan"><a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Estado del servicio</span></nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow">Infraestructura</span>
                <h1>Estado del servicio</h1>
                <p>Comprobaciones técnicas del portal y de la conexión con PostgreSQL en Neon.</p>
            </div>
            <div class="page-hero-aside">
                <span class="badge badge-<?= $payload['ok'] ? 'success' : 'danger' ?>"><?= $payload['ok'] ? 'Operativo' : 'Revisar' ?></span>
                <span class="muted"><?= e($payload['time']) ?></span>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <div class="health-technical-grid">
            <?php foreach ($payload['checks'] as $name => $value): ?>
                <div class="health-technical <?= $value === true || (is_string($value) && $value !== '') ? 'is-ok' : 'is-bad' ?>">
                    <span><?= e($name) ?></span>
                    <strong><?= $value === true ? 'OK' : ($value === false ? 'Falla' : e((string) $value)) ?></strong>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($payload['counts'])): ?>
            <div class="admin-card">
                <div class="admin-card-head"><h2 class="admin-card-title">Contenido publicado</h2></div>
                <div class="admin-stat-grid">
                    <?php foreach ($payload['counts'] as $label => $count): ?>
                        <div class="admin-stat"><div class="admin-stat-info"><span><?= e(ucfirst($label)) ?></span><strong><?= number_format((int) $count) ?></strong></div></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <div class="admin-card-head"><h2 class="admin-card-title">Información técnica</h2></div>
            <dl class="plot-data">
                <div><dt>Aplicación</dt><dd><?= e($payload['app']) ?></dd></div>
                <div><dt>Entorno</dt><dd><?= e($payload['environment']) ?></dd></div>
                <div><dt>Controlador de datos</dt><dd><?= e($payload['driver'] ?? 'No disponible') ?></dd></div>
                <div><dt>PHP</dt><dd><?= e($payload['checks']['php']) ?></dd></div>
            </dl>
        </div>
    </div>
</section>
