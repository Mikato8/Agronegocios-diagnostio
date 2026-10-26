<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Str;

$options = getopt('', ['name::', 'email::', 'password::', 'state::', 'role::']);
$name = trim((string) ($options['name'] ?? 'Administrador AgroNegocios'));
$email = Str::normalizeEmail((string) ($options['email'] ?? 'admin@agronegocios.mx'));
$password = (string) ($options['password'] ?? getenv('ADMIN_PASSWORD') ?: '');
$state = (string) ($options['state'] ?? 'CMX');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Correo inválido.\n");
    exit(1);
}

if ($password === '') {
    fwrite(STDOUT, 'Contraseña para ' . $email . ': ');
    $password = trim((string) fgets(STDIN));
}
if (mb_strlen($password) < 12) {
    fwrite(STDERR, "La contraseña debe tener al menos 12 caracteres.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$existing = Database::value('select id from users where email = $1', [$email]);

if ($existing !== null) {
    Database::execute(
        "update users set name = $1, password_hash = $2, role = 'admin', status = 'verificado',
                email_verified = true, state_code = $3, deleted_at = null where id = $4",
        [$name, $hash, $state, (int) $existing]
    );
    echo "✓ Cuenta administrativa actualizada: {$email}\n";
    exit(0);
}

$row = Database::insert(
    "insert into users (name, email, password_hash, role, status, state_code, career, bio,
                        verification_method, email_verified, terms_accepted_at)
     values ($1, $2, $3, 'admin', 'verificado', $4, 'Administración del portal',
             'Cuenta del comité editorial de AgroNegocios MX.', 'admin', true, now())
     returning id",
    [$name, $email, $hash, $state]
);

echo '✓ Administrador creado con ID ' . (int) ($row['id'] ?? 0) . ": {$email}\n";
echo "  Cambia la contraseña después del primer acceso.\n";
