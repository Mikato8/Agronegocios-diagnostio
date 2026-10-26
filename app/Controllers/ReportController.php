<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\CatalogRepository;
use App\Repositories\InteractionRepository;
use App\Repositories\ReportRepository;
use App\Support\Financials;
use App\Support\ReportPdf;

final class ReportController extends BaseController
{
    public function index(Request $request): Response
    {
        $filters = [
            'q' => trim($request->string('q')),
            'sector' => $request->string('sector'),
            'chain' => $request->string('chain'),
            'state' => $request->string('state'),
            'company_type' => $request->string('company_type'),
            'year' => $request->int('year'),
            'sort' => $request->string('sort', 'recientes'),
        ];
        $page = max(1, $request->int('page', 1));
        $perPage = (int) config('app.pagination.reports', 9);
        $repo = new ReportRepository();
        $result = $repo->search($filters, $page, $perPage);
        $catalog = new CatalogRepository();

        return $this->view('reports/index', [
            'title' => 'Reportes financieros · AgroNegocios MX',
            'metaDescription' => 'Estados de resultados, balances y análisis financiero de empresas agropecuarias y agroindustriales de México, documentados por estudiantes.',
            'reports' => $result['items'],
            'total' => $result['total'],
            'filters' => $filters,
            'paginator' => new Paginator($result['total'], $perPage, $page),
            'sectorsList' => $catalog->sectors(),
            'chainsList' => $catalog->chains(),
            'states' => $catalog->states(),
            'years' => $catalog->years(),
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $repo = new ReportRepository();
        $report = $repo->findPublishedBySlug($slug);
        $preview = false;
        if ($report === null) {
            $user = Auth::user();
            $report = $repo->findBySlug($slug);
            if ($report !== null && $user !== null && ((int) $report['author_id'] === (int) $user['id'] || in_array($user['role'], (array) config('app.moderators', []), true))) {
                $preview = true;
            } else {
                $report = null;
            }
        }
        if ($report === null) {
            $this->notFound('Este reporte no existe o aún no se ha publicado.');
        }

        if (!$preview) {
            $repo->incrementViews((int) $report['id']);
            $report['views'] = (int) $report['views'] + 1;
        }

        $user = Auth::user();
        $interactions = new InteractionRepository();
        $files = $repo->files((int) $report['id']);

        return $this->view('reports/show', [
            'title' => $report['title'] . ' · AgroNegocios MX',
            'metaDescription' => excerpt((string) $report['summary'], 160),
            'report' => $report,
            'ratios' => Financials::ratios($report),
            'health' => Financials::health($report),
            'files' => $files,
            'comments' => $interactions->comments('reporte', (int) $report['id'], $user !== null && in_array($user['role'], (array) config('app.moderators', []), true)),
            'reviews' => $interactions->reviews('reporte', (int) $report['id']),
            'reacted' => $user !== null && $interactions->hasReacted('reporte', (int) $report['id'], (int) $user['id']),
            'bookmarked' => $user !== null && $interactions->isBookmarked('reporte', (int) $report['id'], (int) $user['id']),
            'reactionsCount' => $interactions->reactionsCount('reporte', (int) $report['id']),
            'preview' => $preview,
            'canEdit' => $user !== null && $this->canEditContent($report, $user),
            'canonical' => url('/reportes/' . $report['slug']),
            'robots' => $preview ? 'noindex,nofollow' : 'index,follow',
        ]);
    }

    public function create(Request $request): Response
    {
        $user = Auth::requireLogin();
        return $this->view('reports/form', $this->catalogData() + [
            'title' => 'Compartir un reporte financiero · AgroNegocios MX',
            'metaDescription' => 'Publica un estado de resultados, balance o análisis financiero con indicadores calculados automáticamente.',
            'report' => null,
            'user' => $user,
            'robots' => 'noindex,nofollow',
        ]);
    }

    public function store(Request $request): Response
    {
        $user = Auth::requireLogin();
        $action = $request->string('action', 'draft');
        [$data, $errors] = $this->validate($request, $action, $user);
        if ($errors !== []) {
            $this->withErrors($errors);
            $this->flashOld($data);
            return $this->redirect('/reportes/nuevo');
        }

        $data['author_id'] = (int) $user['id'];
        $data['status'] = $action === 'submit' ? 'en_revision' : 'borrador';
        $repo = new ReportRepository();
        $id = $repo->create($data);
        $this->saveReportFiles($id, $request);
        $this->audit($action === 'submit' ? 'reporte.enviado' : 'reporte.borrador', 'reporte', $id);
        Flash::success($action === 'submit' ? 'Tu reporte se envió al comité editorial.' : 'Guardamos el borrador del reporte.');
        $report = $repo->findById($id);
        return $this->redirect('/reportes/' . ($report['slug'] ?? ''));
    }

    public function edit(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $report = (new ReportRepository())->findBySlug($slug);
        if ($report === null) {
            $this->notFound('Este reporte no existe.');
        }
        $this->requireContentOwner($report, $user);
        return $this->view('reports/form', $this->catalogData() + [
            'title' => 'Editar reporte financiero · AgroNegocios MX',
            'report' => $report,
            'user' => $user,
            'robots' => 'noindex,nofollow',
        ]);
    }

    public function update(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $repo = new ReportRepository();
        $report = $repo->findBySlug($slug);
        if ($report === null) {
            $this->notFound('Este reporte no existe.');
        }
        $this->requireContentOwner($report, $user);

        $action = $request->string('action', 'draft');
        [$data, $errors] = $this->validate($request, $action, $user);
        if ($errors !== []) {
            $this->withErrors($errors);
            $this->flashOld($data);
            return $this->redirect('/reportes/' . $report['slug'] . '/editar');
        }

        $repo->update((int) $report['id'], $data);
        if ($action === 'submit') {
            $repo->updateStatus((int) $report['id'], 'en_revision');
        }
        $this->saveReportFiles((int) $report['id'], $request);
        $this->audit('reporte.actualizado', 'reporte', (int) $report['id']);
        Flash::success($action === 'submit' ? 'Tu reporte se envió de nuevo al comité.' : 'Guardamos los cambios.');
        return $this->redirect('/reportes/' . $report['slug']);
    }

    public function submit(Request $request, string $slug): Response
    {
        $user = Auth::requireVerified();
        $repo = new ReportRepository();
        $report = $repo->findBySlug($slug);
        if ($report === null) {
            $this->notFound('Este reporte no existe.');
        }
        $this->requireContentOwner($report, $user);
        $repo->updateStatus((int) $report['id'], 'en_revision');
        $this->audit('reporte.enviado', 'reporte', (int) $report['id']);
        Flash::success('Tu reporte se envió al comité editorial.');
        return $this->redirect('/reportes/' . $report['slug']);
    }

    public function comment(Request $request, string $slug): Response
    {
        $user = Auth::requireVerified();
        $report = (new ReportRepository())->findPublishedBySlug($slug);
        if ($report === null) {
            $this->notFound('Este reporte no está disponible para comentar.');
        }
        $body = trim($request->string('body'));
        if (mb_strlen($body) < 5) {
            Flash::error('Escribe un comentario de al menos 5 caracteres.');
            return $this->redirect('/reportes/' . $slug . '#comentarios');
        }
        (new InteractionRepository())->addComment('reporte', (int) $report['id'], (int) $user['id'], mb_substr($body, 0, 2000));
        Flash::success('Tu comentario se publicó.');
        return $this->redirect('/reportes/' . $slug . '#comentarios');
    }

    public function react(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $report = (new ReportRepository())->findPublishedBySlug($slug);
        if ($report === null) {
            $this->notFound('Este reporte no está disponible.');
        }
        $result = (new InteractionRepository())->toggleReaction('reporte', (int) $report['id'], (int) $user['id']);
        return $request->wantsJson() ? Response::json($result) : $this->redirect('/reportes/' . $slug);
    }

    public function bookmark(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $report = (new ReportRepository())->findPublishedBySlug($slug);
        if ($report === null) {
            $this->notFound('Este reporte no está disponible.');
        }
        $result = (new InteractionRepository())->toggleBookmark('reporte', (int) $report['id'], (int) $user['id']);
        if ($request->wantsJson()) {
            return Response::json($result);
        }
        Flash::success($result['active'] ? 'Guardaste este reporte.' : 'Quitamos el reporte de tus favoritos.');
        return $this->redirect('/reportes/' . $slug);
    }

    public function deleteFile(Request $request, string $slug, string $fileId): Response
    {
        $user = Auth::requireLogin();
        $repo = new ReportRepository();
        $report = $repo->findBySlug($slug);
        if ($report === null) {
            $this->notFound('Este reporte no existe.');
        }
        $this->requireContentOwner($report, $user);
        $path = $repo->deleteFile((int) $fileId, (int) $report['id']);
        if ($path !== null) {
            $file = rtrim((string) config('app.uploads.files_path'), '/') . '/' . ltrim($path, '/');
            if (is_file($file)) {
                @unlink($file);
            }
        }
        Flash::success('El archivo se eliminó.');
        return $this->redirect('/reportes/' . $slug . '/editar');
    }

    public function pdf(Request $request, string $slug): Response
    {
        $repo = new ReportRepository();
        $report = $repo->findPublishedBySlug($slug);
        if ($report === null) {
            $user = Auth::user();
            $report = $repo->findBySlug($slug);
            if ($report === null || $user === null || ((int) $report['author_id'] !== (int) $user['id'] && !in_array($user['role'], (array) config('app.moderators', []), true))) {
                $this->notFound('Este reporte no está disponible para descarga.');
            }
        }
        $content = ReportPdf::render($report, $repo->files((int) $report['id']));
        return new Response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="reporte-' . $report['slug'] . '.pdf"',
            'Content-Length' => (string) strlen($content),
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function validate(Request $request, string $action, array $user): array
    {
        $data = [
            'title' => trim($request->string('title')),
            'summary' => trim($request->string('summary')),
            'sector_id' => $request->int('sector_id'),
            'chain_id' => $request->int('chain_id'),
            'state_code' => $request->string('state_code'),
            'company_type' => $request->string('company_type', 'pequena'),
            'period_start' => $request->string('period_start'),
            'period_end' => $request->string('period_end'),
            'currency' => $request->string('currency', 'MXN'),
            'analysis' => trim($request->string('analysis')),
            'findings' => trim($request->string('findings')),
            'recommendations' => trim($request->string('recommendations')),
            'assumptions' => trim($request->string('assumptions')),
            'references_text' => trim($request->string('references_text')),
            'license' => $request->string('license', 'CC BY-NC-SA 4.0'),
        ];
        foreach (ReportRepository::NUMERIC_FIELDS as $field) {
            $data[$field] = $request->string($field);
        }
        $data = $this->normalizeNumbers($data, ReportRepository::NUMERIC_FIELDS);

        $validator = $this->validator($data, [
            'title' => 'título', 'summary' => 'resumen', 'sector_id' => 'sector',
            'state_code' => 'estado', 'period_start' => 'inicio del periodo',
            'period_end' => 'fin del periodo', 'revenue' => 'ingresos',
            'net_income' => 'utilidad neta', 'total_assets' => 'activo total',
        ]);
        $validator->validate([
            'title' => 'required|min:10|max:180',
            'summary' => 'required|min:30|max:600',
            'sector_id' => 'required|integer',
            'state_code' => 'required|min:2|max:5',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'revenue' => 'required|numeric',
            'net_income' => 'required|numeric',
            'total_assets' => 'required|numeric',
            'analysis' => 'required|min:50|max:8000',
            'findings' => 'required|min:30|max:6000',
            'recommendations' => 'required|min:30|max:6000',
            'assumptions' => 'nullable|max:4000',
            'references_text' => 'nullable|max:4000',
        ]);
        $errors = $validator->errors();

        if ($data['sector_id'] <= 0) {
            $errors['sector_id'] = 'Selecciona un sector.';
        }
        if ($data['period_start'] !== '' && $data['period_end'] !== '' && strtotime($data['period_start']) > strtotime($data['period_end'])) {
            $errors['period_end'] = 'El fin del periodo debe ser posterior al inicio.';
        }
        if ((float) $data['revenue'] < 0) {
            $errors['revenue'] = 'Los ingresos no pueden ser negativos.';
        }
        if ($action === 'submit' && $user['status'] !== 'verificado') {
            $errors['account'] = 'Para enviar a revisión necesitas una cuenta verificada.';
        }

        return [$data, $errors];
    }
}
