<?php $errors = \App\Core\Flash::errors(); ?>
<section class="auth-section">
    <div class="container auth-grid">
        <div class="auth-panel">
            <span class="eyebrow">Recuperar acceso</span>
            <h1>Restablece tu contraseña</h1>
            <p class="auth-lead">Escribe el correo con el que te registraste. Si existe, enviaremos un enlace para crear una nueva contraseña.</p>

            <?php if ($errors !== []): ?>
                <div class="form-error-summary" role="alert">
                    <?= icon('alert-triangle', 'icon') ?>
                    <div><strong>Revisa el campo</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="post" action="<?= e(url('/recuperar')) ?>">
                <?= csrf_field() ?>
                <div class="field">
                    <label for="email">Correo electrónico</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="<?= e(old('email')) ?>" placeholder="tu@universidad.edu.mx">
                </div>
                <button class="btn btn-primary btn-block" type="submit">Enviar enlace <?= icon('arrow-right', 'icon icon-sm') ?></button>
            </form>

            <p class="auth-switch"><a href="<?= e(url('/acceder')) ?>">Volver a iniciar sesión</a></p>
            <p class="auth-switch">¿No llega el correo? Revisa spam o escribe al comité editorial desde <a href="<?= e(url('/nosotros')) ?>">la página de contacto</a>.</p>
        </div>
        <aside class="auth-aside">
            <span class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> Seguridad</span>
            <h2>Tu cuenta, tus datos.</h2>
            <p>El enlace de recuperación vence en 60 minutos y sólo puede usarse una vez. Nunca te pediremos tu contraseña por correo.</p>
            <ul class="check-list check-list-light">
                <li><?= icon('check', 'icon icon-sm') ?> Contraseñas con hash bcrypt.</li>
                <li><?= icon('check', 'icon icon-sm') ?> Sesiones con cookies HttpOnly y SameSite.</li>
                <li><?= icon('check', 'icon icon-sm') ?> Protección CSRF en todos los formularios.</li>
            </ul>
        </aside>
    </div>
</section>
