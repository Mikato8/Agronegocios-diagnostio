<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Core\Upload;
use App\Repositories\CaseRepository;
use App\Repositories\CatalogRepository;
use App\Repositories\InteractionRepository;
use App\Support\CasePdf;

final class CaseController extends BaseController
{
    public function index(Request $request): Response
    {
        $filters = [
            'q' => trim($request->string('q')),
            'sector' => $request->string('sector'),
            'chain' => $request->string('chain'),
            'state' => $request->string('state'),
            'stage' => $request->string('stage'),
            'company_type' => $request->string('company_type'),
            'year' => $request->int('year'),
            'sort' => $request->string('sort', 'recientes'),
        ];
        $page = max(1, $request->int('page', 1));
        $perPage = (int) config('app.pagination.cases', 9);
        $repo = new CaseRepository();
        $result = $repo->search($filters, $page, $perPage);
        $catalog = new CatalogRepository();

        return $this->view('cases/index', [
            'title' => 'Casos de agronegocios · AgroNegocios MX',
            'metaDescription' => 'Casos documentados por estudiantes de agronegocios: contexto, reto, diagnóstico, estrategia y resultados de empresas y proyectos del campo mexicano.',
            'cases' => $result['items'],
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
        $repo = new CaseRepository();
        $case = $repo->findPublishedBySlug($slug);
        $preview = false;

        if ($case === null) {
            $user = Auth::user();
            $case = $repo->findBySlug($slug);
            if ($case !== null && $user !== null && ((int) $case['author_id'] === (int) $user['id'] || in_array($user['role'], (array) config('app.moderators', []), true))) {
                $preview = true;
            } else {
                $case = null;
            }
        }

        if ($case === null) {
            $this->notFound('Este caso no existe o aún no se ha publicado.');
        }

        if (!$preview) {
            $repo->incrementViews((int) $case['id']);
            $case['views'] = (int) $case['views'] + 1;
        }

        $user = Auth::user();
        $interactions = new InteractionRepository();
        $comments = $interactions->comments('caso', (int) $case['id'], $user !== null && in_array($user['role'], (array) config('app.moderators', []), true));
        $reacted = $user !== null && $interactions->hasReacted('caso', (int) $case['id'], (int) $user['id']);
        $bookmarked = $user !== null && $interactions->isBookmarked('caso', (int) $case['id'], (int) $user['id']);
        $reviews = $interactions->reviews('caso', (int) $case['id']);
        $similar = $repo->similar($case, 3);

        return $this->view('cases/show', [
            'title' => $case['title'] . ' · AgroNegocios MX',
            'metaDescription' => excerpt((string) $case['summary'], 160),
            'case' => $case,
            'comments' => $comments,
            'reviews' => $reviews,
            'similar' => $similar,
            'reacted' => $reacted,
            'bookmarked' => $bookmarked,
            'reactionsCount' => $interactions->reactionsCount('caso', (int) $case['id']),
            'preview' => $preview,
            'canEdit' => $user !== null && $this->canEditContent($case, $user),
            'canModerate' => $user !== null && in_array($user['role'], (array) config('app.moderators', []), true),
            'canonical' => url('/casos/' . $case['slug']),
            'robots' => $preview ? 'noindex,nofollow' : 'index,follow',
        ]);
    }

    public function create(Request $request): Response
    {
        $user = Auth::requireLogin();
        return $this->view('cases/form', $this->catalogData() + [
            'title' => 'Compartir un caso · AgroNegocios MX',
            'metaDescription' => 'Documenta un caso de agronegocios con contexto, reto, diagnóstico, estrategia y resultados.',
            'case' => null,
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
            return $this->redirect('/casos/nuevo');
        }

        $data['author_id'] = (int) $user['id'];
        $data['status'] = $action === 'submit' ? 'en_revision' : 'borrador';
        $repo = new CaseRepository();
        $id = $repo->create($data);
        $this->saveCaseImages($id, $request);
        $this->audit($action === 'submit' ? 'caso.enviado' : 'caso.borrador', 'caso', $id);
        Flash::success($action === 'submit' ? 'Tu caso se envió al comité editorial.' : 'Guardamos tu borrador. Puedes completarlo cuando quieras.');

        $case = $repo->findById($id);
        return $this->redirect('/casos/' . ($case['slug'] ?? ''));
    }

    public function edit(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $case = (new CaseRepository())->findBySlug($slug);
        if ($case === null) {
            $this->notFound('Este caso no existe.');
        }
        $this->requireContentOwner($case, $user);

        return $this->view('cases/form', $this->catalogData() + [
            'title' => 'Editar caso · AgroNegocios MX',
            'case' => $case,
            'user' => $user,
            'robots' => 'noindex,nofollow',
        ]);
    }

    public function update(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $repo = new CaseRepository();
        $case = $repo->findBySlug($slug);
        if ($case === null) {
            $this->notFound('Este caso no existe.');
        }
        $this->requireContentOwner($case, $user);

        $action = $request->string('action', 'draft');
        [$data, $errors] = $this->validate($request, $action, $user);
        if ($errors !== []) {
            $this->withErrors($errors);
            $this->flashOld($data);
            return $this->redirect('/casos/' . $case['slug'] . '/editar');
        }

        $repo->update((int) $case['id'], $data);
        if ($action === 'submit') {
            $repo->updateStatus((int) $case['id'], 'en_revision');
        }
        $this->saveCaseImages((int) $case['id'], $request);
        $this->audit('caso.actualizado', 'caso', (int) $case['id']);
        Flash::success($action === 'submit' ? 'Tu caso se envió de nuevo al comité editorial.' : 'Guardamos los cambios.');
        return $this->redirect('/casos/' . $case['slug']);
    }

    public function submit(Request $request, string $slug): Response
    {
        $user = Auth::requireVerified();
        $repo = new CaseRepository();
        $case = $repo->findBySlug($slug);
        if ($case === null) {
            $this->notFound('Este caso no existe.');
        }
        $this->requireContentOwner($case, $user);
        $repo->updateStatus((int) $case['id'], 'en_revision');
        $this->audit('caso.enviado', 'caso', (int) $case['id']);
        Flash::success('Tu caso se envió al comité editorial. Te avisaremos cuando haya una decisión.');
        return $this->redirect('/casos/' . $case['slug']);
    }

    public function comment(Request $request, string $slug): Response
    {
        $user = Auth::requireVerified();
        $case = (new CaseRepository())->findPublishedBySlug($slug);
        if ($case === null) {
            $this->notFound('Este caso no está disponible para comentar.');
        }
        $body = trim($request->string('body'));
        if (mb_strlen($body) < 5) {
            Flash::error('Escribe un comentario de al menos 5 caracteres.');
            return $this->redirect('/casos/' . $slug . '#comentarios');
        }
        if (mb_strlen($body) > 2000) {
            Flash::error('El comentario no debe exceder 2,000 caracteres.');
            return $this->redirect('/casos/' . $slug . '#comentarios');
        }
        (new InteractionRepository())->addComment('caso', (int) $case['id'], (int) $user['id'], $body);
        $this->audit('comentario.caso', 'caso', (int) $case['id']);
        Flash::success('Tu comentario se publicó.');
        return $this->redirect('/casos/' . $slug . '#comentarios');
    }

    public function react(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $case = (new CaseRepository())->findPublishedBySlug($slug);
        if ($case === null) {
            $this->notFound('Este caso no está disponible.');
        }
        $result = (new InteractionRepository())->toggleReaction('caso', (int) $case['id'], (int) $user['id']);
        if ($request->wantsJson()) {
            return Response::json($result);
        }
        return $this->redirect('/casos/' . $slug);
    }

    public function bookmark(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $case = (new CaseRepository())->findPublishedBySlug($slug);
        if ($case === null) {
            $this->notFound('Este caso no está disponible.');
        }
        $result = (new InteractionRepository())->toggleBookmark('caso', (int) $case['id'], (int) $user['id']);
        if ($request->wantsJson()) {
            return Response::json($result);
        }
        Flash::success($result['active'] ? 'Guardaste este caso en tus favoritos.' : 'Quitamos este caso de tus favoritos.');
        return $this->redirect('/casos/' . $slug);
    }

    public function deleteImage(Request $request, string $slug, string $imageId): Response
    {
        $user = Auth::requireLogin();
        $repo = new CaseRepository();
        $case = $repo->findBySlug($slug);
        if ($case === null) {
            $this->notFound('Este caso no existe.');
        }
        $this->requireContentOwner($case, $user);
        $path = $repo->deleteImage((int) $imageId, (int) $case['id']);
        if ($path !== null) {
            $file = rtrim((string) config('app.uploads.images_path'), '/') . '/' . ltrim($path, '/');
            if (is_file($file)) {
                @unlink($file);
            }
        }
        Flash::success('La imagen se eliminó.');
        return $this->redirect('/casos/' . $slug . '/editar');
    }

    public function pdf(Request $request, string $slug): Response
    {
        $repo = new CaseRepository();
        $case = $repo->findPublishedBySlug($slug);
        if ($case === null) {
            $user = Auth::user();
            $case = $repo->findBySlug($slug);
            if ($case === null || $user === null || ((int) $case['author_id'] !== (int) $user['id'] && !in_array($user['role'], (array) config('app.moderators', []), true))) {
                $this->notFound('Este caso no está disponible para descarga.');
            }
        }
        $content = CasePdf::render($case);
        return new Response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="caso-' . $case['slug'] . '.pdf"',
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
            'municipality' => trim($request->string('municipality')),
            'company_type' => $request->string('company_type', 'pequena'),
            'stage' => $request->string('stage', 'operacion'),
            'year' => $request->int('year', (int) date('Y')),
            'challenge' => trim($request->string('challenge')),
            'context' => trim($request->string('context')),
            'diagnosis' => trim($request->string('diagnosis')),
            'strategy' => trim($request->string('strategy')),
            'results' => trim($request->string('results')),
            'lessons' => trim($request->string('lessons')),
            'financial_highlights' => trim($request->string('financial_highlights')),
            'kpis' => trim($request->string('kpis')),
            'references_text' => trim($request->string('references_text')),
            'license' => $request->string('license', 'CC BY-NC-SA 4.0'),
        ];

        $validator = $this->validator($data, [
            'title' => 'título', 'summary' => 'resumen', 'sector_id' => 'sector',
            'state_code' => 'estado', 'year' => 'año', 'challenge' => 'reto',
            'context' => 'contexto', 'diagnosis' => 'diagnóstico', 'strategy' => 'estrategia',
            'results' => 'resultados', 'lessons' => 'aprendizajes',
        ]);
        $validator->validate([
            'title' => 'required|min:10|max:180',
            'summary' => 'required|min:30|max:600',
            'sector_id' => 'required|integer',
            'state_code' => 'required|min:2|max:5',
            'year' => 'required|integer|between:2000,2100',
            'challenge' => 'required|min:40|max:6000',
            'context' => 'required|min:40|max:8000',
            'diagnosis' => 'required|min:40|max:8000',
            'strategy' => 'required|min:40|max:8000',
            'results' => 'required|min:30|max:6000',
            'lessons' => 'required|min:30|max:6000',
            'municipality' => 'nullable|max:120',
            'financial_highlights' => 'nullable|max:4000',
            'kpis' => 'nullable|max:3000',
            'references_text' => 'nullable|max:4000',
        ]);
        $errors = $validator->errors();
        if ($data['sector_id'] <= 0) {
            $errors['sector_id'] = 'Selecciona un sector.';
        }

        if ($action === 'submit' && $user['status'] !== 'verificado') {
            $errors['account'] = 'Para enviar a revisión necesitas una cuenta verificada. Sube tu constancia de estudios en tu perfil.';
        }

        return [$data, $errors];
    }
}
