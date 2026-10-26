<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CatalogRepository;

/**
 * Verificación de estudiantes sin cédula profesional.
 *
 * Reglas:
 *  - Correo con dominio de una institución registrada → verificación automática.
 *  - Correo con dominio .edu.mx → verificación automática.
 *  - Cualquier otro correo → revisión manual; el estudiante puede subir una
 *    constancia de estudios y un editor decide.
 */
final class StudentVerifier
{
    /** @return array{status: string, method: string, university_id: ?int, domain: string, message: string} */
    public function evaluate(string $email, ?int $selectedUniversityId = null): array
    {
        $domain = $this->domain($email);

        if ($domain === '') {
            return [
                'status' => 'pendiente',
                'method' => 'manual',
                'university_id' => $selectedUniversityId,
                'domain' => '',
                'message' => 'Usa un correo válido para que podamos verificar tu cuenta.',
            ];
        }

        $university = (new CatalogRepository())->universityByEmailDomain($domain);

        if ($university !== null) {
            return [
                'status' => 'verificado',
                'method' => 'dominio_institucional',
                'university_id' => $selectedUniversityId ?: (int) $university['id'],
                'domain' => $domain,
                'message' => 'Tu correo institucional coincide con ' . $university['name'] . '. La cuenta quedó verificada.',
            ];
        }

        if (str_ends_with($domain, '.edu.mx')) {
            return [
                'status' => 'verificado',
                'method' => 'dominio_institucional',
                'university_id' => $selectedUniversityId,
                'domain' => $domain,
                'message' => 'Tu correo .edu.mx confirma tu pertenencia institucional. La cuenta quedó verificada.',
            ];
        }

        if ((bool) config('app.auto_verify_users', false)) {
            return [
                'status' => 'verificado',
                'method' => 'admin',
                'university_id' => $selectedUniversityId,
                'domain' => $domain,
                'message' => 'La verificación automática está activa en este entorno.',
            ];
        }

        return [
            'status' => 'pendiente',
            'method' => 'manual',
            'university_id' => $selectedUniversityId,
            'domain' => $domain,
            'message' => 'Tu correo no es institucional. Puedes subir una constancia de estudios; un editor revisará tu cuenta.',
        ];
    }

    public function isInstitutionalDomain(string $email): bool
    {
        $domain = $this->domain($email);
        if ($domain === '') {
            return false;
        }
        if (str_ends_with($domain, '.edu.mx')) {
            return true;
        }
        return (new CatalogRepository())->universityByEmailDomain($domain) !== null;
    }

    public function domain(string $email): string
    {
        $email = mb_strtolower(trim($email));
        $position = mb_strrpos($email, '@');
        if ($position === false) {
            return '';
        }
        return trim(substr($email, $position + 1), " \t\n\r\0\x0B.");
    }
}
