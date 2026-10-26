<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CaseRepository;
use App\Repositories\CatalogRepository;
use App\Repositories\OpportunityRepository;
use App\Repositories\ReportRepository;

final class SearchController extends BaseController
{
    public function index(Request $request): Response
    {
        $q = trim($request->string('q'));
        $sector = $request->string('sector');
        $results = [];
        $total = 0;

        if (mb_strlen($q) >= 2) {
            $filters = ['q' => $q, 'sort' => 'relevancia'];
            if ($sector !== '') {
                $filters['sector'] = $sector;
            }
            $cases = (new CaseRepository())->search($filters, 1, 6);
            $reports = (new ReportRepository())->search($filters, 1, 6);
            $opportunities = (new OpportunityRepository())->search($filters, 1, 6);
            $total = $cases['total'] + $reports['total'] + $opportunities['total'];

            foreach ($cases['items'] as $item) {
                $results[] = ['type' => 'caso', 'item' => $item];
            }
            foreach ($reports['items'] as $item) {
                $results[] = ['type' => 'reporte', 'item' => $item];
            }
            foreach ($opportunities['items'] as $item) {
                $results[] = ['type' => 'oportunidad', 'item' => $item];
            }
        }

        $catalog = new CatalogRepository();
        return $this->view('search/index', [
            'title' => ($q !== '' ? 'Buscar: ' . $q : 'Buscar') . ' · AgroNegocios MX',
            'metaDescription' => 'Busca casos, reportes financieros y oportunidades de agronegocios en México.',
            'q' => $q,
            'sector' => $sector,
            'results' => $results,
            'total' => $total,
            'sectors' => $catalog->sectors(),
            'robots' => 'noindex,follow',
        ]);
    }
}
