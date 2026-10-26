<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class CatalogRepository
{
    /** @var array<string, array<int, array<string, mixed>>> */
    private static array $cache = [];

    /** @return array<int, array<string, mixed>> */
    public function states(): array
    {
        return self::$cache['states'] ??= Database::select('select code, name, region from states order by name');
    }

    /** @return array<int, array<string, mixed>> */
    public function sectors(): array
    {
        return self::$cache['sectors'] ??= Database::select(
            'select s.*, (select count(*) from cases c where c.sector_id = s.id and c.status = \'publicado\') as cases_count
             from sectors s order by s.sort_order, s.name'
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function chains(): array
    {
        return self::$cache['chains'] ??= Database::select('select * from chains order by name');
    }

    /** @return array<int, array<string, mixed>> */
    public function universities(): array
    {
        $rows = self::$cache['universities'] ??= Database::select(
            'select u.*, s.name as state_name,
                    (select count(*) from users x where x.university_id = u.id and x.status = \'verificado\' and x.deleted_at is null) as students_count
             from universities u
             left join states s on s.code = u.state_code
             order by u.name'
        );

        foreach ($rows as &$row) {
            $row['email_domains_list'] = pg_array($row['email_domains']);
        }
        unset($row);
        return $rows;
    }

    /** @return array<string, mixed>|null */
    public function university(int $id): ?array
    {
        return Database::first(
            'select u.*, s.name as state_name from universities u
             left join states s on s.code = u.state_code where u.id = $1',
            [$id]
        );
    }

    /** @return array<string, mixed>|null */
    public function universityBySlug(string $slug): ?array
    {
        return Database::first(
            'select u.*, s.name as state_name from universities u
             left join states s on s.code = u.state_code where u.slug = $1',
            [$slug]
        );
    }

    /**
     * Busca la institución por dominio de correo. Devuelve la primera
     * coincidencia; los dominios se guardan sin "@".
     *
     * @return array<string, mixed>|null
     */
    public function universityByEmailDomain(string $domain): ?array
    {
        $domain = mb_strtolower(trim($domain));
        if ($domain === '') {
            return null;
        }
        return Database::first(
            "select u.*, s.name as state_name from universities u
             left join states s on s.code = u.state_code
             where exists (select 1 from unnest(u.email_domains) d where lower(d) = $1)
             limit 1",
            [$domain]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function universitiesByState(): array
    {
        $rows = $this->universities();
        $grouped = [];
        foreach ($rows as $row) {
            $key = (string) ($row['state_name'] ?? 'Sin estado');
            $grouped[$key][] = $row;
        }
        ksort($grouped);
        return $grouped;
    }

    /** @return array<string, int> */
    public function stats(): array
    {
        $row = Database::first(
            "select
                (select count(*) from cases where status = 'publicado' and deleted_at is null) as cases,
                (select count(*) from reports where status = 'publicado' and deleted_at is null) as reports,
                (select count(*) from opportunities where status = 'publicado' and deleted_at is null) as opportunities,
                (select count(*) from users where status = 'verificado' and deleted_at is null) as students,
                (select count(*) from universities) as universities,
                (select count(distinct state_code) from (
                    select state_code from cases where status = 'publicado' and state_code is not null
                    union select state_code from reports where status = 'publicado' and state_code is not null
                    union select state_code from opportunities where status = 'publicado' and state_code is not null
                ) t) as states,
                (select count(distinct sector_id) from cases where status = 'publicado' and sector_id is not null) as sectors,
                (select coalesce(sum(views),0) from cases where status = 'publicado') as views"
        ) ?? [];

        return array_map(static fn ($value): int => (int) $value, $row);
    }

    /** @return array<int, int> */
    public function years(): array
    {
        $rows = Database::select(
            "select distinct year from cases where status = 'publicado' and year between 2000 and 2100 order by year desc"
        );
        return array_map(static fn (array $row): int => (int) $row['year'], $rows);
    }

    /** @return array<int, array{name: string, total: int}> */
    public function popularStates(int $limit = 8): array
    {
        return Database::select(
            "select s.name, count(*)::int as total
             from (
                select state_code from cases where status = 'publicado' and state_code is not null
                union all select state_code from reports where status = 'publicado' and state_code is not null
                union all select state_code from opportunities where status = 'publicado' and state_code is not null
             ) x join states s on s.code = x.state_code
             group by s.name order by total desc, s.name limit $1",
            [$limit]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function topUniversities(int $limit = 6): array
    {
        return Database::select(
            "select u.name, u.acronym, u.slug, s.name as state_name, count(x.id)::int as total
             from users x
             join universities u on u.id = x.university_id
             left join states s on s.code = u.state_code
             where x.status = 'verificado' and x.deleted_at is null
             group by u.id, u.name, u.acronym, u.slug, s.name
             order by total desc, u.name limit $1",
            [$limit]
        );
    }

    public static function flush(): void
    {
        self::$cache = [];
    }
}
