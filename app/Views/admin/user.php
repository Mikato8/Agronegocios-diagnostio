<?php
/** @var array<string, mixed> $user */
/** @var array<string, int> $activity */
/** @var string|null $proof */
?>
<div class="admin-page-head">
    <div>
        <a class="text-link" href="<?= e(url('/admin/usuarios')) ?>"><?= icon('arrow-left', 'icon icon-xs') ?> Volver a usuarios</a>
        <h1><?= e($user['name']) ?></h1>
        <p><?= e($user['email']) ?> · <?= e($user['university_name'] ?? ($user['institution_other'] ?: 'Sin universidad')) ?> · <span class="badge badge-<?= $user['status'] === 'verificado' ? 'success' : ($user['status'] === 'pendiente' ? 'warning' : 'danger') ?>"><?= e(user_status_label($user['status'])) ?></span></p>
    </div>
    <div class="admin-page-actions">
        <a class="btn btn-outline btn-sm" href="<?= e(url('/estudiante/' . $user['id'])) ?>" target="_blank" rel="noopener">Ver perfil público</a>
    </div>
</div>

<div class="admin-review-layout">
    <div class="admin-review-main">
        <section class="admin-card">
            <div class="admin-card-head"><h2 class="admin-card-title">Datos de la cuenta</h2></div>
            <dl class="plot-data plot-data-wide">
                <div><dt>Nombre</dt><dd><?= e($user['name']) ?></dd></div>
                <div><dt>Correo</dt><dd><?= e($user['email']) ?></dd></div>
                <div><dt>Rol</dt><dd><?= e((string) config('app.roles.' . $user['role'], $user['role'])) ?></dd></div>
                <div><dt>Estado</dt><dd><?= e(user_status_label($user['status'])) ?></dd></div>
                <div><dt>Verificación</dt><dd><?= e(verification_method_label($user['verification_method'])) ?></dd></div>
                <div><dt>Universidad</dt><dd><?= e($user['university_name'] ?? ($user['institution_other'] ?: '—')) ?></dd></div>
                <div><dt>Carrera</dt><dd><?= e($user['career'] ?: '—') ?></dd></div>
                <div><dt>Semestre</dt><dd><?= e(semester_label($user['semester'] !== null ? (int) $user['semester'] : null)) ?></dd></div>
                <div><dt>Estado</dt><dd><?= e($user['state_name'] ?? '—') ?></dd></div>
                <div><dt>Matrícula</dt><dd><?= e($user['student_id'] ?: 'No indicada') ?></dd></div>
                <div><dt>Registro</dt><dd><?= e(format_date($user['created_at'], 'd/m/Y H:i')) ?></dd></div>
                <div><dt>Último acceso</dt><dd><?= e($user['last_login_at'] ? format_date($user['last_login_at'], 'd/m/Y H:i') : '—') ?></dd></div>
            </dl>
            <?php if (!empty($user['bio'])): ?><p class="review-summary"><?= e($user['bio']) ?></p><?php endif; ?>
        </section>

        <section class="admin-card">
            <div class="admin-card-head"><h2 class="admin-card-title">Actividad</h2></div>
            <div class="admin-content-counts">
                <div><strong><?= (int) $activity['cases'] ?></strong><span>casos</span></div>
                <div><strong><?= (int) $activity['reports'] ?></strong><span>reportes</span></div>
                <div><strong><?= (int) $activity['opportunities'] ?></strong><span>oportunidades</span></div>
                <div><strong><?= number_format((int) $activity['views']) ?></strong><span>vistas</span></div>
                <div><strong><?= (int) $activity['comments'] ?></strong><span>comentarios</span></div>
            </div>
        </section>

        <?php if ($proof !== null): ?>
            <section class="admin-card">
                <div class="admin-card-head"><h2 class="admin-card-title">Constancia de estudios</h2></div>
                <p class="muted">Documento privado. No lo compartas fuera del comité.</p>
                <a class="btn btn-outline" href="<?= e($proof) ?>" target="_blank" rel="noopener"><?= icon('file-text', 'icon icon-sm') ?> Abrir constancia</a>
            </section>
        <?php else: ?>
            <section class="admin-card">
                <div class="admin-card-head"><h2 class="admin-card-title">Constancia de estudios</h2></div>
                <p class="muted">La cuenta no tiene constancia cargada. <?= $user['verification_method'] === 'dominio_institucional' ? 'Se verificó por correo institucional.' : '' ?></p>
            </section>
        <?php endif; ?>
    </div>

    <aside class="admin-review-side">
        <section class="admin-card">
            <div class="admin-card-head"><h2 class="admin-card-title">Acciones</h2></div>
            <form method="post" action="<?= e(url('/admin/usuarios/' . $user['id'] . '/accion')) ?>" class="admin-action-form">
                <?= csrf_field() ?>
                <div class="field">
                    <label for="action">Acción</label>
                    <select id="action" name="action">
                        <option value="verify">Verificar cuenta</option>
                        <option value="suspend">Suspender</option>
                        <option value="reactivate">Reactivar</option>
                        <option value="role">Cambiar rol</option>
                    </select>
                </div>
                <div class="field">
                    <label for="role">Nuevo rol (si aplica)</label>
                    <select id="role" name="role">
                        <?php foreach (['estudiante' => 'Estudiante', 'docente' => 'Docente', 'editor' => 'Editor', 'admin' => 'Administrador'] as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= $user['role'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="note">Nota interna</label>
                    <textarea id="note" name="note" rows="3" maxlength="500" placeholder="Motivo o contexto de la decisión…"></textarea>
                </div>
                <button class="btn btn-primary btn-block" type="submit">Aplicar</button>
            </form>
        </section>
        <section class="admin-card">
            <div class="admin-card-head"><h2 class="admin-card-title">Riesgo</h2></div>
            <p class="muted">Suspende cuentas que suplanten identidad, publiquen datos personales o incumplan los criterios editoriales. Documenta el motivo en la nota interna.</p>
        </section>
    </aside>
</div>
