<?php
/** @var array<string, mixed>|null $case */
/** @var array<string, mixed> $user */
/** @var array<int, array<string, mixed>> $sectorsList */
/** @var array<int, array<string, mixed>> $chainsList */
/** @var array<int, array<string, mixed>> $states */
$isEdit = $case !== null;
$actionUrl = $isEdit ? url('/casos/' . $case['slug']) : url('/casos');
$errors = \App\Core\Flash::errors();
$selectedSector = (string) old('sector_id', $case['sector_id'] ?? '');
$selectedChain = (string) old('chain_id', $case['chain_id'] ?? '');
$selectedState = (string) old('state_code', $case['state_code'] ?? ($user['state_code'] ?? ''));
?>
<section class="page-hero page-hero-form">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <a href="<?= e(url('/mi-panel')) ?>">Mi panel</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <span><?= $isEdit ? 'Editar caso' : 'Nuevo caso' ?></span>
        </nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow"><?= $isEdit ? 'Edición' : 'Nueva aportación' ?></span>
                <h1><?= $isEdit ? 'Editar caso de agronegocios' : 'Documenta un caso de agronegocios' ?></h1>
                <p>Escribe para alguien que no estuvo ahí: contexto, decisión, evidencia y aprendizaje. Anonimiza nombres de productores o empresas cuando sea necesario.</p>
            </div>
            <div class="form-progress">
                <span class="form-progress-label">Estado</span>
                <strong><?= e($isEdit ? status_label($case['status']) : 'Nuevo borrador') ?></strong>
                <span class="badge badge-<?= e($isEdit ? status_tone($case['status']) : 'neutral') ?>"><?= $isEdit ? e(status_label($case['status'])) : 'Sin guardar' ?></span>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container form-layout">
        <form class="case-form" method="post" action="<?= e($actionUrl) ?>" enctype="multipart/form-data" data-case-form>
            <?= csrf_field() ?>

            <?php if ($errors !== []): ?>
                <div class="form-error-summary" role="alert">
                    <?= icon('alert-triangle', 'icon') ?>
                    <div>
                        <strong>Revisa los siguientes campos</strong>
                        <ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($user['status'] !== 'verificado'): ?>
                <div class="notice notice-warning">
                    <?= icon('shield', 'icon') ?>
                    <div>
                        <strong>Tu cuenta está pendiente de verificación</strong>
                        <p>Puedes guardar borradores, pero para enviar a revisión necesitas verificar tu correo institucional o subir una constancia de estudios en <a href="<?= e(url('/perfil')) ?>">tu perfil</a>.</p>
                    </div>
                </div>
            <?php endif; ?>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">01</span>
                    <div>
                        <h2>Ficha del caso</h2>
                        <p>Un título concreto y un resumen que permita decidir si el caso es relevante.</p>
                    </div>
                </div>
                <div class="field">
                    <label for="title">Título <span class="req">*</span></label>
                    <input id="title" name="title" type="text" maxlength="180" required value="<?= e(old('title', $case['title'] ?? '')) ?>" placeholder="Ej. Reconvertir 12 ha de maíz a maíz azul para nixtamaleras de Tlaxcala">
                    <div class="field-foot"><span>Entre 10 y 180 caracteres. Incluye sujeto, decisión y contexto.</span><span data-counter-for="title"></span></div>
                    <?php if (field_error('title')): ?><span class="field-error"><?= e(field_error('title')) ?></span><?php endif; ?>
                </div>
                <div class="field">
                    <label for="summary">Resumen ejecutivo <span class="req">*</span></label>
                    <textarea id="summary" name="summary" rows="4" maxlength="600" required data-counter placeholder="En 3 o 4 líneas: quién es la empresa, cuál era el reto, qué se hizo y qué resultado se obtuvo."><?= e(old('summary', $case['summary'] ?? '')) ?></textarea>
                    <div class="field-foot"><span>Mínimo 30 caracteres.</span><span data-counter-for="summary"></span></div>
                    <?php if (field_error('summary')): ?><span class="field-error"><?= e(field_error('summary')) ?></span><?php endif; ?>
                </div>
                <div class="field-grid field-grid-3">
                    <div class="field">
                        <label for="sector_id">Sector <span class="req">*</span></label>
                        <select id="sector_id" name="sector_id" required>
                            <option value="">Selecciona…</option>
                            <?php foreach ($sectorsList as $sector): ?>
                                <option value="<?= (int) $sector['id'] ?>" <?= $selectedSector === (string) $sector['id'] ? 'selected' : '' ?>><?= e($sector['emoji'] . ' ' . $sector['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (field_error('sector_id')): ?><span class="field-error"><?= e(field_error('sector_id')) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="chain_id">Cadena productiva</label>
                        <select id="chain_id" name="chain_id">
                            <option value="">No especificada</option>
                            <?php foreach ($chainsList as $chain): ?>
                                <option value="<?= (int) $chain['id'] ?>" <?= $selectedChain === (string) $chain['id'] ? 'selected' : '' ?>><?= e($chain['emoji'] . ' ' . $chain['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="year">Año del caso <span class="req">*</span></label>
                        <input id="year" name="year" type="number" min="2000" max="2100" step="1" required value="<?= e(old('year', $case['year'] ?? date('Y'))) ?>">
                        <?php if (field_error('year')): ?><span class="field-error"><?= e(field_error('year')) ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="field-grid field-grid-3">
                    <div class="field">
                        <label for="company_type">Tipo de organización</label>
                        <select id="company_type" name="company_type">
                            <?php foreach (['micro' => 'Microempresa', 'pequena' => 'Pequeña empresa', 'mediana' => 'Mediana empresa', 'grande' => 'Empresa grande', 'cooperativa' => 'Cooperativa', 'ejido' => 'Ejido o comunidad', 'asociacion' => 'Asociación de productores'] as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= old('company_type', $case['company_type'] ?? 'pequena') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="stage">Etapa del negocio</label>
                        <select id="stage" name="stage">
                            <?php foreach (['idea' => 'Idea o proyecto', 'arranque' => 'Arranque', 'operacion' => 'En operación', 'crecimiento' => 'Crecimiento', 'consolidacion' => 'Consolidación', 'crisis' => 'Crisis o reestructura'] as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= old('stage', $case['stage'] ?? 'operacion') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="state_code">Estado <span class="req">*</span></label>
                        <select id="state_code" name="state_code" required>
                            <option value="">Selecciona…</option>
                            <?php foreach ($states as $state): ?>
                                <option value="<?= e($state['code']) ?>" <?= $selectedState === $state['code'] ? 'selected' : '' ?>><?= e($state['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (field_error('state_code')): ?><span class="field-error"><?= e(field_error('state_code')) ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="municipality">Municipio</label>
                        <input id="municipality" name="municipality" type="text" maxlength="120" value="<?= e(old('municipality', $case['municipality'] ?? '')) ?>" placeholder="Ej. Huamantla">
                    </div>
                    <div class="field">
                        <label for="license">Licencia de publicación</label>
                        <select id="license" name="license">
                            <?php foreach (['CC BY-NC-SA 4.0' => 'CC BY-NC-SA 4.0 · educativo con atribución', 'CC BY 4.0' => 'CC BY 4.0 · uso amplio con atribución', 'CC BY-ND 4.0' => 'CC BY-ND 4.0 · sin obras derivadas', 'Todos los derechos reservados' => 'Todos los derechos reservados'] as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= old('license', $case['license'] ?? 'CC BY-NC-SA 4.0') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">02</span>
                    <div>
                        <h2>Contexto y reto</h2>
                        <p>Describe la empresa, el entorno y la decisión que había que tomar.</p>
                    </div>
                </div>
                <div class="field">
                    <label for="context">Contexto de la empresa o proyecto <span class="req">*</span></label>
                    <textarea id="context" name="context" rows="7" maxlength="8000" required data-counter placeholder="Historia, tamaño, ubicación, productos, clientes, equipo, financiamiento, cadena de valor y entorno competitivo…"><?= e(old('context', $case['context'] ?? '')) ?></textarea>
                    <div class="field-foot"><span>Mínimo 40 caracteres. Evita datos que identifiquen a personas sin consentimiento.</span><span data-counter-for="context"></span></div>
                    <?php if (field_error('context')): ?><span class="field-error"><?= e(field_error('context')) ?></span><?php endif; ?>
                </div>
                <div class="field">
                    <label for="challenge">El reto o la decisión <span class="req">*</span></label>
                    <textarea id="challenge" name="challenge" rows="5" maxlength="6000" required data-counter placeholder="¿Qué había que resolver? ¿Qué alternativas existían? ¿Qué restricciones había (dinero, tiempo, clima, mercado, regulación)…"><?= e(old('challenge', $case['challenge'] ?? '')) ?></textarea>
                    <div class="field-foot"><span>Mínimo 40 caracteres.</span><span data-counter-for="challenge"></span></div>
                    <?php if (field_error('challenge')): ?><span class="field-error"><?= e(field_error('challenge')) ?></span><?php endif; ?>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">03</span>
                    <div>
                        <h2>Análisis y estrategia</h2>
                        <p>Explica el razonamiento, los datos que usaste y la alternativa elegida.</p>
                    </div>
                </div>
                <div class="field">
                    <label for="diagnosis">Diagnóstico y análisis <span class="req">*</span></label>
                    <textarea id="diagnosis" name="diagnosis" rows="7" maxlength="8000" required data-counter placeholder="Análisis de costos, mercado, capacidad, flujo de efectivo, riesgos, cadena de valor, indicadores y hallazgos…"><?= e(old('diagnosis', $case['diagnosis'] ?? '')) ?></textarea>
                    <div class="field-foot"><span>Mínimo 40 caracteres. Distingue hechos de supuestos.</span><span data-counter-for="diagnosis"></span></div>
                    <?php if (field_error('diagnosis')): ?><span class="field-error"><?= e(field_error('diagnosis')) ?></span><?php endif; ?>
                </div>
                <div class="field">
                    <label for="strategy">Estrategia implementada <span class="req">*</span></label>
                    <textarea id="strategy" name="strategy" rows="7" maxlength="8000" required data-counter placeholder="¿Qué se decidió hacer? ¿Con qué recursos, aliados y tiempos? ¿Qué se descartó y por qué…"><?= e(old('strategy', $case['strategy'] ?? '')) ?></textarea>
                    <div class="field-foot"><span>Mínimo 40 caracteres.</span><span data-counter-for="strategy"></span></div>
                    <?php if (field_error('strategy')): ?><span class="field-error"><?= e(field_error('strategy')) ?></span><?php endif; ?>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">04</span>
                    <div>
                        <h2>Resultados y aprendizajes</h2>
                        <p>Muestra la evidencia, incluyendo lo que no salió como se esperaba.</p>
                    </div>
                </div>
                <div class="field">
                    <label for="results">Resultados <span class="req">*</span></label>
                    <textarea id="results" name="results" rows="6" maxlength="6000" required data-counter placeholder="Cifras antes y después, ventas, costos, rendimiento, satisfacción, empleo, adopción, aprendizajes del equipo…"><?= e(old('results', $case['results'] ?? '')) ?></textarea>
                    <div class="field-foot"><span>Mínimo 30 caracteres. Si no hay cifras, explica por qué.</span><span data-counter-for="results"></span></div>
                    <?php if (field_error('results')): ?><span class="field-error"><?= e(field_error('results')) ?></span><?php endif; ?>
                </div>
                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="financial_highlights">Cifras financieras relevantes</label>
                        <textarea id="financial_highlights" name="financial_highlights" rows="5" maxlength="4000" placeholder="Ingresos, costos, inversión, punto de equilibrio, financiamiento…"><?= e(old('financial_highlights', $case['financial_highlights'] ?? '')) ?></textarea>
                    </div>
                    <div class="field">
                        <label for="kpis">Indicadores clave (KPI)</label>
                        <textarea id="kpis" name="kpis" rows="5" maxlength="3000" placeholder="Un indicador por línea: nombre, valor y periodo."><?= e(old('kpis', $case['kpis'] ?? '')) ?></textarea>
                    </div>
                </div>
                <div class="field">
                    <label for="lessons">Aprendizajes <span class="req">*</span></label>
                    <textarea id="lessons" name="lessons" rows="6" maxlength="6000" required data-counter placeholder="¿Qué harías distinto? ¿Qué se puede transferir a otra empresa o territorio? ¿Qué quedó pendiente…"><?= e(old('lessons', $case['lessons'] ?? '')) ?></textarea>
                    <div class="field-foot"><span>Mínimo 30 caracteres.</span><span data-counter-for="lessons"></span></div>
                    <?php if (field_error('lessons')): ?><span class="field-error"><?= e(field_error('lessons')) ?></span><?php endif; ?>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">05</span>
                    <div>
                        <h2>Evidencia y referencias</h2>
                        <p>Adjunta imágenes del proyecto y las fuentes que sustentan el análisis.</p>
                    </div>
                </div>
                <div class="field">
                    <label for="references_text">Referencias</label>
                    <textarea id="references_text" name="references_text" rows="4" maxlength="4000" placeholder="Bibliografía, entrevistas, datos de la empresa, informes públicos, normativa…"><?= e(old('references_text', $case['references_text'] ?? '')) ?></textarea>
                </div>
                <div class="field">
                    <label for="images">Imágenes del caso <span class="optional">(hasta 4)</span></label>
                    <input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple data-image-input>
                    <span class="field-help">JPG, PNG o WebP · máximo 8 MB por imagen. No subas documentos con datos personales visibles.</span>
                    <div class="image-previews" data-image-previews></div>
                </div>
                <?php if ($isEdit && !empty($case['images'])): ?>
                    <div class="current-images">
                        <?php foreach ($case['images'] as $image): ?>
                            <figure>
                                <img src="<?= e(url('/uploads/casos/' . $image['path'])) ?>" alt="<?= e($image['caption'] ?? '') ?>" loading="lazy">
                                <figcaption>
                                    <span><?= e(excerpt((string) ($image['caption'] ?? ''), 60)) ?></span>
                                    <form method="post" action="<?= e(url('/casos/' . $case['slug'] . '/imagenes/' . $image['id'] . '/eliminar')) ?>">
                                        <?= csrf_field() ?>
                                        <button class="text-link text-link-danger" type="submit">Eliminar</button>
                                    </form>
                                </figcaption>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <div class="form-actions">
                <div class="form-actions-left">
                    <a class="btn btn-ghost" href="<?= e($isEdit ? url('/casos/' . $case['slug']) : url('/mi-panel')) ?>">Cancelar</a>
                </div>
                <div class="form-actions-right">
                    <button class="btn btn-outline" type="submit" name="action" value="draft">Guardar borrador</button>
                    <?php if ($user['status'] === 'verificado'): ?>
                        <button class="btn btn-primary" type="submit" name="action" value="submit">Enviar a revisión <?= icon('arrow-right', 'icon icon-sm') ?></button>
                    <?php else: ?>
                        <button class="btn btn-primary" type="button" disabled title="Verifica tu cuenta para enviar">Enviar a revisión</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <aside class="form-sidebar">
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('info', 'icon icon-sm') ?> Antes de publicar</h2>
                <ul class="check-list check-list-small">
                    <li><?= icon('check', 'icon icon-xs') ?> La empresa o el productor autorizó el uso del caso.</li>
                    <li><?= icon('check', 'icon icon-xs') ?> No hay nombres, teléfonos ni direcciones sin consentimiento.</li>
                    <li><?= icon('check', 'icon icon-xs') ?> Las cifras tienen fuente o se declaran como estimación.</li>
                    <li><?= icon('check', 'icon icon-xs') ?> El texto distingue hechos, hipótesis y opiniones.</li>
                </ul>
                <a class="text-link" href="<?= e(url('/etica')) ?>">Leer criterios editoriales <?= icon('arrow-right', 'icon icon-xs') ?></a>
            </div>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('book-open', 'icon icon-sm') ?> Guía rápida</h2>
                <p>Un caso útil responde: ¿cuál era el problema?, ¿qué alternativas había?, ¿por qué se eligió esa?, ¿qué evidencia hay del resultado?</p>
                <a class="btn btn-outline btn-block btn-sm" href="<?= e(url('/guias')) ?>">Abrir guías</a>
            </div>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('shield', 'icon icon-sm') ?> Privacidad</h2>
                <p>Tu correo y tu matrícula no se muestran públicamente. Consulta el <a href="<?= e(url('/privacidad')) ?>">aviso de privacidad</a>.</p>
            </div>
        </aside>
    </div>
</section>
