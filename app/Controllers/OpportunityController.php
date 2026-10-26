<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\CatalogRepository;
use App\Repositories\InteractionRepository;
use App\Repositories\OpportunityRepository;
use App\Support\OpportunityPdf;

final class OpportunityController extends BaseController
{
    public function index(Request $request): Response
    {
        $filters = [
            'q' => trim($request->string('q')),
            'sector' => $request->string('sector'),
            'chain' => $request->string('chain'),
            'state' => $request->string('state'),
            'type' => $request->string('type'),
            'stage' => $request->string('stage'),
            'investment_min' => $request->int('investment_min'),
            'investment_max' => $request->int('investment_max'),
            'sort' => $request->string('sort', 'recientes'),
            'inversion' => $request->string('inversion'),
        ];
        $range = $request->string('inversion');
        if (preg_match('/^(\d+)-(\d*)$/', $range, $m)) {
            $filters['investment_min'] = (int) $m[1];
            $filters['investment_max'] = (int) ($m[2] ?? 0);
        }
        $page = max(1, $request->int('page', 1));
        $perPage = (int) config('app.pagination.opportunities', 9);
        $repo = new OpportunityRepository();
        $result = $repo->search($filters, $page, $perPage);
        $catalog = new CatalogRepository();

        return $this->view('opportunities/index', [
            'title' => 'Oportunidades de agronegocios · AgroNegocios MX',
            'metaDescription' => 'Oportunidades de producción, comercialización, agroindustria, exportación y servicios en el campo mexicano. Conecta con quienes las promueven.',
            'opportunities' => $result['items'],
            'total' => $result['total'],
            'filters' => $filters,
            'paginator' => new Paginator($result['total'], $perPage, $page),
            'sectorsList' => $catalog->sectors(),
            'chainsList' => $catalog->chains(),
            'states' => $catalog->states(),
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $repo = new OpportunityRepository();
        $opportunity = $repo->findPublishedBySlug($slug);
        $preview = false;
        if ($opportunity === null) {
            $user = Auth::user();
            $opportunity = $repo->findBySlug($slug);
            if ($opportunity !== null && $user !== null && ((int) $opportunity['author_id'] === (int) $user['id'] || in_array($user['role'], (array) config('app.moderators', []), true))) {
                $preview = true;
            } else {
                $opportunity = null;
            }
        }
        if ($opportunity === null) {
            $this->notFound('Esta oportunidad no existe o aún no se ha publicado.');
        }

        if (!$preview) {
            $repo->incrementViews((int) $opportunity['id']);
            $opportunity['views'] = (int) $opportunity['views'] + 1;
        }

        $user = Auth::user();
        $interactions = new InteractionRepository();
        $isOwner = $user !== null && (int) $opportunity['author_id'] === (int) $user['id'];
        $canModerate = $user !== null && in_array($user['role'], (array) config('app.moderators', []), true);
        $related = $repo->search(['sector' => $opportunity['sector_code']], 1, 3)['items'];
        $related = array_values(array_filter($related, static fn (array $item): bool => (int) $item['id'] !== (int) $opportunity['id']));

        return $this->view('opportunities/show', [
            'title' => $opportunity['title'] . ' · AgroNegocios MX',
            'metaDescription' => excerpt((string) $opportunity['summary'], 160),
            'opportunity' => $opportunity,
            'related' => array_slice($related, 0, 3),
            'comments' => $interactions->comments('oportunidad', (int) $opportunity['id'], $canModerate),
            'reviews' => $interactions->reviews('oportunidad', (int) $opportunity['id']),
            'reacted' => $user !== null && $interactions->hasReacted('oportunidad', (int) $opportunity['id'], (int) $user['id']),
            'bookmarked' => $user !== null && $interactions->isBookmarked('oportunidad', (int) $opportunity['id'], (int) $user['id']),
            'reactionsCount' => $interactions->reactionsCount('oportunidad', (int) $opportunity['id']),
            'hasInterest' => $user !== null && $repo->hasInterest((int) $opportunity['id'], (int) $user['id']),
            'interests' => ($isOwner || $canModerate) ? $repo->interestsFor((int) $opportunity['id']) : [],
            'canManage' => $isOwner || $canModerate,
            'preview' => $preview,
            'canEdit' => $user !== null && $this->canEditContent($opportunity, $user),
            'canonical' => url('/oportunidades/' . $opportunity['slug']),
            'robots' => $preview ? 'noindex,nofollow' : 'index,follow',
        ]);
    }

    public function create(Request $request): Response
    {
        $user = Auth::requireLogin();
        return $this->view('opportunities/form', $this->catalogData() + [
            'title' => 'Publicar una oportunidad · AgroNegocios MX',
            'metaDescription' => 'Publica una oportunidad de agronegocios con mercado, inversión, requisitos y riesgos.',
            'opportunity' => null,
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
            return $this->redirect('/oportunidades/nueva');
        }

        $data['author_id'] = (int) $user['id'];
        $data['status'] = $action === 'submit' ? 'en_revision' : 'borrador';
        $repo = new OpportunityRepository();
        $id = $repo->create($data);
        $this->audit($action === 'submit' ? 'oportunidad.enviada' : 'oportunidad.borrador', 'oportunidad', $id);
        Flash::success($action === 'submit' ? 'Tu oportunidad se envió al comité editorial.' : 'Guardamos el borrador de la oportunidad.');
        $opportunity = $repo->findById($id);
        return $this->redirect('/oportunidades/' . ($opportunity['slug'] ?? ''));
    }

    public function edit(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $opportunity = (new OpportunityRepository())->findBySlug($slug);
        if ($opportunity === null) {
            $this->notFound('Esta oportunidad no existe.');
        }
        $this->requireContentOwner($opportunity, $user);
        return $this->view('opportunities/form', $this->catalogData() + [
            'title' => 'Editar oportunidad · AgroNegocios MX',
            'opportunity' => $opportunity,
            'user' => $user,
            'robots' => 'noindex,nofollow',
        ]);
    }

    public function update(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $repo = new OpportunityRepository();
        $opportunity = $repo->findBySlug($slug);
        if ($opportunity === null) {
            $this->notFound('Esta oportunidad no existe.');
        }
        $this->requireContentOwner($opportunity, $user);

        $action = $request->string('action', 'draft');
        [$data, $errors] = $this->validate($request, $action, $user);
        if ($errors !== []) {
            $this->withErrors($errors);
            $this->flashOld($data);
            return $this->redirect('/oportunidades/' . $opportunity['slug'] . '/editar');
        }

        $repo->update((int) $opportunity['id'], $data);
        if ($action === 'submit') {
            $repo->updateStatus((int) $opportunity['id'], 'en_revision');
        }
        $this->audit('oportunidad.actualizada', 'oportunidad', (int) $opportunity['id']);
        Flash::success($action === 'submit' ? 'Tu oportunidad se envió de nuevo al comité.' : 'Guardamos los cambios.');
        return $this->redirect('/oportunidades/' . $opportunity['slug']);
    }

    public function submit(Request $request, string $slug): Response
    {
        $user = Auth::requireVerified();
        $repo = new OpportunityRepository();
        $opportunity = $repo->findBySlug($slug);
        if ($opportunity === null) {
            $this->notFound('Esta oportunidad no existe.');
        }
        $this->requireContentOwner($opportunity, $user);
        $repo->updateStatus((int) $opportunity['id'], 'en_revision');
        $this->audit('oportunidad.enviada', 'oportunidad', (int) $opportunity['id']);
        Flash::success('Tu oportunidad se envió al comité editorial.');
        return $this->redirect('/oportunidades/' . $opportunity['slug']);
    }

    public function interest(Request $request, string $slug): Response
    {
        $user = Auth::requireVerified();
        $repo = new OpportunityRepository();
        $opportunity = $repo->findPublishedBySlug($slug);
        if ($opportunity === null) {
            $this->notFound('Esta oportunidad no está disponible.');
        }
        if ((int) $opportunity['author_id'] === (int) $user['id']) {
            Flash::info('Eres quien publica esta oportunidad.');
            return $this->redirect('/oportunidades/' . $slug);
        }

        $message = trim($request->string('message'));
        if (mb_strlen($message) < 20) {
            Flash::error('Cuéntale al autor por qué te interesa (mínimo 20 caracteres).');
            return $this->redirect('/oportunidades/' . $slug . '#interes');
        }

        $repo->registerInterest((int) $opportunity['id'], (int) $user['id'], mb_substr($message, 0, 1200));
        $this->audit('oportunidad.interes', 'oportunidad', (int) $opportunity['id']);
        Flash::success('Enviamos tu expresión de interés. El autor podrá contactarte por correo.');
        return $this->redirect('/oportunidades/' . $slug . '#interes');
    }

    public function updateInterest(Request $request, string $slug, string $interestId): Response
    {
        $user = Auth::requireLogin();
        $repo = new OpportunityRepository();
        $opportunity = $repo->findBySlug($slug);
        if ($opportunity === null) {
            $this->notFound('Esta oportunidad no existe.');
        }
        if ((int) $opportunity['author_id'] !== (int) $user['id'] && !in_array($user['role'], (array) config('app.moderators', []), true)) {
            $this->forbidden();
        }
        $belongs = (int) Database::value('select opportunity_id from interests where id = $1', [(int) $interestId]);
        if ($belongs !== (int) $opportunity['id']) {
            $this->notFound('La expresión de interés no corresponde a esta oportunidad.');
        }
        $repo->updateInterestStatus((int) $interestId, $request->string('status'));
        Flash::success('Actualizamos el estado del interés.');
        return $this->redirect('/oportunidades/' . $slug . '#interes');
    }

    public function comment(Request $request, string $slug): Response
    {
        $user = Auth::requireVerified();
        $opportunity = (new OpportunityRepository())->findPublishedBySlug($slug);
        if ($opportunity === null) {
            $this->notFound('Esta oportunidad no está disponible para comentar.');
        }
        $body = trim($request->string('body'));
        if (mb_strlen($body) < 5) {
            Flash::error('Escribe un comentario de al menos 5 caracteres.');
            return $this->redirect('/oportunidades/' . $slug . '#comentarios');
        }
        (new InteractionRepository())->addComment('oportunidad', (int) $opportunity['id'], (int) $user['id'], mb_substr($body, 0, 2000));
        Flash::success('Tu comentario se publicó.');
        return $this->redirect('/oportunidades/' . $slug . '#comentarios');
    }

    public function react(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $opportunity = (new OpportunityRepository())->findPublishedBySlug($slug);
        if ($opportunity === null) {
            $this->notFound('Esta oportunidad no está disponible.');
        }
        $result = (new InteractionRepository())->toggleReaction('oportunidad', (int) $opportunity['id'], (int) $user['id']);
        return $request->wantsJson() ? Response::json($result) : $this->redirect('/oportunidades/' . $slug);
    }

    public function bookmark(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $opportunity = (new OpportunityRepository())->findPublishedBySlug($slug);
        if ($opportunity === null) {
            $this->notFound('Esta oportunidad no está disponible.');
        }
        $result = (new InteractionRepository())->toggleBookmark('oportunidad', (int) $opportunity['id'], (int) $user['id']);
        if ($request->wantsJson()) {
            return Response::json($result);
        }
        Flash::success($result['active'] ? 'Guardaste esta oportunidad.' : 'Quitamos la oportunidad de tus favoritos.');
        return $this->redirect('/oportunidades/' . $slug);
    }

    public function pdf(Request $request, string $slug): Response
    {
        $repo = new OpportunityRepository();
        $opportunity = $repo->findPublishedBySlug($slug);
        if ($opportunity === null) {
            $user = Auth::user();
            $opportunity = $repo->findBySlug($slug);
            if ($opportunity === null || $user === null || ((int) $opportunity['author_id'] !== (int) $user['id'] && !in_array($user['role'], (array) config('app.moderators', []), true))) {
                $this->notFound('Esta oportunidad no está disponible para descarga.');
            }
        }
        $content = OpportunityPdf::render($opportunity);
        return new Response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="oportunidad-' . $opportunity['slug'] . '.pdf"',
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
            'opportunity_type' => $request->string('opportunity_type', 'produccion'),
            'stage' => $request->string('stage', 'idea'),
            'investment_min' => $request->string('investment_min'),
            'investment_max' => $request->string('investment_max'),
            'currency' => $request->string('currency', 'MXN'),
            'expected_return_pct' => $request->string('expected_return_pct'),
            'payback_months' => $request->string('payback_months'),
            'market_size' => trim($request->string('market_size')),
            'description' => trim($request->string('description')),
            'value_proposition' => trim($request->string('value_proposition')),
            'requirements' => trim($request->string('requirements')),
            'risks' => trim($request->string('risks')),
            'contact_name' => trim($request->string('contact_name')),
            'contact_email' => trim($request->string('contact_email')),
            'contact_phone' => trim($request->string('contact_phone')),
            'is_confidential' => (bool) $request->post('is_confidential'),
            'expires_at' => $request->string('expires_at'),
            'license' => $request->string('license', 'CC BY-NC-SA 4.0'),
        ];
        $data = $this->normalizeNumbers($data, ['investment_min', 'investment_max', 'expected_return_pct']);

        $validator = $this->validator($data, [
            'title' => 'título', 'summary' => 'resumen', 'sector_id' => 'sector',
            'state_code' => 'estado', 'description' => 'descripción',
            'value_proposition' => 'propuesta de valor', 'requirements' => 'requisitos',
            'contact_name' => 'nombre de contacto', 'contact_email' => 'correo de contacto',
        ]);
        $validator->validate([
            'title' => 'required|min:10|max:180',
            'summary' => 'required|min:30|max:600',
            'sector_id' => 'required|integer',
            'state_code' => 'required|min:2|max:5',
            'description' => 'required|min:60|max:8000',
            'value_proposition' => 'required|min:30|max:4000',
            'requirements' => 'required|min:20|max:4000',
            'risks' => 'nullable|max:4000',
            'market_size' => 'nullable|max:2000',
            'contact_name' => 'required|min:3|max:120',
            'contact_email' => 'required|email|max:180',
            'contact_phone' => 'nullable|max:40',
            'payback_months' => 'nullable|integer|between:1,240',
            'expires_at' => 'nullable|date',
            'investment_min' => 'nullable|numeric',
            'investment_max' => 'nullable|numeric',
            'expected_return_pct' => 'nullable|numeric|between:0,300',
        ]);
        $errors = $validator->errors();

        if ($data['sector_id'] <= 0) {
            $errors['sector_id'] = 'Selecciona un sector.';
        }
        if ($data['investment_min'] !== null && $data['investment_max'] !== null && $data['investment_min'] > $data['investment_max']) {
            $errors['investment_max'] = 'La inversión máxima debe ser mayor o igual a la mínima.';
        }
        if ($data['expires_at'] !== '' && strtotime($data['expires_at']) < strtotime('today')) {
            $errors['expires_at'] = 'La fecha de cierre debe ser futura.';
        }
        if ($action === 'submit' && $user['status'] !== 'verificado') {
            $errors['account'] = 'Para enviar a revisión necesitas una cuenta verificada.';
        }

        return [$data, $errors];
    }
}
