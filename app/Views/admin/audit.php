<?php /** @var array<int, array<string, mixed>> $entries */ ?>
<div class="admin-page-head">
    <div><span class="eyebrow">Seguridad</span><h1>Auditoría</h1><p>Registro de acciones editoriales, verificaciones y cambios de cuenta.</p></div>
</div>

<section class="admin-card">
    <?php if ($entries === []): ?>
        <div class="admin-empty"><p>Sin registros todavía.</p></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="admin-table">
                <thead><tr><th>Acción</th><th>Actor</th><th>Entidad</th><th>Detalle</th><th>IP</th><th>Fecha</th></tr></thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
                        <tr>
                            <td class="table-title"><?= e($entry['action']) ?></td>
                            <td class="table-user"><?= e($entry['actor_name'] ?? 'Sistema') ?></td>
                            <td class="table-muted"><?= e($entry['entity_type']) ?><?= $entry['entity_id'] ? ' #' . (int) $entry['entity_id'] : '' ?></td>
                            <td class="table-muted"><code class="audit-meta"><?= e(excerpt((string) $entry['meta'], 90)) ?></code></td>
                            <td class="table-muted"><?= e($entry['ip'] ?? '—') ?></td>
                            <td class="table-muted"><?= e(format_date($entry['created_at'], 'd/m/Y H:i')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
