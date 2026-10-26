<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\Str;

final class OpportunityRepository
{
    /**
     * @param array<string, mixed> $filters
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function search(array $filters, int $page = 1, int $perPage = 9, bool $admin = false): array
    {
        $where = [];
        $params = [];

        if ($admin) {
            $where[] = 'o.deleted_at is null';
            $status = (string) ($filters['status'] ?? '');
            if (in_array($status, ['borrador', 'en_revision', 'cambios_solicitados', 'publicado', 'rechazado', 'archivado'], true)) {
                $params[] = $status;
                $where[] = 'o.status = $' . count($params);
            }
        } else {
            $where[] = "o.status = 'publicado' and o.deleted_at is null";
            $where[] = '(o.expires_at is null or o.expires_at >= current_date)';
        }

        $q = trim((string) ($filters['q'] ?? ''));
        $rankSql = '0::real';
        if ($q !== '') {
            $params[] = $q;
            $fts = '$' . count($params);
            $params[] = '%' . $q . '%';
            $like = '$' . count($params);
            $where[] = "(o.search_vector @@ websearch_to_tsquery('spanish', {$fts}) or o.title ilike {$like} or o.summary ilike {$like})";
            $params[] = $q;
            $rankSql = "ts_rank_cd(o.search_vector, websearch_to_tsquery('spanish', $" . count($params) . '))';
        }

        $sector = (string) ($filters['sector'] ?? '');
        if ($sector !== '') {
            $params[] = $sector;
            $where[] = 's.code = $' . count($params);
        }

        $chain = (string) ($filters['chain'] ?? '');
        if ($chain !== '') {
            $params[] = $chain;
            $where[] = 'ch.code = $' . count($params);
        }

        $state = (string) ($filters['state'] ?? '');
        if ($state !== '') {
            $params[] = $state;
            $where[] = 'o.state_code = $' . count($params);
        }

        $type = (string) ($filters['type'] ?? '');
        if (in_array($type, ['produccion', 'comercializacion', 'agroindustria', 'servicios', 'exportacion', 'turismo_rural', 'financiamiento', 'tecnologia'], true)) {
            $params[] = $type;
            $where[] = 'o.opportunity_type = $' . count($params);
        }

        $stage = (string) ($filters['stage'] ?? '');
        if (in_array($stage, ['idea', 'validacion', 'operacion', 'expansion'], true)) {
            $params[] = $stage;
            $where[] = 'o.stage = $' . count($params);
        }

        $min = (float) ($filters['investment_min'] ?? 0);
        $max = (float) ($filters['investment_max'] ?? 0);
        if ($min > 0) {
            $params[] = $min;
            $where[] = '(o.investment_max is null or o.investment_max >= $' . count($params) . ')';
        }
        if ($max > 0) {
            $params[] = $max;
            $where[] = '(o.investment_min is null or o.investment_min <= $' . count($params) . ')';
        }

        $authorId = (int) ($filters['author_id'] ?? 0);
        if ($authorId > 0) {
            $params[] = $authorId;
            $where[] = 'o.author_id = $' . count($params);
        }

        $universityId = (int) ($filters['university_id'] ?? 0);
        if ($universityId > 0) {
            $params[] = $universityId;
            $where[] = 'u.university_id = $' . count($params);
        }

        if (!empty($filters['featured'])) {
            $where[] = 'o.featured = true';
        }

        $sort = (string) ($filters['sort'] ?? 'recientes');
        $orderSql = match ($sort) {
            'relevancia' => $q !== '' ? $rankSql . ' desc, o.published_at desc nulls last' : 'o.published_at desc nulls last',
            'inversion' => 'o.investment_min asc nulls last',
            'cierre' => 'o.expires_at asc nulls last',
            default => 'o.published_at desc nulls last, o.created_at desc',
        };

        $offset = max(0, ($page - 1) * $perPage);
        $params[] = $perPage;
        $limit = '$' . count($params);
        $params[] = $offset;
        $offsetParam = '$' . count($params);

        $items = Database::select(
            "select o.id, o.slug, o.folio, o.title, o.summary, o.opportunity_type, o.stage,
                    o.investment_min, o.investment_max, o.currency, o.expected_return_pct, o.payback_months,
                    o.expires_at, o.is_confidential, o.views, o.published_at, o.created_at, o.featured,
                    o.is_demo, o.status,
                    s.name as sector_name, s.code as sector_code, s.emoji as sector_emoji,
                    ch.name as chain_name, ch.emoji as chain_emoji, st.name as state_name,
                    u.name as author_name, u.avatar_url as author_avatar, u.role as author_role,
                    un.acronym as university_acronym,
                    (select count(*) from interests i where i.opportunity_id = o.id) as interests_count,
                    count(*) over() as total_count,
                    {$rankSql} as rank
             from opportunities o
             join sectors s on s.id = o.sector_id
             left join chains ch on ch.id = o.chain_id
             left join states st on st.code = o.state_code
             join users u on u.id = o.author_id
             left join universities un on un.id = u.university_id
             where " . implode(' and ', $where) . "
             order by {$orderSql}
             limit {$limit} offset {$offsetParam}",
            $params
        );

        $total = $items === [] ? 0 : (int) $items[0]['total_count'];
        foreach ($items as &$item) {
            $item['id'] = (int) $item['id'];
            $item['views'] = (int) $item['views'];
            $item['interests_count'] = (int) $item['interests_count'];
            $item['is_confidential'] = $item['is_confidential'] === true || $item['is_confidential'] === 't';
            $item['featured'] = $item['featured'] === true || $item['featured'] === 't';
            $item['is_demo'] = $item['is_demo'] === true || $item['is_demo'] === 't';
            unset($item['total_count'], $item['rank']);
        }
        unset($item);

        return ['items' => $items, 'total' => $total];
    }

    /** @return array<string, mixed>|null */
    public function findPublishedBySlug(string $slug): ?array
    {
        $row = Database::first($this->detailSql() . " where o.slug = $1 and o.status = 'publicado' and o.deleted_at is null", [$slug]);
        return $row !== null ? $this->hydrate($row) : null;
    }

    /** @return array<string, mixed>|null */
    public function findBySlug(string $slug): ?array
    {
        $row = Database::first($this->detailSql() . ' where o.slug = $1 and o.deleted_at is null', [$slug]);
        return $row !== null ? $this->hydrate($row) : null;
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $row = Database::first($this->detailSql() . ' where o.id = $1 and o.deleted_at is null', [$id]);
        return $row !== null ? $this->hydrate($row) : null;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $slug = $this->uniqueSlug((string) $data['title']);
        $folio = 'OPO-' . date('Y') . '-' . str_pad((string) Database::value("select nextval('opportunity_folio_seq')"), 4, '0', STR_PAD_LEFT);
        $row = Database::insert(
            'insert into opportunities (slug, folio, author_id, title, summary, sector_id, chain_id, state_code,
                    municipality, opportunity_type, stage, investment_min, investment_max, currency,
                    expected_return_pct, payback_months, market_size, description, value_proposition,
                    requirements, risks, contact_name, contact_email, contact_phone, is_confidential,
                    expires_at, license, status, featured, is_demo, published_at)
             values ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23,$24,$25,
                     $26,$27,$28,$29,$30,$31)
             returning id',
            [
                $slug,
                $folio,
                (int) $data['author_id'],
                $data['title'],
                $data['summary'] ?? '',
                $data['sector_id'] ?: null,
                $data['chain_id'] ?: null,
                $data['state_code'] ?: null,
                $data['municipality'] ?? '',
                $data['opportunity_type'] ?? 'produccion',
                $data['stage'] ?? 'idea',
                $this->num($data['investment_min'] ?? null),
                $this->num($data['investment_max'] ?? null),
                $data['currency'] ?? 'MXN',
                $this->num($data['expected_return_pct'] ?? null),
                $data['payback_months'] !== '' ? (int) ($data['payback_months'] ?? 0) : null,
                $data['market_size'] ?? '',
                $data['description'] ?? '',
                $data['value_proposition'] ?? '',
                $data['requirements'] ?? '',
                $data['risks'] ?? '',
                $data['contact_name'] ?? '',
                $data['contact_email'] ?: null,
                $data['contact_phone'] ?? '',
                !empty($data['is_confidential']),
                $data['expires_at'] ?: null,
                $data['license'] ?? 'CC BY-NC-SA 4.0',
                $data['status'] ?? 'borrador',
                !empty($data['featured']),
                !empty($data['is_demo']),
                ($data['status'] ?? '') === 'publicado' ? date('c') : null,
            ]
        );
        return (int) ($row['id'] ?? 0);
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): void
    {
        Database::execute(
            'update opportunities set title = $1, summary = $2, sector_id = $3, chain_id = $4, state_code = $5,
                    municipality = $6, opportunity_type = $7, stage = $8, investment_min = $9, investment_max = $10,
                    currency = $11, expected_return_pct = $12, payback_months = $13, market_size = $14,
                    description = $15, value_proposition = $16, requirements = $17, risks = $18, contact_name = $19,
                    contact_email = $20, contact_phone = $21, is_confidential = $22, expires_at = $23, license = $24
             where id = $25',
            [
                $data['title'],
                $data['summary'] ?? '',
                $data['sector_id'] ?: null,
                $data['chain_id'] ?: null,
                $data['state_code'] ?: null,
                $data['municipality'] ?? '',
                $data['opportunity_type'] ?? 'produccion',
                $data['stage'] ?? 'idea',
                $this->num($data['investment_min'] ?? null),
                $this->num($data['investment_max'] ?? null),
                $data['currency'] ?? 'MXN',
                $this->num($data['expected_return_pct'] ?? null),
                $data['payback_months'] !== '' ? (int) ($data['payback_months'] ?? 0) : null,
                $data['market_size'] ?? '',
                $data['description'] ?? '',
                $data['value_proposition'] ?? '',
                $data['requirements'] ?? '',
                $data['risks'] ?? '',
                $data['contact_name'] ?? '',
                $data['contact_email'] ?: null,
                $data['contact_phone'] ?? '',
                !empty($data['is_confidential']),
                $data['expires_at'] ?: null,
                $data['license'] ?? 'CC BY-NC-SA 4.0',
                $id,
            ]
        );
    }

    public function updateStatus(int $id, string $status, bool $featured = false): void
    {
        Database::execute(
            'update opportunities set status = $1, featured = $2,
                    published_at = case when $1 = \'publicado\' and published_at is null then now()
                                        when $1 <> \'publicado\' then null else published_at end
             where id = $3',
            [$status, $featured, $id]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function mine(int $authorId): array
    {
        return Database::select(
            "select o.*, s.name as sector_name, s.emoji as sector_emoji, st.name as state_name,
                    (select count(*) from interests i where i.opportunity_id = o.id) as interests_count
             from opportunities o
             join sectors s on s.id = o.sector_id
             left join states st on st.code = o.state_code
             where o.author_id = $1 and o.deleted_at is null
             order by o.updated_at desc",
            [$authorId]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function interestsFor(int $opportunityId): array
    {
        return Database::select(
            "select i.*, u.name as student_name, u.email as student_email, u.career, u.semester,
                    un.acronym as university_acronym
             from interests i
             join users u on u.id = i.user_id
             left join universities un on un.id = u.university_id
             where i.opportunity_id = $1
             order by i.created_at desc",
            [$opportunityId]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function interestsForAuthor(int $authorId, int $limit = 30): array
    {
        return Database::select(
            "select i.*, o.title, o.slug, u.name as student_name, u.email as student_email,
                    un.acronym as university_acronym
             from interests i
             join opportunities o on o.id = i.opportunity_id
             join users u on u.id = i.user_id
             left join universities un on un.id = u.university_id
             where o.author_id = $1
             order by i.created_at desc limit $2",
            [$authorId, $limit]
        );
    }

    public function hasInterest(int $opportunityId, int $userId): bool
    {
        return (bool) Database::value(
            'select exists(select 1 from interests where opportunity_id = $1 and user_id = $2)',
            [$opportunityId, $userId]
        );
    }

    public function registerInterest(int $opportunityId, int $userId, string $message): void
    {
        Database::execute(
            'insert into interests (opportunity_id, user_id, message) values ($1, $2, $3)
             on conflict (opportunity_id, user_id) do update set message = excluded.message, created_at = now()',
            [$opportunityId, $userId, $message]
        );
    }

    public function updateInterestStatus(int $interestId, string $status): void
    {
        if (in_array($status, ['nuevo', 'contactado', 'cerrado'], true)) {
            Database::execute('update interests set status = $1 where id = $2', [$status, $interestId]);
        }
    }

    /** @return array<string, int> */
    public function stats(): array
    {
        $row = Database::first(
            "select count(*) filter (where status = 'publicado') as published,
                    count(*) filter (where status = 'en_revision') as review,
                    count(*) filter (where status = 'borrador') as drafts,
                    coalesce(sum(views) filter (where status = 'publicado'), 0) as views,
                    (select count(*) from interests) as interests
             from opportunities where deleted_at is null"
        ) ?? [];
        return array_map(static fn ($value): int => (int) $value, $row);
    }

    /** @return array<int, array<string, mixed>> */
    public function featured(int $limit = 3): array
    {
        return $this->search(['featured' => true], 1, $limit)['items'];
    }

    /** @return array<int, array<string, mixed>> */
    public function latest(int $limit = 6): array
    {
        return $this->search([], 1, $limit)['items'];
    }

    public function incrementViews(int $id): void
    {
        Database::execute('update opportunities set views = views + 1 where id = $1', [$id]);
    }

    public function uniqueSlug(string $title): string
    {
        $base = Str::slug($title, 70);
        $slug = $base;
        $i = 2;
        while ((bool) Database::value('select exists(select 1 from opportunities where slug = $1)', [$slug])) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }

    private function num(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        return (float) str_replace([',', '$', ' '], '', (string) $value);
    }

    private function detailSql(): string
    {
        return "select o.*, s.name as sector_name, s.code as sector_code, s.emoji as sector_emoji,
                       ch.name as chain_name, ch.code as chain_code, ch.emoji as chain_emoji,
                       st.name as state_name,
                       u.name as author_name, u.email as author_email, u.bio as author_bio,
                       u.avatar_url as author_avatar_url, u.role as author_role, u.status as author_status,
                       u.career as author_career, u.semester as author_semester,
                       un.name as university_name, un.acronym as university_acronym, un.slug as university_slug,
                       (select count(*) from interests i where i.opportunity_id = o.id) as interests_count,
                       (select count(*) from comments cc where cc.target_type = 'oportunidad' and cc.target_id = o.id and cc.status = 'visible') as comments_count,
                       (select count(*) from reactions cr where cr.target_type = 'oportunidad' and cr.target_id = o.id) as reactions_count,
                       (select count(*) from bookmarks b where b.target_type = 'oportunidad' and b.target_id = o.id) as bookmarks_count
                from opportunities o
                join sectors s on s.id = o.sector_id
                left join chains ch on ch.id = o.chain_id
                left join states st on st.code = o.state_code
                join users u on u.id = o.author_id
                left join universities un on un.id = u.university_id";
    }

    /** @param array<string, mixed> $row
     *  @return array<string, mixed>
     */
    private function hydrate(array $row): array
    {
        $row['id'] = (int) $row['id'];
        $row['author_id'] = (int) $row['author_id'];
        $row['views'] = (int) $row['views'];
        $row['interests_count'] = (int) $row['interests_count'];
        $row['comments_count'] = (int) $row['comments_count'];
        $row['reactions_count'] = (int) $row['reactions_count'];
        $row['bookmarks_count'] = (int) ($row['bookmarks_count'] ?? 0);
        $row['is_confidential'] = $row['is_confidential'] === true || $row['is_confidential'] === 't';
        $row['featured'] = $row['featured'] === true || $row['featured'] === 't';
        $row['is_demo'] = $row['is_demo'] === true || $row['is_demo'] === 't';
        return $row;
    }
}
