<?php
/** @var array<int, array<string, mixed>> $items */
/** @var int $total */
/** @var array<string, mixed> $filters */
/** @var \App\Core\Paginator $paginator */
/** @var array<string, int> $stats */
?>
<div class="admin-page-head">
    <div><span class="eyebrow">Administración</span><h1>Usuarios y verificación</h1><p>Verifica cuentas, revisa constancias y administra roles.</p></div>
    <div class="admin-page-actions"><span class="badge badge-soft"><?= number_format($total) ?> cuentas</span></div>
</div>

<div class="admin-stat-grid admin-stat-grid-4">
    <div class="admin-stat"><div class="admin-stat-info"><span>Verificadas</span><strong><?= (int) $stats['verified'] ?></strong></div></div>
    <div class="admin-stat admin-stat-warning"><div class="admin-stat-info"><span>Pendientes</span><strong><?= (int) $stats['pending'] ?></strong></div></div>
    <div class="admin-stat"><div class="admin-stat-info"><span>Docentes</span><strong><?= (int) $stats['teachers'] ?></strong></div></div>
    <div class="admin-stat"><div class="admin-stat-info"><span>Suspendidas</span><strong><?= (int) $stats['suspended'] ?></strong></div></div>
</div>

<form class="admin-filter" method="get" action="<?= e(url('/admin/usuarios')) ?>">
    <label class="filter-search"><?= icon('search', 'icon icon-sm') ?><input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Nombre o correo…"></label>
    <label><span>Estado</span><select name="status"><option value="">Todos</option><?php foreach (['pendiente' => 'Pendiente', 'verificado' => 'Verificado', 'suspendido' => 'Suspendido'] as $value => $label): ?><option value="<?= e($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
    <label><span>Rol</span><select name="role"><option value="">Todos</option><?php foreach (['estudiante' => 'Estudiante', 'docente' => 'Docente', 'editor' => 'Editor', 'admin' => 'Administrador'] as $value => $label): ?><option value="<?= e($value) ?>" <?= $filters['role'] === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
    <button class="btn btn-dark btn-sm" type="submit">Filtrar</button>
</form>

<section class="admin-card">
    <?php if ($items === []): ?>
        <div class="admin-empty"><p>No hay usuarios con esos filtros.</p></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="admin-table">
                <thead><tr><th>Nombre</th><th>Correo</th><th>Universidad</th><th>Rol</th><th>Estado</th><th>Registro</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="table-title"><a href="<?= e(url('/admin/usuarios/' . $item['id'])) ?>"><?= e($item['name']) ?></a></td>
                            <td class="table-muted"><?= e($item['email']) ?></td>
                            <td><?= e($item['university_acronym'] ?? ($item['institution_other'] ?: '—')) ?></td>
                            <td><span class="badge badge-neutral"><?= e((string) config('app.roles.' . $item['role'], $item['role'])) ?></span></td>
                            <td><span class="badge badge-<?= $item['status'] === 'verificado' ? 'success' : ($item['status'] === 'pendiente' ? 'warning' : 'danger') ?>"><?= e(user_status_label($item['status'])) ?></span></td>
                            <td class="table-muted"><?= e(format_date($item['created_at'], 'd/m/Y')) ?></td>
                            <td class="table-actions"><a class="btn btn-outline btn-sm" href="<?= e(url('/admin/usuarios/' . $item['id'])) ?>">Gestionar</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php \App\Core\View::partial('partials/pagination', ['paginator' => $paginator]); ?>
    <?php endif; ?>
</section>
