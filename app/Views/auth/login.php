<?php $errors = \App\Core\Flash::errors(); ?>
<section class="auth-section">
    <div class="container auth-grid">
        <div class="auth-panel">
            <span class="eyebrow">Bienvenido de nuevo</span>
            <h1>Accede a tu cuenta</h1>
            <p class="auth-lead">Continúa tus casos, revisa tus reportes y da seguimiento a las oportunidades que guardaste.</p>

            <?php if ($errors !== []): ?>
                <div class="form-error-summary" role="alert">
                    <?= icon('alert-triangle', 'icon') ?>
                    <div><strong>No pudimos iniciar sesión</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="post" action="<?= e(url('/acceder')) ?>">
                <?= csrf_field() ?>
                <div class="field">
                    <label for="email">Correo electrónico</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="<?= e(old('email')) ?>" placeholder="tu@universidad.edu.mx">
                </div>
                <div class="field">
                    <div class="label-row">
                        <label for="password">Contraseña</label>
                        <a href="<?= e(url('/recuperar')) ?>">¿Olvidaste tu contraseña?</a>
                    </div>
                    <div class="password-field">
                        <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="Tu contraseña">
                        <?php \App\Core\View::partial('partials/password-toggle', ['id' => 'password']); ?>
                    </div>
                </div>
                <label class="check-line">
                    <input type="checkbox" name="remember" value="1" <?= old('remember') ? 'checked' : '' ?>>
                    <span>Mantener mi sesión iniciada en este dispositivo</span>
                </label>
                <button class="btn btn-primary btn-block" type="submit">Iniciar sesión <?= icon('arrow-right', 'icon icon-sm') ?></button>
            </form>

            <p class="auth-switch">¿Aún no tienes cuenta? <a href="<?= e(url('/registro')) ?>">Regístrate como estudiante</a></p>
        </div>

        <aside class="auth-aside">
            <div class="auth-aside-top">
                <span class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> Comunidad estudiantil</span>
                <h2>El criterio se entrena leyendo, escribiendo y discutiendo casos.</h2>
                <p>Casos de empresas agropecuarias, reportes financieros con indicadores y oportunidades reales para practicar la toma de decisiones.</p>
            </div>
            <div class="auth-quote">
                <p>“Nadie aprende agronegocios sólo con teoría. Un caso bien documentado es un simulador de decisiones.”</p>
                <span>Comité editorial · AgroNegocios MX</span>
            </div>
            <ul class="check-list check-list-light">
                <li><?= icon('check', 'icon icon-sm') ?> Verificación con correo institucional o constancia.</li>
                <li><?= icon('check', 'icon icon-sm') ?> Sin cédula profesional ni trámites innecesarios.</li>
                <li><?= icon('check', 'icon icon-sm') ?> Revisión editorial y crédito para tu trabajo.</li>
            </ul>
        </aside>
    </div>
</section>
