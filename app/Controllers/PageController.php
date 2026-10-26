<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CatalogRepository;

final class PageController extends BaseController
{
    public function about(Request $request): Response
    {
        $stats = (new CatalogRepository())->stats();
        return $this->view('pages/about', [
            'title' => 'Nosotros · AgroNegocios MX',
            'metaDescription' => 'Una comunidad de estudiantes de agronegocios de México que documenta casos, finanzas y oportunidades con criterio editorial.',
            'stats' => $stats,
        ]);
    }

    public function ethics(Request $request): Response
    {
        return $this->view('pages/ethics', [
            'title' => 'Ética y criterios editoriales · AgroNegocios MX',
            'metaDescription' => 'Criterios de publicación, anonimización, conflicto de interés y verificación de estudiantes.',
        ]);
    }

    public function privacy(Request $request): Response
    {
        return $this->view('pages/privacy', [
            'title' => 'Aviso de privacidad · AgroNegocios MX',
            'metaDescription' => 'Cómo tratamos tus datos personales y qué información es pública en AgroNegocios MX.',
        ]);
    }

    public function terms(Request $request): Response
    {
        return $this->view('pages/terms', [
            'title' => 'Términos de uso · AgroNegocios MX',
            'metaDescription' => 'Reglas de convivencia, licencias y responsabilidad sobre el contenido publicado.',
        ]);
    }

    public function guides(Request $request): Response
    {
        return $this->view('pages/guides', [
            'title' => 'Guías para documentar · AgroNegocios MX',
            'metaDescription' => 'Método para escribir un caso de agronegocios, ordenar un reporte financiero y evaluar una oportunidad.',
        ]);
    }
}
