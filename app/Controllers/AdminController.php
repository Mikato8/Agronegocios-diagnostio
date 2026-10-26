<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\AdminRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\CaseRepository;
use App\Repositories\InteractionRepository;
use App\Repositories\OpportunityRepository;
use App\Repositories\ReportRepository;
use App\Repositories\UserRepository;

final class AdminController extends BaseController
{
    public function dashboard(Request $request): Response
    {
        Auth::requireModerator();
        $admin = new AdminRepository();
        return $this->view('admin/dashboard', [
            'title' => 'Resumen editorial',
            'stats' => $admin->stats(),
            'queue' => $admin->pendingQueue(12),
            'comments' => (new InteractionRepository())->recentComments(8),
            'audit' => (new AuditLogRepository())->recent(10),
            'counts' => $admin->contentCounts(),
        ], 'admin');
    }

    public function contents(Request $request): Response
    {
        Auth::requireModerator();
        $type = $request->string('tipo', 'caso');
        if (!in_array($type, ['caso', 'reporte', 'oportunidad'], true)) {
            $type = 'caso';
        }
        $status = $request->string('estado', 'en_revision');
        $q = trim($request->string('q'));
        $page = max(1, $request->int('page', 1));
        $perPage = (int) config('app.pagination.admin', 20);

        $repo = match ($type) {
            'reporte' => new ReportRepository(),
            'oportunidad' => new OpportunityRepository(),
            default => new CaseRepository(),
        };
        $result = $repo->search(['status' => $status, 'q' => $q], $page, $perPage, true);

        return $this->view('admin/contents', [
            'title' => 'Contenido editorial',
            'type' => $type,
            'status' => $status,
            'q' => $q,
            'items' => $result['items'],
            'total' => $result['total'],
            'paginator' => new Paginator($result['total'], $perPage, $page),
        ], 'admin');
    }

    public function review(Request $request, string $type, string $id): Response
    {
        Auth::requireModerator();
        if (!in_array($type, ['caso', 'reporte', 'oportunidad'], true)) {
            $this->notFound('Tipo de contenido no válido.');
        }
        $admin = new AdminRepository();
        $content = $admin->content($type, (int) $id);
        if ($content === null) {
            $this->notFound('El contenido no existe.');
        }
        $interactions = new InteractionRepository();
        $repo = match ($type) {
            'reporte' => new ReportRepository(),
            'oportunidad' => new OpportunityRepository(),
            default => new CaseRepository(),
        };

        return $this->view('admin/review', [
            'title' => 'Revisar ' . content_type_short($type),
            'type' => $type,
            'content' => $content,
            'reviews' => $interactions->reviews($type, (int) $content['id']),
            'comments' => $interactions->comments($type, (int) $content['id'], true),
            'files' => $type === 'reporte' ? (new ReportRepository())->files((int) $content['id']) : [],
            'interests' => $type === 'oportunidad' ? (new OpportunityRepository())->interestsFor((int) $content['id']) : [],
        ], 'admin');
    }

    public function reviewPost(Request $request, string $type, string $id): Response
    {
        $user = Auth::requireModerator();
        if (!in_array($type, ['caso', 'reporte', 'oportunidad'], true)) {
            $this->notFound('Tipo de contenido no válido.');
        }
        $decision = $request->string('decision');
        if (!in_array($decision, ['publicar', 'cambios', 'rechazar'], true)) {
            Flash::error('Selecciona una decisión editorial válida.');
            return $this->redirect('/admin/revision/' . $type . '/' . (int) $id);
        }
        $notes = trim($request->string('notes'));
        if ($decision !== 'publicar' && mb_strlen($notes) < 10) {
            Flash::error('Explica al autor qué debe cambiar o por qué se rechaza (mínimo 10 caracteres).');
            return $this->redirect('/admin/revision/' . $type . '/' . (int) $id);
        }

        $featured = (bool) $request->post('featured');
        $status = (new AdminRepository())->review($type, (int) $id, (int) $user['id'], $decision, $notes, $featured);
        Flash::success('Decisión registrada: ' . status_label($status) . '.');
        return $this->redirect('/admin/contenidos?tipo=' . $type . '&estado=en_revision');
    }

    public function feature(Request $request, string $type, string $id): Response
    {
        Auth::requireModerator();
        $featured = (bool) $request->post('featured');
        (new AdminRepository())->toggleFeatured($type, (int) $id, $featured);
        Flash::success($featured ? 'El contenido se destacó en la portada.' : 'El contenido dejó de estar destacado.');
        return $this->back('/admin');
    }

    public function users(Request $request): Response
    {
        Auth::requireAdmin();
        $filters = [
            'q' => trim($request->string('q')),
            'status' => $request->string('status'),
            'role' => $request->string('role'),
        ];
        $page = max(1, $request->int('page', 1));
        $perPage = (int) config('app.pagination.admin', 20);
        $repo = new UserRepository();
        $result = $repo->search($filters, $page, $perPage);

        return $this->view('admin/users', [
            'title' => 'Usuarios y verificación',
            'items' => $result['items'],
            'total' => $result['total'],
            'filters' => $filters,
            'paginator' => new Paginator($result['total'], $perPage, $page),
            'stats' => $repo->stats(),
        ], 'admin');
    }

    public function user(Request $request, string $id): Response
    {
        Auth::requireAdmin();
        $repo = new UserRepository();
        $user = $repo->find((int) $id);
        if ($user === null) {
            $this->notFound('El usuario no existe.');
        }
        return $this->view('admin/user', [
            'title' => 'Cuenta de ' . $user['name'],
            'user' => $user,
            'activity' => $repo->activity((int) $user['id']),
            'proof' => $user['proof_path'] ? url('/admin/constancias/' . $user['proof_path']) : null,
        ], 'admin');
    }

    public function userAction(Request $request, string $id): Response
    {
        $actor = Auth::requireAdmin();
        $repo = new UserRepository();
        $user = $repo->find((int) $id);
        if ($user === null) {
            $this->notFound('El usuario no existe.');
        }
        if ((int) $user['id'] === (int) $actor['id'] && $request->string('action') !== 'verify') {
            Flash::error('No puedes suspender ni cambiar el rol de tu propia cuenta.');
            return $this->redirect('/admin/usuarios/' . (int) $id);
        }

        $action = $request->string('action');
        $note = trim($request->string('note'));
        switch ($action) {
            case 'verify':
                $repo->verify((int) $id, $note !== '' ? $note : 'Verificación manual aprobada.');
                $this->audit('usuario.verificado', 'usuario', (int) $id);
                Flash::success('La cuenta quedó verificada.');
                break;
            case 'suspend':
                $repo->setStatus((int) $id, 'suspendido', $note);
                $this->audit('usuario.suspendido', 'usuario', (int) $id);
                Flash::success('La cuenta quedó suspendida.');
                break;
            case 'reactivate':
                $repo->setStatus((int) $id, 'verificado', $note);
                $this->audit('usuario.reactivado', 'usuario', (int) $id);
                Flash::success('La cuenta se reactivó.');
                break;
            case 'role':
                $role = $request->string('role');
                if (!in_array($role, ['estudiante', 'docente', 'editor', 'admin'], true)) {
                    Flash::error('Rol no válido.');
                    break;
                }
                $repo->setRole((int) $id, $role);
                $this->audit('usuario.rol', 'usuario', (int) $id, ['role' => $role]);
                Flash::success('El rol se actualizó.');
                break;
            default:
                Flash::error('Acción no reconocida.');
        }
        return $this->redirect('/admin/usuarios/' . (int) $id);
    }

    public function comments(Request $request): Response
    {
        Auth::requireModerator();
        return $this->view('admin/comments', [
            'title' => 'Moderación de comentarios',
            'comments' => (new AdminRepository())->comments(80),
        ], 'admin');
    }

    public function commentAction(Request $request, string $id): Response
    {
        Auth::requireModerator();
        $action = $request->string('action');
        $repo = new InteractionRepository();
        if ($action === 'hide') {
            $repo->hideComment((int) $id);
            Flash::success('El comentario se ocultó.');
        } elseif ($action === 'show') {
            $repo->showComment((int) $id);
            Flash::success('El comentario se volvió a mostrar.');
        }
        return $this->back('/admin/comentarios');
    }

    public function auditLog(Request $request): Response
    {
        Auth::requireAdmin();
        return $this->view('admin/audit', [
            'title' => 'Registro de auditoría',
            'entries' => (new AuditLogRepository())->recent(120),
        ], 'admin');
    }

    public function proof(Request $request, string $userId, string $file): Response
    {
        Auth::requireAdmin();
        $userId = (int) $userId;
        $file = basename($file);
        $path = rtrim((string) config('app.uploads.proofs_path'), '/') . '/' . $userId . '/' . $file;
        if (!is_file($path)) {
            $this->notFound('La constancia no existe.');
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($path);
        if (!in_array($mime, ['application/pdf', 'image/jpeg', 'image/png'], true)) {
            $this->notFound('Archivo no permitido.');
        }
        $body = (string) file_get_contents($path);
        return new Response($body, 200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) strlen($body),
            'Content-Disposition' => 'inline; filename="constancia-' . $userId . '.' . pathinfo($file, PATHINFO_EXTENSION) . '"',
            'Cache-Control' => 'private, no-store',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }
}
