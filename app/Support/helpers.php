<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Config;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Str;

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }
}

if (!function_exists('env_bool')) {
    function env_bool(string $key, bool $default = false): bool
    {
        $value = getenv($key);
        if ($value === false || $value === '') {
            return $default;
        }
        return filter_var($value, FILTER_VALIDATE_BOOL);
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = '/'): string
    {
        $base = rtrim((string) config('app.url', ''), '/');
        if ($path === '' || $path === '/') {
            return $base . '/';
        }
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('current_path')) {
    function current_path(): string
    {
        return Request::capture()->path();
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $path = ltrim($path, '/');
        $file = BASE_PATH . '/public/' . $path;
        $version = is_file($file) ? (string) filemtime($file) : '1';
        return url($path) . '?v=' . $version;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
    }
}

if (!function_exists('method_field')) {
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . e(strtoupper($method)) . '">';
    }
}

if (!function_exists('field_error')) {
    function field_error(string $field): ?string
    {
        return Flash::firstError($field);
    }
}

if (!function_exists('has_errors')) {
    function has_errors(): bool
    {
        return Flash::errors() !== [];
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        $old = Flash::oldInput();
        return $old[$key] ?? $default;
    }
}

if (!function_exists('auth')) {
    function auth(): ?array
    {
        return Auth::user();
    }
}

if (!function_exists('can')) {
    function can(string $ability): bool
    {
        $user = Auth::user();
        if ($user === null) {
            return false;
        }
        return match ($ability) {
            'moderar' => in_array($user['role'], (array) config('app.moderators', []), true),
            'administrar' => $user['role'] === 'admin',
            'publicar' => $user['status'] === 'verificado',
            'comentar' => $user['status'] === 'verificado',
            'editar' => true,
            default => false,
        };
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'd M Y'): string
    {
        if ($date === null || $date === '') {
            return '—';
        }
        $months = [1 => 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        try {
            $dt = (new DateTimeImmutable($date))->setTimezone(new DateTimeZone((string) config('app.timezone', 'America/Mexico_City')));
        } catch (Exception) {
            return '—';
        }
        return str_replace(
            ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            array_values($months),
            $dt->format($format)
        );
    }
}

if (!function_exists('time_ago')) {
    function time_ago(?string $date): string
    {
        if ($date === null || $date === '') {
            return '';
        }
        try {
            $dt = (new DateTimeImmutable($date))->setTimezone(new DateTimeZone((string) config('app.timezone', 'America/Mexico_City')));
        } catch (Exception) {
            return '';
        }
        $diff = time() - $dt->getTimestamp();
        if ($diff < 60) {
            return 'hace un momento';
        }
        if ($diff < 3600) {
            return 'hace ' . (int) floor($diff / 60) . ' min';
        }
        if ($diff < 86400) {
            return 'hace ' . (int) floor($diff / 3600) . ' h';
        }
        if ($diff < 86400 * 30) {
            return 'hace ' . (int) floor($diff / 86400) . ' d';
        }
        return format_date($date, 'd/m/Y');
    }
}

if (!function_exists('excerpt')) {
    function excerpt(?string $text, int $limit = 160): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', strip_tags((string) $text)) ?? '');
        if (mb_strlen($clean) <= $limit) {
            return $clean;
        }
        $cut = mb_substr($clean, 0, $limit - 1);
        $space = mb_strrpos($cut, ' ');
        if ($space !== false && $space > (int) ($limit * 0.6)) {
            $cut = mb_substr($cut, 0, $space);
        }
        return rtrim($cut, " ,;:.") . '…';
    }
}

if (!function_exists('initials')) {
    function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $parts = array_values(array_filter($parts));
        $titles = ['dra.', 'dr.', 'ing.', 'lic.', 'mtro.', 'mtra.', 'biol.'];
        while ($parts !== [] && in_array(mb_strtolower($parts[0]), $titles, true)) {
            array_shift($parts);
        }
        $letters = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $letters .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        return $letters !== '' ? $letters : 'AN';
    }
}

if (!function_exists('avatar_tag')) {
    function avatar_tag(string $name, ?string $url = null, string $class = ''): string
    {
        $classes = trim('avatar ' . $class);
        if ($url !== null && $url !== '') {
            return '<img class="' . e($classes) . '" src="' . e($url) . '" alt="' . e($name) . '" loading="lazy">';
        }
        return '<span class="' . e($classes) . '" title="' . e($name) . '" aria-hidden="true">' . e(initials($name)) . '</span>';
    }
}

if (!function_exists('status_label')) {
    function status_label(string $status): string
    {
        return [
            'borrador' => 'Borrador',
            'en_revision' => 'En revisión',
            'cambios_solicitados' => 'Cambios solicitados',
            'publicado' => 'Publicado',
            'rechazado' => 'Rechazado',
            'archivado' => 'Archivado',
        ][$status] ?? $status;
    }
}

if (!function_exists('status_tone')) {
    function status_tone(string $status): string
    {
        return [
            'borrador' => 'neutral',
            'en_revision' => 'warning',
            'cambios_solicitados' => 'warning',
            'publicado' => 'success',
            'rechazado' => 'danger',
            'archivado' => 'neutral',
        ][$status] ?? 'neutral';
    }
}

if (!function_exists('user_status_label')) {
    function user_status_label(string $status): string
    {
        return [
            'pendiente' => 'Verificación pendiente',
            'verificado' => 'Cuenta verificada',
            'suspendido' => 'Cuenta suspendida',
        ][$status] ?? $status;
    }
}

if (!function_exists('content_type_label')) {
    function content_type_label(string $type): string
    {
        return [
            'caso' => 'Caso de agronegocios',
            'reporte' => 'Reporte financiero',
            'oportunidad' => 'Oportunidad de negocio',
        ][$type] ?? $type;
    }
}

if (!function_exists('content_type_short')) {
    function content_type_short(string $type): string
    {
        return ['caso' => 'Caso', 'reporte' => 'Reporte', 'oportunidad' => 'Oportunidad'][$type] ?? $type;
    }
}

if (!function_exists('content_route')) {
    function content_route(string $type, string $slug = ''): string
    {
        $base = ['caso' => '/casos', 'reporte' => '/reportes', 'oportunidad' => '/oportunidades'][$type] ?? '/';
        return $slug === '' ? url($base) : url($base . '/' . $slug);
    }
}

if (!function_exists('company_type_label')) {
    function company_type_label(?string $type): string
    {
        return [
            'micro' => 'Microempresa',
            'pequena' => 'Pequeña empresa',
            'mediana' => 'Mediana empresa',
            'grande' => 'Empresa grande',
            'cooperativa' => 'Cooperativa',
            'ejido' => 'Ejido o comunidad',
            'asociacion' => 'Asociación de productores',
        ][(string) $type] ?? 'Mipyme';
    }
}

if (!function_exists('business_stage_label')) {
    function business_stage_label(?string $stage): string
    {
        return [
            'idea' => 'Idea o proyecto',
            'arranque' => 'Arranque',
            'operacion' => 'En operación',
            'crecimiento' => 'Crecimiento',
            'consolidacion' => 'Consolidación',
            'crisis' => 'Crisis o reestructura',
        ][(string) $stage] ?? 'En operación';
    }
}

if (!function_exists('opportunity_type_label')) {
    function opportunity_type_label(?string $type): string
    {
        return [
            'produccion' => 'Producción',
            'comercializacion' => 'Comercialización',
            'agroindustria' => 'Agroindustria',
            'servicios' => 'Servicios',
            'exportacion' => 'Exportación',
            'turismo_rural' => 'Turismo rural',
            'financiamiento' => 'Financiamiento',
            'tecnologia' => 'Tecnología',
        ][(string) $type] ?? 'Producción';
    }
}

if (!function_exists('opportunity_stage_label')) {
    function opportunity_stage_label(?string $stage): string
    {
        return [
            'idea' => 'Idea',
            'validacion' => 'En validación',
            'operacion' => 'En operación',
            'expansion' => 'Expansión',
        ][(string) $stage] ?? 'Idea';
    }
}

if (!function_exists('verification_method_label')) {
    function verification_method_label(?string $method): string
    {
        return [
            'dominio_institucional' => 'Correo institucional',
            'manual' => 'Revisión manual',
            'admin' => 'Alta administrativa',
        ][(string) $method] ?? 'Revisión manual';
    }
}

if (!function_exists('semester_label')) {
    function semester_label(?int $semester): string
    {
        return $semester !== null && $semester > 0 ? $semester . '.º semestre' : 'Semestre no indicado';
    }
}

if (!function_exists('money')) {
    function money(float|int|string|null $amount, string $currency = 'MXN'): string
    {
        if ($amount === null || $amount === '') {
            return '—';
        }
        $value = (float) $amount;
        $prefix = $currency === 'MXN' ? '$' : ($currency === 'USD' ? 'US$' : $currency . ' ');
        return $prefix . number_format($value, 2, '.', ',');
    }
}

if (!function_exists('compact_money')) {
    function compact_money(float|int|string|null $amount, string $currency = 'MXN'): string
    {
        if ($amount === null || $amount === '') {
            return '—';
        }
        $value = (float) $amount;
        $prefix = $currency === 'MXN' ? '$' : ($currency === 'USD' ? 'US$' : $currency . ' ');
        if (abs($value) >= 1_000_000) {
            return $prefix . number_format($value / 1_000_000, 1, '.', ',') . ' M';
        }
        if (abs($value) >= 1_000) {
            return $prefix . number_format($value / 1_000, 0, '.', ',') . ' mil';
        }
        return $prefix . number_format($value, 0, '.', ',');
    }
}

if (!function_exists('investment_range')) {
    function investment_range(float|int|string|null $min, float|int|string|null $max, string $currency = 'MXN'): string
    {
        if (($min === null || $min === '') && ($max === null || $max === '')) {
            return 'A convenir';
        }
        if ($max === null || $max === '' || (float) $max === (float) $min) {
            return compact_money($min, $currency);
        }
        return compact_money($min, $currency) . ' – ' . compact_money($max, $currency);
    }
}

if (!function_exists('pct')) {
    function pct(float|int|string|null $value, int $decimals = 1): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        return number_format((float) $value, $decimals, '.', ',') . '%';
    }
}

if (!function_exists('ratio')) {
    function ratio(float|int|string|null $value, int $decimals = 2): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        return number_format((float) $value, $decimals, '.', ',') . '×';
    }
}

if (!function_exists('ratio_tone')) {
    function ratio_tone(float|int|string|null $value, float $good, float $warn, bool $higherIsBetter = true): string
    {
        if ($value === null || $value === '') {
            return 'neutral';
        }
        $number = (float) $value;
        if ($higherIsBetter) {
            return $number >= $good ? 'good' : ($number >= $warn ? 'warn' : 'bad');
        }
        return $number <= $good ? 'good' : ($number <= $warn ? 'warn' : 'bad');
    }
}

if (!function_exists('tone_label')) {
    function tone_label(string $tone): string
    {
        return ['good' => 'Saludable', 'warn' => 'Vigilar', 'bad' => 'Atención', 'neutral' => 'Sin datos'][$tone] ?? 'Sin datos';
    }
}

if (!function_exists('is_active')) {
    function is_active(string $prefix): string
    {
        $path = current_path();
        if ($prefix === '/') {
            return $path === '/' ? 'is-active' : '';
        }
        return ($path === $prefix || str_starts_with($path, $prefix . '/')) ? 'is-active' : '';
    }
}

if (!function_exists('mask_email')) {
    function mask_email(string $email): string
    {
        return Str::maskEmail($email);
    }
}

if (!function_exists('share_url')) {
    function share_url(string $path): string
    {
        return rawurlencode(url($path));
    }
}

if (!function_exists('pg_array')) {
    /** Convierte una representación textual de PostgreSQL ({a,b}) en lista PHP. */
    function pg_array(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (!is_string($value) || trim($value) === '' || $value === '{}') {
            return [];
        }
        $inner = trim($value);
        if (str_starts_with($inner, '{') && str_ends_with($inner, '}')) {
            $inner = substr($inner, 1, -1);
        }
        if ($inner === '') {
            return [];
        }
        $items = str_getcsv($inner, ',', '"', '\\');
        return array_values(array_filter(array_map(static fn ($item) => trim((string) $item), $items), static fn ($item) => $item !== ''));
    }
}

if (!function_exists('first_name')) {
    function first_name(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $parts = array_values(array_filter($parts));
        if ($parts === []) {
            return 'colega';
        }
        $titles = ['dra.', 'dr.', 'dra', 'dr', 'ing.', 'ing', 'lic.', 'mtro.', 'mtra.', 'biól.', 'biol.'];
        if (in_array(mb_strtolower($parts[0]), $titles, true)) {
            array_shift($parts);
        }
        return $parts[0] ?? 'colega';
    }
}

if (!function_exists('plural')) {
    function plural(int $count, string $singular, string $plural): string
    {
        return $count === 1 ? $singular : $plural;
    }
}
