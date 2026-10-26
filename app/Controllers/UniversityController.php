<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CaseRepository;
use App\Repositories\CatalogRepository;
use App\Repositories\OpportunityRepository;
use App\Repositories\ReportRepository;
use App\Repositories\UserRepository;

final class UniversityController extends BaseController
{
    public function index(Request $request): Response
    {
        $catalog = new CatalogRepository();
        $universities = $catalog->universities();
        $grouped = $catalog->universitiesByState();

        return $this->view('universities/index', [
            'title' => 'Universidades · AgroNegocios MX',
            'metaDescription' => 'Directorio de universidades mexicanas con programas de agronegocios y estudiantes que publican en el portal.',
            'universities' => $universities,
            'grouped' => $grouped,
            'total' => count($universities),
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $catalog = new CatalogRepository();
        $university = $catalog->universityBySlug($slug);
        if ($university === null) {
            $this->notFound('No encontramos esa universidad.');
        }
        $students = (new UserRepository())->byUniversity((int) $university['id']);
        $filter = ['university_id' => (int) $university['id']];
        $cases = (new CaseRepository())->search($filter, 1, 3);
        $reports = (new ReportRepository())->search($filter, 1, 3);
        $opportunities = (new OpportunityRepository())->search($filter, 1, 3);

        return $this->view('universities/show', [
            'title' => $university['name'] . ' · AgroNegocios MX',
            'metaDescription' => 'Estudiantes, casos y reportes de ' . $university['name'] . ' en AgroNegocios MX.',
            'university' => $university,
            'students' => $students,
            'cases' => $cases['items'],
            'reports' => $reports['items'],
            'opportunities' => $opportunities['items'],
        ]);
    }
}
