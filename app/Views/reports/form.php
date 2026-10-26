<?php
/** @var array<string, mixed>|null $report */
/** @var array<string, mixed> $user */
/** @var array<int, array<string, mixed>> $sectorsList */
/** @var array<int, array<string, mixed>> $chainsList */
/** @var array<int, array<string, mixed>> $states */
$isEdit = $report !== null;
$actionUrl = $isEdit ? url('/reportes/' . $report['slug']) : url('/reportes');
$errors = \App\Core\Flash::errors();
$selectedSector = (string) old('sector_id', $report['sector_id'] ?? '');
$selectedChain = (string) old('chain_id', $report['chain_id'] ?? '');
$selectedState = (string) old('state_code', $report['state_code'] ?? ($user['state_code'] ?? ''));
$fieldValue = static fn (string $field): string => (string) old($field, $report[$field] ?? '');
?>
<section class="page-hero page-hero-form">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <a href="<?= e(url('/mi-panel')) ?>">Mi panel</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <span><?= $isEdit ? 'Editar reporte' : 'Nuevo reporte' ?></span>
        </nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow"><?= $isEdit ? 'Edición' : 'Nueva aportación' ?></span>
                <h1><?= $isEdit ? 'Editar reporte financiero' : 'Documenta un reporte financiero' ?></h1>
                <p>Captura las cifras principales y explica qué significan. El portal calcula los indicadores; tú aportas el análisis y las decisiones.</p>
            </div>
            <div class="form-progress">
                <span class="form-progress-label">Estado</span>
                <strong><?= e($isEdit ? status_label($report['status']) : 'Nuevo borrador') ?></strong>
                <span class="badge badge-<?= e($isEdit ? status_tone($report['status']) : 'neutral') ?>"><?= $isEdit ? e(status_label($report['status'])) : 'Sin guardar' ?></span>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container form-layout">
        <form class="case-form" method="post" action="<?= e($actionUrl) ?>" enctype="multipart/form-data" data-finance-form>
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
                        <p>Puedes guardar borradores. Para enviar a revisión, verifica tu correo institucional o sube una constancia en <a href="<?= e(url('/perfil')) ?>">tu perfil</a>.</p>
                    </div>
                </div>
            <?php endif; ?>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">01</span>
                    <div>
                        <h2>Ficha del reporte</h2>
                        <p>Identifica la empresa, el periodo y la unidad monetaria.</p>
                    </div>
                </div>
                <div class="field">
                    <label for="title">Título <span class="req">*</span></label>
                    <input id="title" name="title" type="text" maxlength="180" required value="<?= e(old('title', $report['title'] ?? '')) ?>" placeholder="Ej. Estado de resultados 2025 de una empacadora de limón persa">
                    <div class="field-foot"><span data-counter-for="title"></span></div>
                    <?php if (field_error('title')): ?><span class="field-error"><?= e(field_error('title')) ?></span><?php endif; ?>
                </div>
                <div class="field">
                    <label for="summary">Resumen <span class="req">*</span></label>
                    <textarea id="summary" name="summary" rows="3" maxlength="600" required data-counter placeholder="Qué empresa es, qué periodo cubre, cuál es el hallazgo financiero principal y qué recomienda el autor."><?= e(old('summary', $report['summary'] ?? '')) ?></textarea>
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
                <div class="field-grid field-grid-4">
                    <div class="field">
                        <label for="company_type">Tipo de organización</label>
                        <select id="company_type" name="company_type">
                            <?php foreach (['micro' => 'Microempresa', 'pequena' => 'Pequeña', 'mediana' => 'Mediana', 'grande' => 'Grande', 'cooperativa' => 'Cooperativa', 'ejido' => 'Ejido', 'asociacion' => 'Asociación'] as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= old('company_type', $report['company_type'] ?? 'pequena') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="period_start">Inicio del periodo <span class="req">*</span></label>
                        <input id="period_start" name="period_start" type="date" required value="<?= e(old('period_start', $report['period_start'] ?? '')) ?>">
                        <?php if (field_error('period_start')): ?><span class="field-error"><?= e(field_error('period_start')) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="period_end">Fin del periodo <span class="req">*</span></label>
                        <input id="period_end" name="period_end" type="date" required value="<?= e(old('period_end', $report['period_end'] ?? '')) ?>">
                        <?php if (field_error('period_end')): ?><span class="field-error"><?= e(field_error('period_end')) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="currency">Moneda</label>
                        <select id="currency" name="currency">
                            <option value="MXN" <?= old('currency', $report['currency'] ?? 'MXN') === 'MXN' ? 'selected' : '' ?>>MXN · peso</option>
                            <option value="USD" <?= old('currency', $report['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD · dólar</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">02</span>
                    <div>
                        <h2>Estado de resultados</h2>
                        <p>Usa cifras del periodo, sin comas. Si un dato no existe, déjalo vacío.</p>
                    </div>
                </div>
                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="revenue">Ingresos totales <span class="req">*</span></label>
                        <input id="revenue" name="revenue" type="number" step="0.01" min="0" required value="<?= e($fieldValue('revenue')) ?>" placeholder="0.00" data-fin-field="revenue">
                        <?php if (field_error('revenue')): ?><span class="field-error"><?= e(field_error('revenue')) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="cogs">Costo de ventas</label>
                        <input id="cogs" name="cogs" type="number" step="0.01" min="0" value="<?= e($fieldValue('cogs')) ?>" placeholder="0.00" data-fin-field="cogs">
                    </div>
                    <div class="field">
                        <label for="opex">Gastos de operación</label>
                        <input id="opex" name="opex" type="number" step="0.01" min="0" value="<?= e($fieldValue('opex')) ?>" placeholder="0.00" data-fin-field="opex">
                    </div>
                    <div class="field">
                        <label for="other_income">Otros ingresos</label>
                        <input id="other_income" name="other_income" type="number" step="0.01" value="<?= e($fieldValue('other_income')) ?>" placeholder="0.00" data-fin-field="other_income">
                    </div>
                    <div class="field">
                        <label for="net_income">Utilidad neta <span class="req">*</span></label>
                        <input id="net_income" name="net_income" type="number" step="0.01" required value="<?= e($fieldValue('net_income')) ?>" placeholder="0.00" data-fin-field="net_income">
                        <span class="field-help">Puede ser negativa si hubo pérdida.</span>
                        <?php if (field_error('net_income')): ?><span class="field-error"><?= e(field_error('net_income')) ?></span><?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">03</span>
                    <div>
                        <h2>Balance general</h2>
                        <p>Activos, pasivos y capital al cierre del periodo.</p>
                    </div>
                </div>
                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="total_assets">Activo total <span class="req">*</span></label>
                        <input id="total_assets" name="total_assets" type="number" step="0.01" min="0" required value="<?= e($fieldValue('total_assets')) ?>" placeholder="0.00" data-fin-field="total_assets">
                        <?php if (field_error('total_assets')): ?><span class="field-error"><?= e(field_error('total_assets')) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="current_assets">Activo circulante</label>
                        <input id="current_assets" name="current_assets" type="number" step="0.01" min="0" value="<?= e($fieldValue('current_assets')) ?>" placeholder="0.00" data-fin-field="current_assets">
                    </div>
                    <div class="field">
                        <label for="cash">Efectivo y equivalentes</label>
                        <input id="cash" name="cash" type="number" step="0.01" min="0" value="<?= e($fieldValue('cash')) ?>" placeholder="0.00" data-fin-field="cash">
                    </div>
                    <div class="field">
                        <label for="receivables">Cuentas por cobrar</label>
                        <input id="receivables" name="receivables" type="number" step="0.01" min="0" value="<?= e($fieldValue('receivables')) ?>" placeholder="0.00" data-fin-field="receivables">
                    </div>
                    <div class="field">
                        <label for="inventory">Inventario</label>
                        <input id="inventory" name="inventory" type="number" step="0.01" min="0" value="<?= e($fieldValue('inventory')) ?>" placeholder="0.00" data-fin-field="inventory">
                    </div>
                    <div class="field">
                        <label for="total_liabilities">Pasivo total</label>
                        <input id="total_liabilities" name="total_liabilities" type="number" step="0.01" min="0" value="<?= e($fieldValue('total_liabilities')) ?>" placeholder="0.00" data-fin-field="total_liabilities">
                    </div>
                    <div class="field">
                        <label for="current_liabilities">Pasivo circulante</label>
                        <input id="current_liabilities" name="current_liabilities" type="number" step="0.01" min="0" value="<?= e($fieldValue('current_liabilities')) ?>" placeholder="0.00" data-fin-field="current_liabilities">
                    </div>
                    <div class="field">
                        <label for="debt">Deuda financiera</label>
                        <input id="debt" name="debt" type="number" step="0.01" min="0" value="<?= e($fieldValue('debt')) ?>" placeholder="0.00" data-fin-field="debt">
                    </div>
                    <div class="field">
                        <label for="equity">Capital contable</label>
                        <input id="equity" name="equity" type="number" step="0.01" value="<?= e($fieldValue('equity')) ?>" placeholder="0.00" data-fin-field="equity">
                    </div>
                </div>

                <div class="finance-live" data-finance-preview>
                    <span class="summary-label"><?= icon('activity', 'icon icon-xs') ?> Indicadores preliminares</span>
                    <div class="finance-live-grid">
                        <div><span>Margen bruto</span><strong data-ratio="gross">—</strong></div>
                        <div><span>Margen operativo</span><strong data-ratio="operating">—</strong></div>
                        <div><span>Margen neto</span><strong data-ratio="net">—</strong></div>
                        <div><span>Razón circulante</span><strong data-ratio="current">—</strong></div>
                        <div><span>Pasivo / activo</span><strong data-ratio="debt">—</strong></div>
                        <div><span>ROE</span><strong data-ratio="roe">—</strong></div>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">04</span>
                    <div>
                        <h2>Análisis y recomendaciones</h2>
                        <p>Las cifras no hablan solas: explica causas, riesgos y decisiones.</p>
                    </div>
                </div>
                <div class="field">
                    <label for="analysis">Análisis financiero <span class="req">*</span></label>
                    <textarea id="analysis" name="analysis" rows="7" maxlength="8000" required data-counter placeholder="Evolución de ventas, estructura de costos, rentabilidad, liquidez, endeudamiento, capital de trabajo y comparación con el sector…"><?= e(old('analysis', $report['analysis'] ?? '')) ?></textarea>
                    <?php if (field_error('analysis')): ?><span class="field-error"><?= e(field_error('analysis')) ?></span><?php endif; ?>
                </div>
                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="findings">Hallazgos <span class="req">*</span></label>
                        <textarea id="findings" name="findings" rows="5" maxlength="6000" required placeholder="Tres a cinco hallazgos concretos, con la cifra que los sustenta."><?= e(old('findings', $report['findings'] ?? '')) ?></textarea>
                        <?php if (field_error('findings')): ?><span class="field-error"><?= e(field_error('findings')) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="recommendations">Recomendaciones <span class="req">*</span></label>
                        <textarea id="recommendations" name="recommendations" rows="5" maxlength="6000" required placeholder="Qué debería hacer la empresa, en qué orden y con qué indicador se mediría."><?= e(old('recommendations', $report['recommendations'] ?? '')) ?></textarea>
                        <?php if (field_error('recommendations')): ?><span class="field-error"><?= e(field_error('recommendations')) ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="field">
                    <label for="assumptions">Supuestos y limitaciones</label>
                    <textarea id="assumptions" name="assumptions" rows="4" maxlength="4000" placeholder="Fuente de las cifras, estimaciones, periodo inflacionario, cambios contables, información faltante…"><?= e(old('assumptions', $report['assumptions'] ?? '')) ?></textarea>
                </div>
                <div class="field">
                    <label for="references_text">Referencias</label>
                    <textarea id="references_text" name="references_text" rows="3" maxlength="4000" placeholder="Estados financieros, entrevistas, datos de la empresa, fuentes públicas…"><?= e(old('references_text', $report['references_text'] ?? '')) ?></textarea>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">05</span>
                    <div>
                        <h2>Anexos</h2>
                        <p>Adjunta el estado financiero, una hoja de cálculo o un PDF de respaldo.</p>
                    </div>
                </div>
                <div class="field">
                    <label for="documents">Archivos <span class="optional">(hasta 3)</span></label>
                    <input id="documents" name="documents[]" type="file" accept=".pdf,.xlsx,.xls,.csv,.docx" multiple>
                    <span class="field-help">PDF, XLSX, XLS, CSV o DOCX · máximo 12 MB por archivo. Verifica que no contengan datos personales.</span>
                </div>
                <?php if ($isEdit): ?>
                    <?php $files = (new \App\Repositories\ReportRepository())->files((int) $report['id']); ?>
                    <?php if ($files !== []): ?>
                        <div class="attachment-list">
                            <?php foreach ($files as $file): ?>
                                <div class="attachment-row">
                                    <span class="attachment-icon"><?= icon('file-text', 'icon') ?></span>
                                    <div>
                                        <strong><?= e($file['original_name']) ?></strong>
                                        <small><?= e(strtoupper((string) pathinfo($file['original_name'], PATHINFO_EXTENSION))) ?> · <?= e(number_format((int) $file['size_bytes'] / 1024, 0)) ?> KB</small>
                                    </div>
                                    <form method="post" action="<?= e(url('/reportes/' . $report['slug'] . '/archivos/' . $file['id'] . '/eliminar')) ?>">
                                        <?= csrf_field() ?>
                                        <button class="text-link text-link-danger" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <div class="field">
                    <label for="license">Licencia de publicación</label>
                    <select id="license" name="license">
                        <?php foreach (['CC BY-NC-SA 4.0' => 'CC BY-NC-SA 4.0 · educativo con atribución', 'CC BY 4.0' => 'CC BY 4.0 · uso amplio con atribución', 'Todos los derechos reservados' => 'Todos los derechos reservados'] as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= old('license', $report['license'] ?? 'CC BY-NC-SA 4.0') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </section>

            <div class="form-actions">
                <div class="form-actions-left">
                    <a class="btn btn-ghost" href="<?= e($isEdit ? url('/reportes/' . $report['slug']) : url('/mi-panel')) ?>">Cancelar</a>
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
                <h2 class="sidebar-title"><?= icon('info', 'icon icon-sm') ?> Cómo se calculan</h2>
                <ul class="check-list check-list-small">
                    <li>Margen bruto = (ingresos − costo de ventas) / ingresos.</li>
                    <li>Margen operativo = utilidad de operación / ingresos.</li>
                    <li>Razón circulante = activo circulante / pasivo circulante.</li>
                    <li>ROE = utilidad neta / capital contable.</li>
                </ul>
                <p class="field-help">Los indicadores se calculan sólo si existen las cifras necesarias. No se inventan datos.</p>
            </div>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('shield', 'icon icon-sm') ?> Confidencialidad</h2>
                <p>Anonimiza el nombre de la empresa si no tienes autorización. Puedes publicar el reporte como “empresa agroindustrial de Jalisco”.</p>
            </div>
        </aside>
    </div>
</section>
