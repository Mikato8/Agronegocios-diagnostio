<?php
/** @var array<string, mixed>|null $currentUser */
/** @var array<int, array{type: string, message: string}> $flashMessages */
$adminPath = current_path();
?><!doctype html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Panel editorial') ?> · AgroNegocios MX</title>
    <meta name="description" content="<?= e($metaDescription ?? '') ?>">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#173f32">
    <link rel="icon" href="<?= e(url('/assets/img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="admin-body">
<a class="skip-link" href="#admin-contenido">Saltar al contenido</a>

<div class="admin-shell">
    <aside class="admin-sidebar" id="admin-sidebar">
        <a class="brand brand-light" href="<?= e(url('/admin')) ?>">
            <img class="brand-mark" src="<?= e(url('/assets/img/logo.svg')) ?>" alt="" width="40" height="40">
            <span class="brand-text">
                <span class="brand-name">Agro<span class="brand-accent">Negocios</span></span>
                <span class="brand-sub">MX · Editorial</span>
            </span>
        </a>

        <p class="sidebar-label">Panel editorial</p>
        <nav class="admin-nav" aria-label="Navegación del panel">
            <a class="<?= $adminPath === '/admin' ? 'is-active' : '' ?>" href="<?= e(url('/admin')) ?>">
                <?= icon('dashboard') ?> <span>Resumen</span>
            </a>
            <a class="<?= str_starts_with($adminPath, '/admin/contenidos') || str_starts_with($adminPath, '/admin/revision') ? 'is-active' : '' ?>" href="<?= e(url('/admin/contenidos')) ?>">
                <?= icon('file-text') ?> <span>Contenido</span>
            </a>
            <a class="<?= str_starts_with($adminPath, '/admin/comentarios') ? 'is-active' : '' ?>" href="<?= e(url('/admin/comentarios')) ?>">
                <?= icon('message-circle') ?> <span>Comentarios</span>
            </a>
            <?php if (can('administrar')): ?>
                <a class="<?= str_starts_with($adminPath, '/admin/usuarios') ? 'is-active' : '' ?>" href="<?= e(url('/admin/usuarios')) ?>">
                    <?= icon('users') ?> <span>Usuarios</span>
                </a>
                <a class="<?= str_starts_with($adminPath, '/admin/auditoria') ? 'is-active' : '' ?>" href="<?= e(url('/admin/auditoria')) ?>">
                    <?= icon('shield') ?> <span>Auditoría</span>
                </a>
            <?php endif; ?>
        </nav>

        <div class="admin-sidebar-bottom">
            <a href="<?= e(url('/')) ?>"><?= icon('globe') ?> Ver el sitio</a>
            <a href="<?= e(url('/mi-panel')) ?>"><?= icon('folder') ?> Mi panel</a>
            <form method="post" action="<?= e(url('/salir')) ?>">
                <?= csrf_field() ?>
                <button type="submit"><?= icon('logout') ?> Cerrar sesión</button>
            </form>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="admin-menu-toggle" type="button" data-admin-toggle aria-controls="admin-sidebar" aria-expanded="false" aria-label="Abrir menú del panel">
                <?= icon('menu') ?>
            </button>
            <div class="admin-topbar-title">
                <span>AgroNegocios MX</span>
                <strong><?= e($title ?? 'Panel editorial') ?></strong>
            </div>
            <div class="admin-topbar-actions">
                <a class="btn btn-ghost btn-sm" href="<?= e(url('/')) ?>" target="_blank" rel="noopener">Ver sitio <?= icon('external-link', 'icon icon-xs') ?></a>
                <details class="user-menu">
                    <summary>
                        <?= avatar_tag((string) ($currentUser['name'] ?? 'AN'), $currentUser['avatar_url'] ?? null, 'avatar-sm') ?>
                        <span class="user-menu-name"><?= e($currentUser['name'] ?? 'Cuenta') ?></span>
                        <?= icon('chevron-down', 'icon icon-xs') ?>
                    </summary>
                    <div class="user-menu-panel">
                        <div class="user-menu-head">
                            <strong><?= e($currentUser['name'] ?? '') ?></strong>
                            <span><?= e($currentUser['email'] ?? '') ?></span>
                        </div>
                        <a href="<?= e(url('/perfil')) ?>"><?= icon('user') ?> Mi perfil</a>
                        <form method="post" action="<?= e(url('/salir')) ?>">
                            <?= csrf_field() ?>
                            <button type="submit"><?= icon('logout') ?> Cerrar sesión</button>
                        </form>
                    </div>
                </details>
            </div>
        </header>

        <?php \App\Core\View::partial('partials/flash', ['flashMessages' => $flashMessages]); ?>

        <main id="admin-contenido" class="admin-content">
            <?= $content ?>
        </main>
    </div>
</div>

<div class="admin-overlay" data-admin-overlay hidden></div>
<script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
</body>
</html>
