<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

$ok = true;
$check = static function (string $label, bool $value, string $detail = '') use (&$ok): void {
    $ok = $ok && $value;
    printf("[%s] %-28s %s\n", $value ? 'OK' : '!!', $label, $detail);
};

$check('PHP 8.2+', version_compare(PHP_VERSION, '8.2.0', '>='), PHP_VERSION);
foreach (['pdo_pgsql', 'curl', 'mbstring', 'fileinfo', 'gd', 'intl', 'zip'] as $extension) {
    $check('Extensión ' . $extension, extension_loaded($extension));
}
$check('storage escribible', is_writable(BASE_PATH . '/storage'), BASE_PATH . '/storage');
$check('uploads escribible', is_writable(BASE_PATH . '/public/uploads'), BASE_PATH . '/public/uploads');
$check('constancias escribible', is_writable(BASE_PATH . '/storage/constancias'), BASE_PATH . '/storage/constancias');
$check('.env presente', is_file(BASE_PATH . '/.env'));

$connected = Database::ping();
$check('Conexión PostgreSQL', $connected, $connected ? Database::driverName() : 'sin conexión');

if ($connected) {
    $migrations = (int) Database::value("select count(*) from information_schema.tables where table_schema = 'public' and table_name = 'schema_migrations'");
    $applied = $migrations ? (int) Database::value('select count(*) from schema_migrations') : 0;
    $check('Migraciones aplicadas', $applied > 0, (string) $applied);

    foreach (['users', 'cases', 'reports', 'opportunities', 'comments'] as $table) {
        $count = (int) Database::value("select count(*) from {$table}");
        $check('Tabla ' . $table, true, (string) $count . ' registros');
    }

    $published = (int) Database::value("select count(*) from cases where status = 'publicado'");
    $check('Casos publicados', $published > 0, (string) $published);
}

echo $ok ? "\nTodo listo.\n" : "\nHay comprobaciones pendientes.\n";
exit($ok ? 0 : 1);
