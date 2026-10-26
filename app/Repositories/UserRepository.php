<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\Str;

final class UserRepository
{
    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        return Database::first($this->selectSql() . ' where u.id = $1 and u.deleted_at is null', [$id]);
    }

    /** @return array<string, mixed>|null */
    public function findByEmail(string $email): ?array
    {
        return Database::first($this->selectSql() . ' where u.email = $1 and u.deleted_at is null', [Str::normalizeEmail($email)]);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $row = Database::insert(
            'insert into users (name, email, password_hash, role, status, university_id, institution_other, career,
                                semester, student_id, state_code, bio, verification_method, proof_path, terms_accepted_at, email_verified)
             values ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, now(), $15)
             returning id',
            [
                $data['name'],
                Str::normalizeEmail((string) $data['email']),
                $data['password_hash'],
                $data['role'] ?? 'estudiante',
                $data['status'] ?? 'pendiente',
                $data['university_id'] ?? null,
                $data['institution_other'] ?? '',
                $data['career'] ?? '',
                $data['semester'] ?? null,
                $data['student_id'] ?? '',
                $data['state_code'] ?? null,
                $data['bio'] ?? '',
                $data['verification_method'] ?? 'manual',
                $data['proof_path'] ?? null,
                $data['status'] === 'verificado',
            ]
        );
        return (int) ($row['id'] ?? 0);
    }

    /** @param array<string, mixed> $data */
    public function updateProfile(int $id, array $data): void
    {
        Database::execute(
            'update users set name = $1, university_id = $2, institution_other = $3, career = $4,
                    semester = $5, student_id = $6, state_code = $7, bio = $8
             where id = $9 and deleted_at is null',
            [
                $data['name'],
                $data['university_id'] ?? null,
                $data['institution_other'] ?? '',
                $data['career'] ?? '',
                $data['semester'] ?? null,
                $data['student_id'] ?? '',
                $data['state_code'] ?? null,
                $data['bio'] ?? '',
                $id,
            ]
        );
    }

    public function updatePassword(int $id, string $hash): void
    {
        Database::execute('update users set password_hash = $1 where id = $2', [$hash, $id]);
    }

    public function updateEmailVerified(int $id, bool $verified): void
    {
        Database::execute('update users set email_verified = $1 where id = $2', [$verified, $id]);
    }

    public function setProof(int $id, ?string $path): void
    {
        Database::execute('update users set proof_path = $1 where id = $2', [$path, $id]);
    }

    public function updateAvatar(int $id, ?string $url): void
    {
        Database::execute('update users set avatar_url = $1 where id = $2', [$url, $id]);
    }

    public function verify(int $id, string $note = ''): void
    {
        Database::execute(
            "update users set status = 'verificado', verification_method = case when verification_method = 'manual' then 'manual' else verification_method end,
                    verification_note = $1, email_verified = true
             where id = $2",
            [$note, $id]
        );
    }

    public function setStatus(int $id, string $status, string $note = ''): void
    {
        Database::execute('update users set status = $1, verification_note = $2 where id = $3', [$status, $note, $id]);
    }

    public function setRole(int $id, string $role): void
    {
        Database::execute('update users set role = $1 where id = $2', [$role, $id]);
    }

    public function touchLogin(int $id): void
    {
        Database::execute('update users set last_login_at = now() where id = $1', [$id]);
    }

    /** @return array<int, array<string, mixed>> */
    public function pending(int $limit = 20): array
    {
        return Database::select(
            $this->selectSql() . " where u.status = 'pendiente' and u.deleted_at is null
             order by u.created_at asc limit $1",
            [$limit]
        );
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function search(array $filters, int $page = 1, int $perPage = 20): array
    {
        $where = ['u.deleted_at is null'];
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $params[] = '%' . $q . '%';
            $where[] = '(u.name ilike $' . count($params) . ' or u.email::text ilike $' . count($params) . ')';
        }

        $status = (string) ($filters['status'] ?? '');
        if (in_array($status, ['pendiente', 'verificado', 'suspendido'], true)) {
            $params[] = $status;
            $where[] = 'u.status = $' . count($params);
        }

        $role = (string) ($filters['role'] ?? '');
        if (in_array($role, ['estudiante', 'docente', 'editor', 'admin'], true)) {
            $params[] = $role;
            $where[] = 'u.role = $' . count($params);
        }

        $whereSql = implode(' and ', $where);
        $offset = max(0, ($page - 1) * $perPage);
        $params[] = $perPage;
        $limit = '$' . count($params);
        $params[] = $offset;
        $offsetParam = '$' . count($params);

        $items = Database::select(
            $this->selectSql() . " where {$whereSql}
             order by u.created_at desc limit {$limit} offset {$offsetParam}",
            $params
        );
        $total = (int) Database::value("select count(*) from users u where {$whereSql}", array_slice($params, 0, count($params) - 2));

        return ['items' => $items, 'total' => $total];
    }

    /** @return array<int, array<string, mixed>> */
    public function byUniversity(int $universityId, int $limit = 40): array
    {
        return Database::select(
            $this->selectSql() . " where u.university_id = $1 and u.status = 'verificado' and u.deleted_at is null
             order by u.name limit $2",
            [$universityId, $limit]
        );
    }

    /** @return array<string, int> */
    public function stats(): array
    {
        $row = Database::first(
            "select
                count(*) filter (where status = 'verificado') as verified,
                count(*) filter (where status = 'pendiente') as pending,
                count(*) filter (where status = 'suspendido') as suspended,
                count(*) filter (where role = 'docente') as teachers,
                count(*) as total
             from users where deleted_at is null"
        ) ?? [];
        return array_map(static fn ($value): int => (int) $value, $row);
    }

    /** @return array<string, int> */
    public function activity(int $id): array
    {
        $row = Database::first(
            "select
                (select count(*) from cases where author_id = $1 and deleted_at is null) as cases,
                (select count(*) from cases where author_id = $1 and status = 'publicado' and deleted_at is null) as cases_published,
                (select count(*) from reports where author_id = $1 and deleted_at is null) as reports,
                (select count(*) from reports where author_id = $1 and status = 'publicado' and deleted_at is null) as reports_published,
                (select count(*) from opportunities where author_id = $1 and deleted_at is null) as opportunities,
                (select count(*) from opportunities where author_id = $1 and status = 'publicado' and deleted_at is null) as opportunities_published,
                (select count(*) from comments where user_id = $1 and status = 'visible') as comments,
                (select count(*) from bookmarks where user_id = $1) as bookmarks,
                (select coalesce(sum(views),0) from (
                    select views from cases where author_id = $1 and status = 'publicado' and deleted_at is null
                    union all select views from reports where author_id = $1 and status = 'publicado' and deleted_at is null
                    union all select views from opportunities where author_id = $1 and status = 'publicado' and deleted_at is null
                ) v) as views
             ",
            [$id]
        ) ?? [];
        return array_map(static fn ($value): int => (int) $value, $row);
    }

    public function deleteAccount(int $id): void
    {
        Database::execute(
            "update users set deleted_at = now(), status = 'suspendido',
                    name = 'Cuenta eliminada', email = $1,
                    password_hash = $2, bio = '', student_id = '', proof_path = null
             where id = $3",
            ['eliminado-' . $id . '@example.invalid', password_hash(Str::token(24), PASSWORD_DEFAULT), $id]
        );
    }

    public function createPasswordReset(int $userId): string
    {
        Database::execute('delete from password_resets where user_id = $1 or expires_at < now()', [$userId]);
        $token = Str::token(32);
        Database::execute(
            'insert into password_resets (user_id, token_hash, expires_at) values ($1, $2, now() + interval \'60 minutes\')',
            [$userId, hash('sha256', $token)]
        );
        return $token;
    }

    /** @return array<string, mixed>|null */
    public function findByResetToken(string $token): ?array
    {
        return Database::first(
            'select u.* from password_resets r join users u on u.id = r.user_id
             where r.token_hash = $1 and r.used_at is null and r.expires_at > now() and u.deleted_at is null',
            [hash('sha256', $token)]
        );
    }

    public function consumeResetToken(string $token): void
    {
        Database::execute('update password_resets set used_at = now() where token_hash = $1', [hash('sha256', $token)]);
    }

    private function selectSql(): string
    {
        return "select u.*, s.name as state_name,
                       un.name as university_name, un.acronym as university_acronym,
                       un.slug as university_slug, un.state_code as university_state,
                       (select count(*) from cases c where c.author_id = u.id and c.status = 'publicado' and c.deleted_at is null) as published_cases,
                       (select count(*) from reports r where r.author_id = u.id and r.status = 'publicado' and r.deleted_at is null) as published_reports,
                       (select count(*) from opportunities o where o.author_id = u.id and o.status = 'publicado' and o.deleted_at is null) as published_opportunities
                from users u
                left join states s on s.code = u.state_code
                left join universities un on un.id = u.university_id";
    }
}
