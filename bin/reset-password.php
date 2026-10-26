<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Str;

$options = getopt('', ['email::', 'password::']);
$email = Str::normalizeEmail((string) ($options['email'] ?? ''));
$password = (string) ($options['password'] ?? getenv('NEW_PASSWORD') ?: '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Uso: php bin/reset-password.php --email=correo [--password=NuevaClave]\n");
    exit(1);
}
if ($password === '') {
    fwrite(STDOUT, 'Nueva contraseña: ');
    $password = trim((string) fgets(STDIN));
}
if (mb_strlen($password) < 10) {
    fwrite(STDERR, "La contraseña debe tener al menos 10 caracteres.\n");
    exit(1);
}

$id = Database::value('select id from users where email = $1 and deleted_at is null', [$email]);
if ($id === null) {
    fwrite(STDERR, "No existe una cuenta con ese correo.\n");
    exit(1);
}
Database::execute('update users set password_hash = $1 where id = $2', [password_hash($password, PASSWORD_DEFAULT), (int) $id]);
Database::execute('update password_resets set used_at = now() where user_id = $1 and used_at is null', [(int) $id]);
echo "✓ Contraseña actualizada para {$email}\n";
