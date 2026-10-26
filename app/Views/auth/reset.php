<?php $errors = \App\Core\Flash::errors(); ?>
<section class="auth-section">
    <div class="container auth-grid">
        <div class="auth-panel">
            <span class="eyebrow">Nueva contraseña</span>
            <h1>Crea una contraseña segura</h1>
            <p class="auth-lead">Usa al menos 10 caracteres y no reutilices una contraseña de otro servicio.</p>

            <?php if ($errors !== []): ?>
                <div class="form-error-summary" role="alert">
                    <?= icon('alert-triangle', 'icon') ?>
                    <div><strong>Revisa los campos</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="post" action="<?= e(url('/recuperar/' . $token)) ?>">
                <?= csrf_field() ?>
                <div class="field">
                    <label for="password">Nueva contraseña</label>
                    <div class="password-field">
                        <input id="password" name="password" type="password" autocomplete="new-password" minlength="10" required placeholder="Mínimo 10 caracteres">
                        <?php \App\Core\View::partial('partials/password-toggle', ['id' => 'password']); ?>
                    </div>
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirmar contraseña</label>
                    <div class="password-field">
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="10" required>
                        <?php \App\Core\View::partial('partials/password-toggle', ['id' => 'password_confirmation']); ?>
                    </div>
                </div>
                <button class="btn btn-primary btn-block" type="submit">Guardar contraseña <?= icon('arrow-right', 'icon icon-sm') ?></button>
            </form>
        </div>
        <aside class="auth-aside">
            <span class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> Casi listo</span>
            <h2>Después podrás volver a tus casos y reportes.</h2>
            <p>Tu contenido y tus favoritos siguen intactos. Si detectas actividad extraña, cambia la contraseña y avísanos.</p>
        </aside>
    </div>
</section>
