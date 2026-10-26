<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

final class SitemapController extends BaseController
{
    public function index(Request $request): Response
    {
        $urls = [
            ['loc' => url('/'), 'priority' => '1.0'],
            ['loc' => url('/casos'), 'priority' => '0.9'],
            ['loc' => url('/reportes'), 'priority' => '0.9'],
            ['loc' => url('/oportunidades'), 'priority' => '0.9'],
            ['loc' => url('/universidades'), 'priority' => '0.7'],
            ['loc' => url('/nosotros'), 'priority' => '0.5'],
            ['loc' => url('/guias'), 'priority' => '0.6'],
            ['loc' => url('/etica'), 'priority' => '0.4'],
            ['loc' => url('/privacidad'), 'priority' => '0.3'],
            ['loc' => url('/terminos'), 'priority' => '0.3'],
        ];

        foreach (['cases' => 'casos', 'reports' => 'reportes', 'opportunities' => 'oportunidades'] as $table => $prefix) {
            $rows = Database::select("select slug, published_at from {$table} where status = 'publicado' and deleted_at is null order by published_at desc limit 1000");
            foreach ($rows as $row) {
                $urls[] = [
                    'loc' => url('/' . $prefix . '/' . $row['slug']),
                    'lastmod' => $row['published_at'] ? date('c', strtotime((string) $row['published_at'])) : null,
                    'priority' => '0.8',
                ];
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n    <loc>" . htmlspecialchars($url['loc'], ENT_XML1) . "</loc>\n";
            if (!empty($url['lastmod'])) {
                $xml .= '    <lastmod>' . htmlspecialchars($url['lastmod'], ENT_XML1) . "</lastmod>\n";
            }
            $xml .= '    <priority>' . $url['priority'] . "</priority>\n  </url>\n";
        }
        $xml .= '</urlset>';

        return new Response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(Request $request): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /mi-panel',
            'Disallow: /perfil',
            'Disallow: /acceder',
            'Disallow: /registro',
            'Disallow: /recuperar',
            'Disallow: /buscar',
            'Disallow: /uploads/constancias',
            '',
            'Sitemap: ' . url('/sitemap.xml'),
        ];
        return new Response(implode("\n", $lines) . "\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
