<?php
/** @var array<string, mixed>|null $opportunity */
/** @var array<string, mixed> $user */
/** @var array<int, array<string, mixed>> $sectorsList */
/** @var array<int, array<string, mixed>> $chainsList */
/** @var array<int, array<string, mixed>> $states */
$isEdit = $opportunity !== null;
$actionUrl = $isEdit ? url('/oportunidades/' . $opportunity['slug']) : url('/oportunidades');
$errors = \App\Core\Flash::errors();
$selectedSector = (string) old('sector_id', $opportunity['sector_id'] ?? '');
$selectedState = (string) old('state_code', $opportunity['state_code'] ?? ($user['state_code'] ?? ''));
?>
<section class="page-hero page-hero-form">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <a href="<?= e(url('/mi-panel')) ?>">Mi panel</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <span><?= $isEdit ? 'Editar oportunidad' : 'Nueva oportunidad' ?></span>
        </nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow"><?= $isEdit ? 'Edición' : 'Nueva publicación' ?></span>
                <h1><?= $isEdit ? 'Editar oportunidad de negocio' : 'Publica una oportunidad de agronegocios' ?></h1>
                <p>Describe el mercado, la inversión y los requisitos con honestidad. Una oportunidad bien delimitada atrae a las personas correctas.</p>
            </div>
            <div class="form-progress">
                <span class="form-progress-label">Estado</span>
                <strong><?= e($isEdit ? status_label($opportunity['status']) : 'Nuevo borrador') ?></strong>
                <span class="badge badge-<?= e($isEdit ? status_tone($opportunity['status']) : 'neutral') ?>"><?= $isEdit ? e(status_label($opportunity['status'])) : 'Sin guardar' ?></span>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container form-layout">
        <form class="case-form" method="post" action="<?= e($actionUrl) ?>">
            <?= csrf_field() ?>

            <?php if ($errors !== []): ?>
                <div class="form-error-summary" role="alert">
                    <?= icon('alert-triangle', 'icon') ?>
                    <div><strong>Revisa los siguientes campos</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
                </div>
            <?php endif; ?>

            <?php if ($user['status'] !== 'verificado'): ?>
                <div class="notice notice-warning">
                    <?= icon('shield', 'icon') ?>
                    <div><strong>Tu cuenta está pendiente de verificación</strong><p>Puedes guardar borradores. Para enviar a revisión, verifica tu cuenta en <a href="<?= e(url('/perfil')) ?>">tu perfil</a>.</p></div>
                </div>
            <?php endif; ?>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">01</span>
                    <div><h2>Ficha de la oportunidad</h2><p>Qué es, dónde es y qué tipo de participación busca.</p></div>
                </div>
                <div class="field">
                    <label for="title">Título <span class="req">*</span></label>
                    <input id="title" name="title" type="text" maxlength="180" required value="<?= e(old('title', $opportunity['title'] ?? '')) ?>" placeholder="Ej. Proveeduría de aguacate orgánico para retail en CDMX">
                    <?php if (field_error('title')): ?><span class="field-error"><?= e(field_error('title')) ?></span><?php endif; ?>
                </div>
                <div class="field">
                    <label for="summary">Resumen <span class="req">*</span></label>
                    <textarea id="summary" name="summary" rows="3" maxlength="600" required data-counter placeholder="En pocas líneas: producto o servicio, mercado, qué se busca y qué se ofrece."><?= e(old('summary', $opportunity['summary'] ?? '')) ?></textarea>
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
                        <label for="chain_id">Cadena</label>
                        <select id="chain_id" name="chain_id">
                            <option value="">No especificada</option>
                            <?php foreach ($chainsList as $chain): ?>
                                <option value="<?= (int) $chain['id'] ?>" <?= (string) old('chain_id', $opportunity['chain_id'] ?? '') === (string) $chain['id'] ? 'selected' : '' ?>><?= e($chain['emoji'] . ' ' . $chain['name']) ?></option>
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
                <div class="field-grid field-grid-3">
                    <div class="field">
                        <label for="municipality">Municipio</label>
                        <input id="municipality" name="municipality" type="text" maxlength="120" value="<?= e(old('municipality', $opportunity['municipality'] ?? '')) ?>" placeholder="Ej. Uruapan">
                    </div>
                    <div class="field">
                        <label for="opportunity_type">Tipo de oportunidad</label>
                        <select id="opportunity_type" name="opportunity_type">
                            <?php foreach (['produccion' => 'Producción', 'comercializacion' => 'Comercialización', 'agroindustria' => 'Agroindustria', 'servicios' => 'Servicios', 'exportacion' => 'Exportación', 'turismo_rural' => 'Turismo rural', 'financiamiento' => 'Financiamiento', 'tecnologia' => 'Tecnología'] as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= old('opportunity_type', $opportunity['opportunity_type'] ?? 'produccion') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="stage">Etapa</label>
                        <select id="stage" name="stage">
                            <?php foreach (['idea' => 'Idea', 'validacion' => 'En validación', 'operacion' => 'En operación', 'expansion' => 'Expansión'] as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= old('stage', $opportunity['stage'] ?? 'idea') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">02</span>
                    <div><h2>Inversión y retorno</h2><p>Da un rango realista. Si no lo sabes, déjalo vacío y explica qué falta por estimar.</p></div>
                </div>
                <div class="field-grid field-grid-4">
                    <div class="field">
                        <label for="investment_min">Inversión mínima</label>
                        <input id="investment_min" name="investment_min" type="number" min="0" step="1000" value="<?= e(old('investment_min', $opportunity['investment_min'] ?? '')) ?>" placeholder="0">
                    </div>
                    <div class="field">
                        <label for="investment_max">Inversión máxima</label>
                        <input id="investment_max" name="investment_max" type="number" min="0" step="1000" value="<?= e(old('investment_max', $opportunity['investment_max'] ?? '')) ?>" placeholder="0">
                        <?php if (field_error('investment_max')): ?><span class="field-error"><?= e(field_error('investment_max')) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="currency">Moneda</label>
                        <select id="currency" name="currency">
                            <option value="MXN" <?= old('currency', $opportunity['currency'] ?? 'MXN') === 'MXN' ? 'selected' : '' ?>>MXN</option>
                            <option value="USD" <?= old('currency', $opportunity['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="expires_at">Cierra el</label>
                        <input id="expires_at" name="expires_at" type="date" value="<?= e(old('expires_at', $opportunity['expires_at'] ?? '')) ?>">
                        <?php if (field_error('expires_at')): ?><span class="field-error"><?= e(field_error('expires_at')) ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="expected_return_pct">Retorno esperado anual (%)</label>
                        <input id="expected_return_pct" name="expected_return_pct" type="number" min="0" max="300" step="0.1" value="<?= e(old('expected_return_pct', $opportunity['expected_return_pct'] ?? '')) ?>" placeholder="Ej. 18">
                        <span class="field-help">Sólo si tienes una estimación con supuestos. No es una promesa de rendimiento.</span>
                    </div>
                    <div class="field">
                        <label for="payback_months">Recuperación estimada (meses)</label>
                        <input id="payback_months" name="payback_months" type="number" min="1" max="240" step="1" value="<?= e(old('payback_months', $opportunity['payback_months'] ?? '')) ?>" placeholder="Ej. 30">
                    </div>
                </div>
                <div class="field">
                    <label for="market_size">Tamaño y contexto del mercado</label>
                    <textarea id="market_size" name="market_size" rows="3" maxlength="2000" placeholder="Demanda, competencia, precios, canales, estacionalidad, barreras de entrada…"><?= e(old('market_size', $opportunity['market_size'] ?? '')) ?></textarea>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">03</span>
                    <div><h2>Descripción y propuesta de valor</h2><p>Explica el negocio y por qué alguien debería participar.</p></div>
                </div>
                <div class="field">
                    <label for="description">Descripción <span class="req">*</span></label>
                    <textarea id="description" name="description" rows="7" maxlength="8000" required data-counter placeholder="Producto o servicio, modelo de negocio, capacidades existentes, alianzas, ubicación y estado actual…"><?= e(old('description', $opportunity['description'] ?? '')) ?></textarea>
                    <?php if (field_error('description')): ?><span class="field-error"><?= e(field_error('description')) ?></span><?php endif; ?>
                </div>
                <div class="field">
                    <label for="value_proposition">Propuesta de valor <span class="req">*</span></label>
                    <textarea id="value_proposition" name="value_proposition" rows="4" maxlength="4000" required placeholder="Qué problema resuelve, a quién sirve y qué la hace diferente…"><?= e(old('value_proposition', $opportunity['value_proposition'] ?? '')) ?></textarea>
                    <?php if (field_error('value_proposition')): ?><span class="field-error"><?= e(field_error('value_proposition')) ?></span><?php endif; ?>
                </div>
                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="requirements">Requisitos para participar <span class="req">*</span></label>
                        <textarea id="requirements" name="requirements" rows="4" maxlength="4000" required placeholder="Capital, tierra, certificaciones, experiencia, permisos, capacidad de acopio…"><?= e(old('requirements', $opportunity['requirements'] ?? '')) ?></textarea>
                        <?php if (field_error('requirements')): ?><span class="field-error"><?= e(field_error('requirements')) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="risks">Riesgos y consideraciones</label>
                        <textarea id="risks" name="risks" rows="4" maxlength="4000" placeholder="Clima, precio, regulación, logística, competencia, dependencia de un comprador…"><?= e(old('risks', $opportunity['risks'] ?? '')) ?></textarea>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">04</span>
                    <div><h2>Contacto y publicación</h2><p>Así te contactarán las personas interesadas.</p></div>
                </div>
                <div class="field-grid field-grid-3">
                    <div class="field">
                        <label for="contact_name">Nombre de contacto <span class="req">*</span></label>
                        <input id="contact_name" name="contact_name" type="text" maxlength="120" required value="<?= e(old('contact_name', $opportunity['contact_name'] ?? $user['name'])) ?>">
                        <?php if (field_error('contact_name')): ?><span class="field-error"><?= e(field_error('contact_name')) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="contact_email">Correo de contacto <span class="req">*</span></label>
                        <input id="contact_email" name="contact_email" type="email" maxlength="180" required value="<?= e(old('contact_email', $opportunity['contact_email'] ?? $user['email'])) ?>">
                        <?php if (field_error('contact_email')): ?><span class="field-error"><?= e(field_error('contact_email')) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="contact_phone">Teléfono o WhatsApp</label>
                        <input id="contact_phone" name="contact_phone" type="text" maxlength="40" value="<?= e(old('contact_phone', $opportunity['contact_phone'] ?? '')) ?>" placeholder="Opcional">
                    </div>
                </div>
                <label class="check-line">
                    <input type="checkbox" name="is_confidential" value="1" <?= old('is_confidential', $opportunity['is_confidential'] ?? false) ? 'checked' : '' ?>>
                    <span>Mantener el contacto reservado: sólo se mostrará el formulario de interés y yo decidiré a quién responder.</span>
                </label>
                <div class="field">
                    <label for="license">Licencia de publicación</label>
                    <select id="license" name="license">
                        <?php foreach (['CC BY-NC-SA 4.0' => 'CC BY-NC-SA 4.0 · educativo con atribución', 'CC BY 4.0' => 'CC BY 4.0 · uso amplio con atribución', 'Todos los derechos reservados' => 'Todos los derechos reservados'] as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= old('license', $opportunity['license'] ?? 'CC BY-NC-SA 4.0') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </section>

            <div class="form-actions">
                <div class="form-actions-left"><a class="btn btn-ghost" href="<?= e($isEdit ? url('/oportunidades/' . $opportunity['slug']) : url('/mi-panel')) ?>">Cancelar</a></div>
                <div class="form-actions-right">
                    <button class="btn btn-outline" type="submit" name="action" value="draft">Guardar borrador</button>
                    <?php if ($user['status'] === 'verificado'): ?>
                        <button class="btn btn-primary" type="submit" name="action" value="submit">Enviar a revisión <?= icon('arrow-right', 'icon icon-sm') ?></button>
                    <?php else: ?>
                        <button class="btn btn-primary" type="button" disabled>Enviar a revisión</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <aside class="form-sidebar">
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('info', 'icon icon-sm') ?> Una oportunidad clara</h2>
                <ul class="check-list check-list-small">
                    <li>Describe un mercado concreto, no una idea genérica.</li>
                    <li>Explica qué aporta quien publica y qué necesita.</li>
                    <li>Da un rango de inversión y supuestos, no promesas.</li>
                    <li>Declara riesgos y información faltante.</li>
                </ul>
            </div>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('shield', 'icon icon-sm') ?> Aviso</h2>
                <p>El portal no verifica la solvencia de las empresas ni garantiza rendimientos. Toda decisión de inversión es responsabilidad de las partes.</p>
            </div>
        </aside>
    </div>
</section>
