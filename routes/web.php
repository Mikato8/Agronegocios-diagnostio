<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\CaseController;
use App\Controllers\DashboardController;
use App\Controllers\HealthController;
use App\Controllers\HomeController;
use App\Controllers\OpportunityController;
use App\Controllers\PageController;
use App\Controllers\ProfileController;
use App\Controllers\ReportController;
use App\Controllers\SearchController;
use App\Controllers\SitemapController;
use App\Controllers\UniversityController;
use App\Core\Router;

return static function (Router $router): void {
    // Portada y exploración
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/buscar', [SearchController::class, 'index']);

    // Casos de agronegocios
    $router->get('/casos', [CaseController::class, 'index']);
    $router->get('/casos/nuevo', [CaseController::class, 'create']);
    $router->post('/casos', [CaseController::class, 'store']);
    $router->get('/casos/{slug}/editar', [CaseController::class, 'edit']);
    $router->post('/casos/{slug}', [CaseController::class, 'update']);
    $router->post('/casos/{slug}/enviar', [CaseController::class, 'submit']);
    $router->get('/casos/{slug}/pdf', [CaseController::class, 'pdf']);
    $router->post('/casos/{slug}/comentarios', [CaseController::class, 'comment']);
    $router->post('/casos/{slug}/util', [CaseController::class, 'react']);
    $router->post('/casos/{slug}/guardar', [CaseController::class, 'bookmark']);
    $router->post('/casos/{slug}/imagenes/{imageId}/eliminar', [CaseController::class, 'deleteImage']);
    $router->get('/casos/{slug}', [CaseController::class, 'show']);

    // Reportes financieros
    $router->get('/reportes', [ReportController::class, 'index']);
    $router->get('/reportes/nuevo', [ReportController::class, 'create']);
    $router->post('/reportes', [ReportController::class, 'store']);
    $router->get('/reportes/{slug}/editar', [ReportController::class, 'edit']);
    $router->post('/reportes/{slug}', [ReportController::class, 'update']);
    $router->post('/reportes/{slug}/enviar', [ReportController::class, 'submit']);
    $router->get('/reportes/{slug}/pdf', [ReportController::class, 'pdf']);
    $router->post('/reportes/{slug}/comentarios', [ReportController::class, 'comment']);
    $router->post('/reportes/{slug}/util', [ReportController::class, 'react']);
    $router->post('/reportes/{slug}/guardar', [ReportController::class, 'bookmark']);
    $router->post('/reportes/{slug}/archivos/{fileId}/eliminar', [ReportController::class, 'deleteFile']);
    $router->get('/reportes/{slug}', [ReportController::class, 'show']);

    // Oportunidades
    $router->get('/oportunidades', [OpportunityController::class, 'index']);
    $router->get('/oportunidades/nueva', [OpportunityController::class, 'create']);
    $router->post('/oportunidades', [OpportunityController::class, 'store']);
    $router->get('/oportunidades/{slug}/editar', [OpportunityController::class, 'edit']);
    $router->post('/oportunidades/{slug}', [OpportunityController::class, 'update']);
    $router->post('/oportunidades/{slug}/enviar', [OpportunityController::class, 'submit']);
    $router->post('/oportunidades/{slug}/interes', [OpportunityController::class, 'interest']);
    $router->post('/oportunidades/{slug}/interes/{interestId}', [OpportunityController::class, 'updateInterest']);
    $router->get('/oportunidades/{slug}/pdf', [OpportunityController::class, 'pdf']);
    $router->post('/oportunidades/{slug}/comentarios', [OpportunityController::class, 'comment']);
    $router->post('/oportunidades/{slug}/util', [OpportunityController::class, 'react']);
    $router->post('/oportunidades/{slug}/guardar', [OpportunityController::class, 'bookmark']);
    $router->get('/oportunidades/{slug}', [OpportunityController::class, 'show']);

    // Universidades y páginas
    $router->get('/universidades', [UniversityController::class, 'index']);
    $router->get('/universidades/{slug}', [UniversityController::class, 'show']);
    $router->get('/nosotros', [PageController::class, 'about']);
    $router->get('/etica', [PageController::class, 'ethics']);
    $router->get('/privacidad', [PageController::class, 'privacy']);
    $router->get('/terminos', [PageController::class, 'terms']);
    $router->get('/guias', [PageController::class, 'guides']);

    // Cuenta
    $router->get('/acceder', [AuthController::class, 'showLogin']);
    $router->post('/acceder', [AuthController::class, 'login']);
    $router->get('/registro', [AuthController::class, 'showRegister']);
    $router->post('/registro', [AuthController::class, 'register']);
    $router->post('/salir', [AuthController::class, 'logout']);
    $router->get('/recuperar', [AuthController::class, 'showForgot']);
    $router->post('/recuperar', [AuthController::class, 'sendReset']);
    $router->get('/recuperar/{token}', [AuthController::class, 'showReset']);
    $router->post('/recuperar/{token}', [AuthController::class, 'reset']);
    $router->get('/perfil', [ProfileController::class, 'show']);
    $router->post('/perfil', [ProfileController::class, 'update']);
    $router->post('/perfil/avatar', [ProfileController::class, 'avatar']);
    $router->post('/perfil/avatar/eliminar', [ProfileController::class, 'removeAvatar']);
    $router->post('/perfil/password', [ProfileController::class, 'password']);
    $router->post('/perfil/constancia', [ProfileController::class, 'proof']);
    $router->post('/perfil/eliminar', [ProfileController::class, 'deleteAccount']);
    $router->get('/mi-panel', [DashboardController::class, 'index']);
    $router->get('/estudiante/{id}', [ProfileController::class, 'publicProfile']);

    // Panel editorial
    $router->get('/admin', [AdminController::class, 'dashboard']);
    $router->get('/admin/contenidos', [AdminController::class, 'contents']);
    $router->get('/admin/revision/{type}/{id}', [AdminController::class, 'review']);
    $router->post('/admin/revision/{type}/{id}', [AdminController::class, 'reviewPost']);
    $router->post('/admin/destacar/{type}/{id}', [AdminController::class, 'feature']);
    $router->get('/admin/usuarios', [AdminController::class, 'users']);
    $router->get('/admin/usuarios/{id}', [AdminController::class, 'user']);
    $router->post('/admin/usuarios/{id}/accion', [AdminController::class, 'userAction']);
    $router->get('/admin/comentarios', [AdminController::class, 'comments']);
    $router->post('/admin/comentarios/{id}/accion', [AdminController::class, 'commentAction']);
    $router->get('/admin/auditoria', [AdminController::class, 'auditLog']);
    $router->get('/admin/constancias/{userId}/{file}', [AdminController::class, 'proof']);

    // Infraestructura
    $router->get('/salud', [HealthController::class, 'index']);
    $router->get('/sitemap.xml', [SitemapController::class, 'index']);
    $router->get('/robots.txt', [SitemapController::class, 'robots']);
};
