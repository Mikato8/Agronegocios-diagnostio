<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

final class HealthController extends BaseController
{
    public function index(Request $request): Response
    {
        $checks = [
            'php' => PHP_VERSION,
            'pdo_pgsql' => extension_loaded('pdo_pgsql'),
            'curl' => extension_loaded('curl'),
            'gd' => extension_loaded('gd'),
            'mbstring' => extension_loaded('mbstring'),
            'zip' => extension_loaded('zip'),
            'storage' => is_writable(BASE_PATH . '/storage'),
            'uploads' => is_writable(BASE_PATH . '/public/uploads'),
            'database' => Database::ping(),
        ];

        $counts = [];
        if ($checks['database']) {
            try {
                $counts = [
                    'casos' => (int) Database::value("select count(*) from cases where status = 'publicado'"),
                    'reportes' => (int) Database::value("select count(*) from reports where status = 'publicado'"),
                    'oportunidades' => (int) Database::value("select count(*) from opportunities where status = 'publicado'"),
                    'estudiantes' => (int) Database::value("select count(*) from users where status = 'verificado'"),
                    'universidades' => (int) Database::value('select count(*) from universities'),
                ];
            } catch (\Throwable) {
                $checks['database'] = false;
            }
        }

        $payload = [
            'ok' => $checks['database'] && $checks['pdo_pgsql'],
            'app' => (string) config('app.name'),
            'environment' => (string) config('app.env'),
            'driver' => $checks['database'] ? Database::driverName() : null,
            'checks' => $checks,
            'counts' => $counts,
            'time' => date('c'),
        ];

        if ($request->wantsJson()) {
            return Response::json($payload, $payload['ok'] ? 200 : 503);
        }

        return $this->view('pages/health', [
            'title' => 'Estado del servicio · AgroNegocios MX',
            'payload' => $payload,
            'robots' => 'noindex,nofollow',
        ]);
    }
}
