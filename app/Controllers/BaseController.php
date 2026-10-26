<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Upload;
use App\Core\Validator;
use App\Core\View;
use App\Repositories\AuditLogRepository;
use App\Repositories\CatalogRepository;

abstract class BaseController
{
    /** @param array<string, mixed> $data */
    protected function view(string $view, array $data = [], string $layout = 'app'): Response
    {
        $data['title'] = $data['title'] ?? (string) config('app.name');
        $data['metaDescription'] = $data['metaDescription'] ?? 'Casos, reportes financieros y oportunidades de agronegocios creados por estudiantes de universidades de México.';
        $data['canonical'] = $data['canonical'] ?? url(current_path());
        return Response::html(View::render($view, $data, $layout));
    }

    protected function redirect(string $path, int $status = 302): Response
    {
        return Response::redirect(url($path), $status);
    }

    protected function back(string $fallback = '/'): Response
    {
        return Response::redirect(Request::capture()->previousUrl() ?: url($fallback));
    }

    protected function notFound(string $message = 'No encontramos lo que buscabas.'): never
    {
        throw new HttpException(404, $message);
    }

    protected function forbidden(string $message = 'No tienes permisos para esta acción.'): never
    {
        throw new HttpException(403, $message);
    }

    /** @param array<string, mixed> $data
     *  @param array<string, string> $rules
     *  @param array<string, string> $labels
     */
    protected function validator(array $data, array $rules, array $labels = []): Validator
    {
        return new Validator($data, $labels);
    }

    /** @param array<string, string> $errors */
    protected function withErrors(array $errors): void
    {
        Flash::withErrors($errors);
    }

    /** @param array<string, mixed> $data */
    protected function flashOld(array $data): void
    {
        Flash::old($data);
    }

    /** @param array<string, mixed> $meta */
    protected function audit(string $action, ?string $entityType = null, ?int $entityId = null, array $meta = []): void
    {
        (new AuditLogRepository())->log(Auth::id(), $action, $entityType ?? '', $entityId, $meta);
    }

    /** @return array<string, mixed> */
    protected function catalogData(): array
    {
        $catalog = new CatalogRepository();
        return [
            'sectorsList' => $catalog->sectors(),
            'chainsList' => $catalog->chains(),
            'states' => $catalog->states(),
            'universitiesList' => $catalog->universities(),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    protected function uploadedImages(Request $request): array
    {
        $files = $request->file('images');
        if ($files === null || !isset($files['name']) || !is_array($files['name'])) {
            return [];
        }

        $normalized = [];
        foreach ($files['name'] as $index => $name) {
            if ((int) ($files['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $normalized[] = [
                'name' => $name,
                'type' => $files['type'][$index] ?? '',
                'tmp_name' => $files['tmp_name'][$index] ?? '',
                'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$index] ?? 0,
                'caption' => trim((string) ($request->post('captions')[$index] ?? '')),
            ];
        }
        return $normalized;
    }

    protected function saveCaseImages(int $caseId, Request $request): int
    {
        $all = $this->uploadedImages($request);
        if ($all === []) {
            return 0;
        }
        $existing = (int) \App\Core\Database::value('select count(*) from case_images where case_id = $1', [$caseId]);
        $remaining = max(0, (int) config('app.uploads.max_files', 4) - $existing);
        $files = array_slice($all, 0, $remaining);
        $saved = 0;
        foreach ($files as $index => $file) {
            try {
                $image = Upload::image($file, 'caso-' . $caseId);
                (new \App\Repositories\CaseRepository())->addImage($caseId, $image, $file['caption'], $index);
                $saved++;
            } catch (\Throwable $e) {
                Flash::error('Una imagen no se pudo guardar: ' . $e->getMessage());
            }
        }
        return $saved;
    }

    protected function saveReportFiles(int $reportId, Request $request): int
    {
        $files = $request->file('documents');
        if ($files === null || !isset($files['name']) || !is_array($files['name'])) {
            return 0;
        }
        $saved = 0;
        foreach ($files['name'] as $index => $name) {
            if ((int) ($files['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($saved >= 3) {
                break;
            }
            try {
                $document = Upload::document([
                    'name' => $name,
                    'type' => $files['type'][$index] ?? '',
                    'tmp_name' => $files['tmp_name'][$index] ?? '',
                    'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $files['size'][$index] ?? 0,
                ], 'reporte-' . $reportId);
                (new \App\Repositories\ReportRepository())->addFile($reportId, $document + ['kind' => 'anexo']);
                $saved++;
            } catch (\Throwable $e) {
                Flash::error('Un archivo no se pudo guardar: ' . $e->getMessage());
            }
        }
        return $saved;
    }

    /** @param array<string, mixed> $content
     *  @param array<string, mixed> $user
     */
    protected function canEditContent(array $content, array $user): bool
    {
        if (in_array($user['role'], (array) config('app.moderators', []), true)) {
            return true;
        }
        return (int) $content['author_id'] === (int) $user['id']
            && in_array($content['status'], ['borrador', 'cambios_solicitados', 'rechazado'], true);
    }

    /** @param array<string, mixed> $content
     *  @param array<string, mixed> $user
     */
    protected function requireContentOwner(array $content, array $user): void
    {
        if (!$this->canEditContent($content, $user)) {
            $this->forbidden('No puedes editar este contenido en su estado actual.');
        }
    }

    /**
     * Normaliza importes capturados con comas o signos de peso.
     *
     * @param array<string, mixed> $data
     * @param array<int, string> $fields
     * @return array<string, mixed>
     */
    protected function normalizeNumbers(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            $value = $data[$field] ?? null;
            if ($value === null || $value === '') {
                $data[$field] = null;
                continue;
            }
            $clean = str_replace([',', '$', ' ', "\u{00A0}"], '', (string) $value);
            $data[$field] = is_numeric($clean) ? (float) $clean : null;
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    protected function renderValidationError(array $data, array $errors, string $view, string $layout = 'app'): Response
    {
        $this->withErrors($errors);
        $this->flashOld($data);
        return $this->view($view, $data + ['errors' => $errors], $layout);
    }
}
