<?php
/** @var array<string, mixed> $user */
/** @var array<string, int> $activity */
/** @var array<int, array<string, mixed>> $states */
/** @var array<int, array<string, mixed>> $universities */
/** @var array<int, array<string, mixed>> $myCases */
/** @var array<int, array<string, mixed>> $myReports */
/** @var array<int, array<string, mixed>> $myOpportunities */
/** @var int $completeness */
$errors = \App\Core\Flash::errors();
?>
<section class="page-hero page-hero-compact">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Mi perfil</span>
        </nav>
        <div class="profile-hero">
            <?= avatar_tag((string) $user['name'], $user['avatar_url'] ?? null, 'avatar-xl') ?>
            <div class="profile-hero-main">
                <div class="case-kicker">
                    <span class="badge badge-<?= $user['status'] === 'verificado' ? 'success' : 'warning' ?>"><?= e(user_status_label($user['status'])) ?></span>
                    <span class="badge badge-soft"><?= e((string) config('app.roles.' . $user['role'], $user['role'])) ?></span>
                    <span class="badge badge-neutral"><?= e(verification_method_label($user['verification_method'])) ?></span>
                </div>
                <h1><?= e($user['name']) ?></h1>
                <p><?= e($user['university_name'] ?? ($user['institution_other'] ?: 'Universidad no indicada')) ?> · <?= e($user['career'] ?: 'Carrera no indicada') ?></p>
                <div class="profile-completeness">
                    <span>Perfil completo al <?= (int) $completeness ?>%</span>
                    <div class="profile-meter"><span style="width: <?= (int) $completeness ?>%"></span></div>
                </div>
            </div>
            <div class="profile-hero-stats">
                <div><strong><?= (int) $activity['cases'] ?></strong><span>casos</span></div>
                <div><strong><?= (int) $activity['reports'] ?></strong><span>reportes</span></div>
                <div><strong><?= (int) $activity['opportunities'] ?></strong><span>oportunidades</span></div>
                <div><strong><?= number_format((int) $activity['views']) ?></strong><span>vistas</span></div>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container profile-layout">
        <div class="profile-main">
            <?php if ($errors !== []): ?>
                <div class="form-error-summary" role="alert">
                    <?= icon('alert-triangle', 'icon') ?>
                    <div><strong>Revisa los siguientes campos</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
                </div>
            <?php endif; ?>

            <?php if ($user['status'] === 'pendiente'): ?>
                <div class="notice notice-warning">
                    <?= icon('shield', 'icon') ?>
                    <div>
                        <strong>Tu cuenta está en revisión</strong>
                        <p>Si tu correo no es institucional, sube una constancia de estudios. Un editor revisará tu cuenta y podrás publicar y comentar.</p>
                    </div>
                </div>
            <?php endif; ?>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">01</span>
                    <div><h2>Datos de estudiante</h2><p>Tu nombre, universidad, carrera y semblanza se muestran junto a tus publicaciones.</p></div>
                </div>
                <form method="post" action="<?= e(url('/perfil')) ?>">
                    <?= csrf_field() ?>
                    <div class="field-grid field-grid-2">
                        <div class="field">
                            <label for="name">Nombre completo</label>
                            <input id="name" name="name" type="text" required maxlength="120" value="<?= e(old('name', $user['name'])) ?>">
                        </div>
                        <div class="field">
                            <label for="email">Correo electrónico</label>
                            <input id="email" type="email" value="<?= e($user['email']) ?>" disabled>
                            <span class="field-help">El correo no se puede cambiar aquí; escríbenos si necesitas actualizarlo.</span>
                        </div>
                    </div>
                    <div class="field-grid field-grid-2">
                        <div class="field">
                            <label for="university_id">Universidad</label>
                            <select id="university_id" name="university_id">
                                <option value="">Selecciona…</option>
                                <?php foreach ($universities as $university): ?>
                                    <option value="<?= (int) $university['id'] ?>" <?= (string) old('university_id', $user['university_id'] ?? '') === (string) $university['id'] ? 'selected' : '' ?>><?= e($university['name']) ?><?= $university['acronym'] !== '' ? ' (' . e($university['acronym']) . ')' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="institution_other">Otra institución</label>
                            <input id="institution_other" name="institution_other" type="text" maxlength="180" value="<?= e(old('institution_other', $user['institution_other'])) ?>">
                        </div>
                    </div>
                    <div class="field-grid field-grid-3">
                        <div class="field">
                            <label for="career">Carrera</label>
                            <input id="career" name="career" type="text" required maxlength="160" value="<?= e(old('career', $user['career'])) ?>">
                        </div>
                        <div class="field">
                            <label for="semester">Semestre</label>
                            <select id="semester" name="semester">
                                <option value="">No indicar</option>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>" <?= (string) old('semester', $user['semester'] ?? '') === (string) $i ? 'selected' : '' ?>><?= $i ?>º</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="state_code">Estado</label>
                            <select id="state_code" name="state_code" required>
                                <?php foreach ($states as $state): ?>
                                    <option value="<?= e($state['code']) ?>" <?= old('state_code', $user['state_code']) === $state['code'] ? 'selected' : '' ?>><?= e($state['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="field">
                        <label for="student_id">Matrícula <span class="optional">(privada)</span></label>
                        <input id="student_id" name="student_id" type="text" maxlength="40" value="<?= e(old('student_id', $user['student_id'])) ?>">
                        <span class="field-help">No se muestra públicamente; sirve para uso interno del comité editorial.</span>
                    </div>
                    <div class="field">
                        <label for="bio">Semblanza</label>
                        <textarea id="bio" name="bio" rows="4" maxlength="1200" placeholder="Tus intereses y experiencia en agronegocios…"><?= e(old('bio', $user['bio'])) ?></textarea>
                    </div>
                    <button class="btn btn-primary" type="submit">Guardar cambios</button>
                </form>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">02</span>
                    <div><h2>Verificación</h2><p>Si tu correo no es institucional, sube tu constancia de estudios.</p></div>
                </div>
                <div class="verification-box verification-<?= e($user['status']) ?>">
                    <?= icon($user['status'] === 'verificado' ? 'check-circle' : 'shield', 'icon') ?>
                    <div>
                        <strong><?= e(user_status_label($user['status'])) ?></strong>
                        <p><?= e(verification_method_label($user['verification_method'])) ?><?php if (!empty($user['verification_note'])): ?> · <?= e($user['verification_note']) ?><?php endif; ?></p>
                    </div>
                </div>
                <?php if ($user['status'] !== 'verificado' || !empty($user['proof_path'])): ?>
                    <form method="post" action="<?= e(url('/perfil/constancia')) ?>" enctype="multipart/form-data" class="inline-form">
                        <?= csrf_field() ?>
                        <div class="field">
                            <label for="proof">Constancia de estudios</label>
                            <input id="proof" name="proof" type="file" accept=".pdf,image/jpeg,image/png" required>
                            <span class="field-help">PDF, JPG o PNG · máximo 8 MB. No se publica; sólo la consulta el comité.</span>
                        </div>
                        <button class="btn btn-outline" type="submit">Subir constancia</button>
                    </form>
                <?php endif; ?>
                <p class="field-help">Recuerda: nunca pedimos cédula profesional. La verificación es académica: correo institucional o constancia de estudios.</p>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">03</span>
                    <div><h2>Seguridad</h2><p>Cambia tu contraseña periódicamente.</p></div>
                </div>
                <form method="post" action="<?= e(url('/perfil/password')) ?>">
                    <?= csrf_field() ?>
                    <div class="field">
                        <label for="current_password">Contraseña actual</label>
                        <div class="password-field">
                            <input id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                            <?php \App\Core\View::partial('partials/password-toggle', ['id' => 'current_password']); ?>
                        </div>
                    </div>
                    <div class="field-grid field-grid-2">
                        <div class="field">
                            <label for="new_password">Nueva contraseña</label>
                            <div class="password-field">
                                <input id="new_password" name="password" type="password" autocomplete="new-password" minlength="10" required>
                                <?php \App\Core\View::partial('partials/password-toggle', ['id' => 'new_password']); ?>
                            </div>
                        </div>
                        <div class="field">
                            <label for="new_password_confirmation">Confirmar nueva contraseña</label>
                            <div class="password-field">
                                <input id="new_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="10" required>
                                <?php \App\Core\View::partial('partials/password-toggle', ['id' => 'new_password_confirmation']); ?>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-dark" type="submit">Actualizar contraseña</button>
                </form>
            </section>

            <section class="form-section form-section-danger">
                <div class="form-section-head">
                    <span class="form-step">04</span>
                    <div><h2>Eliminar mi cuenta</h2><p>Esta acción cierra tu cuenta y anonimiza tus datos personales. No se puede deshacer.</p></div>
                </div>
                <div class="danger-note">
                    <?= icon('alert-triangle', 'icon') ?>
                    <div>
                        <strong>Qué pasa con tu contenido</strong>
                        <p>Tus publicaciones permanecen en el archivo con el crédito “Cuenta eliminada”. Tus borradores y datos de perfil dejan de ser accesibles. Descarga una copia antes de continuar.</p>
                    </div>
                </div>
                <form method="post" action="<?= e(url('/perfil/eliminar')) ?>">
                    <?= csrf_field() ?>
                    <div class="field-grid field-grid-2">
                        <div class="field">
                            <label for="delete_password">Confirma tu contraseña</label>
                            <input id="delete_password" name="password" type="password" autocomplete="current-password" required>
                        </div>
                        <div class="field">
                            <label for="delete_confirmation">Escribe <strong>ELIMINAR</strong></label>
                            <input id="delete_confirmation" name="confirmation" type="text" maxlength="8" required autocomplete="off" placeholder="ELIMINAR">
                        </div>
                    </div>
                    <button class="btn btn-danger" type="submit"><?= icon('trash', 'icon icon-sm') ?> Eliminar mi cuenta definitivamente</button>
                </form>
            </section>
        </div>

        <aside class="form-sidebar">
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('user', 'icon icon-sm') ?> Foto de perfil</h2>
                <div class="avatar-upload-preview"><?= avatar_tag((string) $user['name'], $user['avatar_url'] ?? null, 'avatar-lg') ?></div>
                <form class="avatar-upload-form" method="post" action="<?= e(url('/perfil/avatar')) ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <label class="btn btn-outline btn-sm" for="avatar">Elegir imagen</label>
                    <input class="sr-only" id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" required>
                    <button class="btn btn-primary btn-sm" type="submit">Subir foto</button>
                </form>
                <?php if (!empty($user['avatar_url'])): ?>
                    <form method="post" action="<?= e(url('/perfil/avatar/eliminar')) ?>">
                        <?= csrf_field() ?>
                        <button class="btn btn-ghost btn-sm" type="submit">Quitar foto</button>
                    </form>
                <?php endif; ?>
                <p class="avatar-upload-help">JPG, PNG o WebP · máximo 8 MB. Se recorta en círculo.</p>
            </div>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('folder', 'icon icon-sm') ?> Tu contenido</h2>
                <div class="sidebar-list">
                    <a href="<?= e(url('/mi-panel')) ?>">Mi panel <span><?= (int) $activity['cases'] + (int) $activity['reports'] + (int) $activity['opportunities'] ?></span></a>
                    <a href="<?= e(url('/casos/nuevo')) ?>">Nuevo caso <span>+</span></a>
                    <a href="<?= e(url('/reportes/nuevo')) ?>">Nuevo reporte <span>+</span></a>
                    <a href="<?= e(url('/oportunidades/nueva')) ?>">Nueva oportunidad <span>+</span></a>
                </div>
            </div>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('shield', 'icon icon-sm') ?> Privacidad</h2>
                <p>Tu correo, matrícula y constancia no se publican. Consulta el <a href="<?= e(url('/privacidad')) ?>">aviso de privacidad</a>.</p>
            </div>
        </aside>
    </div>
</section>
