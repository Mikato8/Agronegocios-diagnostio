<?php /** @var array<int, array<string, mixed>> $comments */ ?>
<div class="admin-page-head">
    <div><span class="eyebrow">Moderación</span><h1>Comentarios</h1><p>Revisa, oculta o vuelve a mostrar comentarios de la comunidad.</p></div>
    <div class="admin-page-actions"><span class="badge badge-soft"><?= count($comments) ?> recientes</span></div>
</div>

<section class="admin-card">
    <?php if ($comments === []): ?>
        <div class="admin-empty"><p>Sin comentarios.</p></div>
    <?php else: ?>
        <div class="admin-list">
            <?php foreach ($comments as $comment): ?>
                <div class="admin-list-row admin-comment-row">
                    <?= avatar_tag((string) $comment['author_name'], null, 'avatar-sm') ?>
                    <span class="admin-list-main">
                        <strong><?= e($comment['author_name']) ?> <span class="muted">· <?= e($comment['author_email']) ?></span></strong>
                        <p><?= e($comment['body']) ?></p>
                        <small>En <?= e($comment['target_title'] ?? 'contenido') ?> · <?= e(time_ago($comment['created_at'])) ?> · <?= e($comment['status']) ?></small>
                    </span>
                    <form method="post" action="<?= e(url('/admin/comentarios/' . $comment['id'] . '/accion')) ?>">
                        <?= csrf_field() ?>
                        <?php if ($comment['status'] === 'visible'): ?>
                            <button class="btn btn-ghost btn-sm" type="submit" name="action" value="hide">Ocultar</button>
                        <?php else: ?>
                            <button class="btn btn-outline btn-sm" type="submit" name="action" value="show">Mostrar</button>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
