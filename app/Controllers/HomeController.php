<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CaseRepository;
use App\Repositories\CatalogRepository;
use App\Repositories\OpportunityRepository;
use App\Repositories\ReportRepository;

final class HomeController extends BaseController
{
    public function index(Request $request): Response
    {
        $catalog = new CatalogRepository();
        $cases = new CaseRepository();
        $reports = new ReportRepository();
        $opportunities = new OpportunityRepository();

        $stats = $catalog->stats();
        $featuredCases = $cases->featured(3);
        $featuredReports = $reports->featured(2);
        $featuredOpportunities = $opportunities->featured(3);
        $latestCases = $cases->latest(6);

        return $this->view('home/index', [
            'title' => 'AgroNegocios MX · Casos, finanzas y oportunidades para estudiantes de agronegocios',
            'metaDescription' => 'Publica y consulta casos reales de agronegocios, reportes financieros y oportunidades de negocio. Comunidad de estudiantes de universidades de México, sin cédula profesional.',
            'stats' => $stats,
            'featuredCases' => $featuredCases,
            'featuredReports' => $featuredReports,
            'featuredOpportunities' => $featuredOpportunities,
            'latestCases' => $latestCases,
            'sectors' => array_slice($catalog->sectors(), 0, 8),
            'states' => $catalog->popularStates(8),
            'universities' => $catalog->topUniversities(6),
        ]);
    }
}
