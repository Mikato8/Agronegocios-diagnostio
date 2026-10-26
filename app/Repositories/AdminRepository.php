<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class AdminRepository
{
    private const TABLES = ['caso' => 'cases', 'reporte' => 'reports', 'oportunidad' => 'opportunities'];

    /** @return array<string, int> */
    public function stats(): array
    {
        $row = Database::first(
            "select
                (select count(*) from users where status = 'pendiente' and deleted_at is null) as pending_users,
                (select count(*) from users where status = 'verificado' and deleted_at is null) as verified_users,
                (select count(*) from users where deleted_at is null) as users,
                (select count(*) from cases where status = 'en_revision' and deleted_at is null) as pending_cases,
                (select count(*) from reports where status = 'en_revision' and deleted_at is null) as pending_reports,
                (select count(*) from opportunities where status = 'en_revision' and deleted_at is null) as pending_opportunities,
                (select count(*) from cases where status = 'publicado' and deleted_at is null) as published_cases,
                (select count(*) from reports where status = 'publicado' and deleted_at is null) as published_reports,
                (select count(*) from opportunities where status = 'publicado' and deleted_at is null) as published_opportunities,
                (select count(*) from comments where status = 'visible') as comments,
                (select count(*) from interests) as interests,
                (select count(*) from reviews) as reviews"
        ) ?? [];
        return array_map(static fn ($value): int => (int) $value, $row);
    }

    /**
     * Cola combinada de revisión editorial.
     *
     * @return array<int, array<string, mixed>>
     */
    public function pendingQueue(int $limit = 30): array
    {
        return Database::select(
            "select * from (
                select 'caso' as type, c.id, c.title, c.slug, c.status, c.created_at, c.updated_at,
                       u.name as author_name, s.name as sector_name
                from cases c join users u on u.id = c.author_id left join sectors s on s.id = c.sector_id
                where c.status = 'en_revision' and c.deleted_at is null
                union all
                select 'reporte' as type, r.id, r.title, r.slug, r.status, r.created_at, r.updated_at,
                       u.name as author_name, s.name as sector_name
                from reports r join users u on u.id = r.author_id left join sectors s on s.id = r.sector_id
                where r.status = 'en_revision' and r.deleted_at is null
                union all
                select 'oportunidad' as type, o.id, o.title, o.slug, o.status, o.created_at, o.updated_at,
                       u.name as author_name, s.name as sector_name
                from opportunities o join users u on u.id = o.author_id left join sectors s on s.id = o.sector_id
                where o.status = 'en_revision' and o.deleted_at is null
             ) q order by q.updated_at asc limit $1",
            [$limit]
        );
    }

    /** @return array<string, mixed>|null */
    public function content(string $type, int $id): ?array
    {
        $table = self::TABLES[$type] ?? null;
        if ($table === null) {
            return null;
        }
        $repo = match ($type) {
            'caso' => new CaseRepository(),
            'reporte' => new ReportRepository(),
            'oportunidad' => new OpportunityRepository(),
        };
        return $repo->findById($id);
    }

    /** @return array<string, mixed>|null */
    public function contentBySlug(string $type, string $slug): ?array
    {
        $repo = match ($type) {
            'caso' => new CaseRepository(),
            'reporte' => new ReportRepository(),
            'oportunidad' => new OpportunityRepository(),
        };
        return $repo->findBySlug($slug);
    }

    public function review(string $type, int $id, int $reviewerId, string $decision, string $notes, bool $featured = false): string
    {
        $table = self::TABLES[$type] ?? null;
        if ($table === null) {
            throw new \InvalidArgumentException('Tipo de contenido no válido.');
        }

        $status = match ($decision) {
            'publicar' => 'publicado',
            'cambios' => 'cambios_solicitados',
            default => 'rechazado',
        };

        Database::execute(
            "update {$table} set status = $1, featured = $2,
                    published_at = case when $1 = 'publicado' and published_at is null then now()
                                        when $1 <> 'publicado' then null else published_at end
             where id = $3",
            [$status, $featured, $id]
        );

        (new InteractionRepository())->addReview($type, $id, $reviewerId, $decision, $notes);

        (new AuditLogRepository())->log($reviewerId, 'revision.' . $decision, $type, $id, [
            'status' => $status,
            'featured' => $featured,
            'notes' => $notes,
        ]);

        return $status;
    }

    public function toggleFeatured(string $type, int $id, bool $featured): void
    {
        $table = self::TABLES[$type] ?? null;
        if ($table === null) {
            return;
        }
        Database::execute("update {$table} set featured = $1 where id = $2", [$featured, $id]);
    }

    /** @return array<int, array<string, mixed>> */
    public function comments(int $limit = 50): array
    {
        return Database::select(
            "select c.*, u.name as author_name, u.email as author_email,
                    coalesce(ca.title, r.title, o.title) as target_title,
                    coalesce(ca.slug, r.slug, o.slug) as target_slug
             from comments c
             join users u on u.id = c.user_id
             left join cases ca on c.target_type = 'caso' and ca.id = c.target_id
             left join reports r on c.target_type = 'reporte' and r.id = c.target_id
             left join opportunities o on c.target_type = 'oportunidad' and o.id = c.target_id
             order by c.created_at desc limit $1",
            [$limit]
        );
    }

    /** @return array<string, int> */
    public function contentCounts(): array
    {
        $row = Database::first(
            "select
                (select count(*) from cases where deleted_at is null) as cases,
                (select count(*) from reports where deleted_at is null) as reports,
                (select count(*) from opportunities where deleted_at is null) as opportunities"
        ) ?? [];
        return array_map(static fn ($value): int => (int) $value, $row);
    }
}
