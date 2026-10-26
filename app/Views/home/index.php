<?php
/** @var array<string, int> $stats */
/** @var array<int, array<string, mixed>> $featuredCases */
/** @var array<int, array<string, mixed>> $featuredReports */
/** @var array<int, array<string, mixed>> $featuredOpportunities */
/** @var array<int, array<string, mixed>> $latestCases */
/** @var array<int, array<string, mixed>> $sectors */
/** @var array<int, array<string, mixed>> $states */
/** @var array<int, array<string, mixed>> $universities */
$heroCase = $featuredCases[0] ?? null;
$heroReport = $featuredReports[0] ?? null;
$heroOpportunity = $featuredOpportunities[0] ?? null;
?>
<section class="hero hero-agro">
    <div class="hero-pattern" aria-hidden="true"></div>
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> Comunidad estudiantil de agronegocios</span>
            <h1>El negocio del campo<br><em>se aprende documentando.</em></h1>
            <p class="hero-lead">Publica casos de empresas agropecuarias, ordena reportes financieros y comparte oportunidades reales. Un archivo abierto para estudiantes de agronegocios de México, con revisión editorial y sin cédula profesional.</p>

            <form class="hero-search" action="<?= e(url('/buscar')) ?>" method="get" role="search">
                <label class="sr-only" for="hero-q">Buscar en el portal</label>
                <div class="hero-search-field">
                    <?= icon('search', 'icon') ?>
                    <input id="hero-q" type="search" name="q" placeholder="Aguacate, lácteos, exportación, financiamiento…">
                </div>
                <label class="sr-only" for="hero-sector">Sector</label>
                <select id="hero-sector" name="sector">
                    <option value="">Todos los sectores</option>
                    <?php foreach ($sectors as $sector): ?>
                        <option value="<?= e($sector['code']) ?>"><?= e($sector['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" type="submit">Explorar <?= icon('arrow-right', 'icon icon-sm') ?></button>
            </form>

            <dl class="hero-stats">
                <div><dt><?= number_format($stats['cases'] ?? 0) ?></dt><dd>casos publicados</dd></div>
                <div><dt><?= number_format($stats['reports'] ?? 0) ?></dt><dd>reportes financieros</dd></div>
                <div><dt><?= number_format($stats['opportunities'] ?? 0) ?></dt><dd>oportunidades</dd></div>
                <div><dt><?= number_format($stats['universities'] ?? 0) ?></dt><dd>universidades</dd></div>
            </dl>
        </div>

        <div class="hero-visual">
            <div class="hero-dashboard">
                <div class="hero-dashboard-head">
                    <span><?= icon('activity', 'icon icon-sm') ?> Radar de agronegocios</span>
                    <span class="badge badge-live">Contenido abierto</span>
                </div>
                <?php if ($heroCase): ?>
                    <a class="radar-row" href="<?= e(content_route('caso', $heroCase['slug'])) ?>">
                        <span class="radar-icon radar-icon-case"><?= icon('file-text', 'icon') ?></span>
                        <span class="radar-body">
                            <small>Caso destacado · <?= e($heroCase['sector_name'] ?? '') ?></small>
                            <strong><?= e(excerpt($heroCase['title'], 62)) ?></strong>
                            <span><?= e($heroCase['state_name'] ?? 'México') ?> · <?= e(business_stage_label($heroCase['stage'] ?? null)) ?></span>
                        </span>
                        <?= icon('arrow-up-right', 'icon icon-sm') ?>
                    </a>
                <?php endif; ?>
                <?php if ($heroReport): ?>
                    <a class="radar-row" href="<?= e(content_route('reporte', $heroReport['slug'])) ?>">
                        <span class="radar-icon radar-icon-report"><?= icon('trending-up', 'icon') ?></span>
                        <span class="radar-body">
                            <small>Reporte financiero · <?= e($heroReport['sector_name'] ?? '') ?></small>
                            <strong><?= e(excerpt($heroReport['title'], 62)) ?></strong>
                            <span>Ingresos <?= e(compact_money($heroReport['revenue'] ?? null)) ?> · Margen neto <?= e(pct(($heroReport['net_income'] ?? null) !== null && ($heroReport['revenue'] ?? 0) > 0 ? ((float) $heroReport['net_income'] / (float) $heroReport['revenue']) * 100 : null)) ?></span>
                        </span>
                        <?= icon('arrow-up-right', 'icon icon-sm') ?>
                    </a>
                <?php endif; ?>
                <?php if ($heroOpportunity): ?>
                    <a class="radar-row" href="<?= e(content_route('oportunidad', $heroOpportunity['slug'])) ?>">
                        <span class="radar-icon radar-icon-opp"><?= icon('sparkles', 'icon') ?></span>
                        <span class="radar-body">
                            <small>Oportunidad · <?= e(opportunity_type_label($heroOpportunity['opportunity_type'] ?? null)) ?></small>
                            <strong><?= e(excerpt($heroOpportunity['title'], 62)) ?></strong>
                            <span><?= e($heroOpportunity['state_name'] ?? 'México') ?> · <?= e(investment_range($heroOpportunity['investment_min'] ?? null, $heroOpportunity['investment_max'] ?? null)) ?></span>
                        </span>
                        <?= icon('arrow-up-right', 'icon icon-sm') ?>
                    </a>
                <?php endif; ?>
                <div class="hero-dashboard-foot">
                    <?= icon('shield', 'icon icon-xs') ?>
                    <span>Verificamos con correo institucional o constancia de estudios. <strong>Nunca pedimos cédula profesional.</strong></span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight section-pillars">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Tres formas de aportar</span>
                <h2>Todo lo que necesitas para aprender haciendo</h2>
              </div>
            <a class="text-link" href="<?= e(url('/guias')) ?>">Ver guías de documentación <?= icon('arrow-right', 'icon icon-sm') ?></a>
        </div>
        <div class="pillar-grid">
            <article class="pillar-card">
                <span class="pillar-icon"><?= icon('folder', 'icon') ?></span>
                <span class="badge badge-soft"><?= number_format($stats['cases'] ?? 0) ?> publicados</span>
                <h3>Casos de agronegocios</h3>
                <p>Contexto, reto, diagnóstico, estrategia y resultados de una empresa o proyecto del campo. Aprende de decisiones reales.</p>
                <a class="text-link" href="<?= e(url('/casos')) ?>">Explorar casos <?= icon('arrow-right', 'icon icon-sm') ?></a>
            </article>
            <article class="pillar-card">
                <span class="pillar-icon"><?= icon('trending-up', 'icon') ?></span>
                <span class="badge badge-soft"><?= number_format($stats['reports'] ?? 0) ?> publicados</span>
                <h3>Reportes financieros</h3>
                <p>Estado de resultados, balance y análisis. El portal calcula márgenes, liquidez, endeudamiento, ROE y ROA por ti.</p>
                <a class="text-link" href="<?= e(url('/reportes')) ?>">Ver reportes <?= icon('arrow-right', 'icon icon-sm') ?></a>
            </article>
            <article class="pillar-card">
                <span class="pillar-icon"><?= icon('sparkles', 'icon') ?></span>
                <span class="badge badge-soft"><?= number_format($stats['opportunities'] ?? 0) ?> activas</span>
                <h3>Oportunidades de negocio</h3>
                <p>Producción, comercialización, agroindustria o exportación. Describe el mercado, la inversión y los riesgos.</p>
                <a class="text-link" href="<?= e(url('/oportunidades')) ?>">Ver oportunidades <?= icon('arrow-right', 'icon icon-sm') ?></a>
            </article>
        </div>
    </div>
</section>

<section class="section section-cases">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Selección editorial</span>
                <h2>Casos que vale la pena leer</h2>
                <p class="section-sub">Empresas reales, decisiones concretas y resultados que se pueden discutir en clase.</p>
            </div>
            <a class="text-link" href="<?= e(url('/casos')) ?>">Ver todos los casos <?= icon('arrow-right', 'icon icon-sm') ?></a>
        </div>
        <?php if ($featuredCases === []): ?>
            <div class="empty-state">
                <span class="empty-icon"><?= icon('folder', 'icon') ?></span>
                <h2>El archivo está comenzando</h2>
                <p>Publica el primer caso y ayuda a construir esta biblioteca.</p>
                <a class="btn btn-primary" href="<?= e(url('/casos/nuevo')) ?>">Compartir un caso</a>
            </div>
        <?php else: ?>
            <div class="case-grid">
                <?php foreach ($featuredCases as $case): ?>
                    <?php \App\Core\View::partial('partials/case-card', ['case' => $case]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-finance">
    <div class="container finance-band">
        <div class="finance-copy">
            <span class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> Educación financiera aplicada</span>
            <h2>De las cifras a la decisión.</h2>
            <p>Captura ingresos, costos, utilidad, activos y pasivos. AgroNegocios MX calcula los indicadores y te obliga a explicar qué significan para la empresa.</p>
            <ul class="check-list check-list-light">
                <li><?= icon('check', 'icon icon-sm') ?> Márgenes bruto, operativo y neto</li>
                <li><?= icon('check', 'icon icon-sm') ?> Liquidez, endeudamiento, ROE y ROA</li>
                <li><?= icon('check', 'icon icon-sm') ?> Semáforo financiero con criterios explícitos</li>
                <li><?= icon('check', 'icon icon-sm') ?> Exportación del reporte a PDF</li>
            </ul>
            <a class="btn btn-sand" href="<?= e(url('/reportes/nuevo')) ?>">Subir un reporte <?= icon('arrow-right', 'icon icon-sm') ?></a>
        </div>
        <div class="finance-preview">
            <div class="finance-preview-head">
                <span><?= icon('trending-up', 'icon icon-sm') ?> Reporte destacado</span>
                <span class="badge badge-live">Calculado</span>
            </div>
            <?php if ($heroReport): ?>
                <h3><a href="<?= e(content_route('reporte', $heroReport['slug'])) ?>"><?= e($heroReport['title']) ?></a></h3>
                <p><?= e(excerpt($heroReport['summary'], 100)) ?></p>
                <?php
                $rev = (float) ($heroReport['revenue'] ?? 0);
                $net = (float) ($heroReport['net_income'] ?? 0);
                $margin = $rev > 0 ? ($net / $rev) * 100 : null;
                ?>
                <div class="finance-bars">
                    <div><span>Ingresos</span><strong><?= e(compact_money($heroReport['revenue'] ?? null)) ?></strong></div>
                    <div><span>Utilidad neta</span><strong><?= e(compact_money($heroReport['net_income'] ?? null)) ?></strong></div>
                    <div><span>Margen neto</span><strong><?= e($margin === null ? '—' : pct($margin)) ?></strong></div>
                </div>
                <div class="finance-meter">
                    <span style="width: <?= e((string) max(2, min(100, abs((float) ($margin ?? 0))))) ?>%"></span>
                </div>
                <a class="text-link text-link-light" href="<?= e(content_route('reporte', $heroReport['slug'])) ?>">Leer análisis completo <?= icon('arrow-right', 'icon icon-sm') ?></a>
            <?php else: ?>
                <p>Aún no hay reportes publicados. Sé la primera persona en compartir uno.</p>
                <a class="btn btn-sand" href="<?= e(url('/reportes/nuevo')) ?>">Crear reporte</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section section-opps">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Bolsa de agronegocios</span>
                <h2>Oportunidades para conectar</h2>
                <p class="section-sub">Proyectos que buscan socios, proveedores, compradores o talento. Expresa tu interés y conversa con quien la publica.</p>
            </div>
            <a class="text-link" href="<?= e(url('/oportunidades')) ?>">Ver todas <?= icon('arrow-right', 'icon icon-sm') ?></a>
        </div>
        <?php if ($featuredOpportunities === []): ?>
            <div class="empty-state">
                <span class="empty-icon"><?= icon('sparkles', 'icon') ?></span>
                <h2>La bolsa está abierta</h2>
                <p>Publica una oportunidad con mercado, inversión y requisitos claros.</p>
                <a class="btn btn-primary" href="<?= e(url('/oportunidades/nueva')) ?>">Publicar oportunidad</a>
            </div>
        <?php else: ?>
            <div class="opp-grid">
                <?php foreach ($featuredOpportunities as $opportunity): ?>
                    <?php \App\Core\View::partial('partials/opp-card', ['opportunity' => $opportunity]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-how">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Cómo funciona</span>
                <h2>Publicar es un ejercicio académico, no un trámite</h2>
            </div>
        </div>
        <ol class="steps-grid">
            <li>
                <span class="step-number">01</span>
                <h3>Crea tu cuenta</h3>
                <p>Regístrate con tu correo institucional. Si usas un correo personal, sube tu constancia de estudios y un editor revisará tu cuenta.</p>
            </li>
            <li>
                <span class="step-number">02</span>
                <h3>Documenta con método</h3>
                <p>Usa las guías del portal: contexto, reto, análisis, alternativa elegida, resultados y aprendizajes. Anonimiza datos sensibles.</p>
            </li>
            <li>
                <span class="step-number">03</span>
                <h3>Recibe revisión</h3>
                <p>El comité editorial revisa claridad, coherencia y ética. Puede publicar, pedir cambios o rechazar con retroalimentación.</p>
            </li>
            <li>
                <span class="step-number">04</span>
                <h3>Conversa y comparte</h3>
                <p>Recibe comentarios de otros estudiantes, guarda favoritos y descarga tu caso o reporte en PDF con tu crédito y licencia.</p>
            </li>
        </ol>
    </div>
</section>

<section class="section section-universities">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Comunidad</span>
                <h2>Estudiantes de todo el país</h2>
                <p class="section-sub">Más de 45 instituciones registradas. Consulta el directorio y encuentra colegas por estado.</p>
            </div>
            <a class="text-link" href="<?= e(url('/universidades')) ?>">Ver universidades <?= icon('arrow-right', 'icon icon-sm') ?></a>
        </div>
        <div class="university-strip">
            <?php foreach ($universities as $university): ?>
                <a class="university-chip" href="<?= e(url('/universidades/' . $university['slug'])) ?>">
                    <span class="university-chip-mark"><?= e(mb_substr($university['acronym'] ?: $university['name'], 0, 4)) ?></span>
                    <span>
                        <strong><?= e($university['acronym'] ?: $university['name']) ?></strong>
                        <small><?= e($university['state_name'] ?? 'México') ?> · <?= (int) $university['total'] ?> estudiantes</small>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-cta">
    <div class="container cta-band">
        <div>
            <span class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> Tu experiencia importa</span>
            <h2>Un caso bien documentado<br>puede cambiar una decisión.</h2>
            <p>Comparte lo que aprendiste en una práctica, una visita o un proyecto. La comunidad y el comité editorial están para ayudarte a escribirlo mejor.</p>
        </div>
        <div class="cta-actions">
            <a class="btn btn-sand" href="<?= e(url('/registro')) ?>">Crear mi cuenta <?= icon('arrow-right', 'icon icon-sm') ?></a>
            <a class="btn btn-ghost-light" href="<?= e(url('/guias')) ?>">Leer las guías</a>
        </div>
        <ol class="cta-steps">
            <li><span>01</span> Documenta el caso con la guía del portal.</li>
            <li><span>02</span> Anonimiza y revisa los datos sensibles.</li>
            <li><span>03</span> Envía a revisión editorial.</li>
        </ol>
    </div>
</section>
