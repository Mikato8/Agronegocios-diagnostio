<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\Str;

final class ReportRepository
{
    public const NUMERIC_FIELDS = [
        'revenue', 'cogs', 'opex', 'other_income', 'net_income',
        'total_assets', 'current_assets', 'cash', 'receivables', 'inventory',
        'total_liabilities', 'current_liabilities', 'debt', 'equity',
    ];

    /**
     * @param array<string, mixed> $filters
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function search(array $filters, int $page = 1, int $perPage = 9, bool $admin = false): array
    {
        $where = [];
        $params = [];

        if ($admin) {
            $where[] = 'r.deleted_at is null';
            $status = (string) ($filters['status'] ?? '');
            if (in_array($status, ['borrador', 'en_revision', 'cambios_solicitados', 'publicado', 'rechazado', 'archivado'], true)) {
                $params[] = $status;
                $where[] = 'r.status = $' . count($params);
            }
        } else {
            $where[] = "r.status = 'publicado' and r.deleted_at is null";
        }

        $q = trim((string) ($filters['q'] ?? ''));
        $rankSql = '0::real';
        if ($q !== '') {
            $params[] = $q;
            $fts = '$' . count($params);
            $params[] = '%' . $q . '%';
            $like = '$' . count($params);
            $where[] = "(r.search_vector @@ websearch_to_tsquery('spanish', {$fts}) or r.title ilike {$like} or r.summary ilike {$like})";
            $params[] = $q;
            $rankSql = "ts_rank_cd(r.search_vector, websearch_to_tsquery('spanish', $" . count($params) . '))';
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
            $where[] = 'r.state_code = $' . count($params);
        }

        $year = (int) ($filters['year'] ?? 0);
        if ($year > 2000 && $year < 2100) {
            $params[] = $year;
            $where[] = 'extract(year from coalesce(r.period_end, r.published_at::date)) = $' . count($params);
        }

        $company = (string) ($filters['company_type'] ?? '');
        if (in_array($company, ['micro', 'pequena', 'mediana', 'grande', 'cooperativa', 'ejido', 'asociacion'], true)) {
            $params[] = $company;
            $where[] = 'r.company_type = $' . count($params);
        }

        $authorId = (int) ($filters['author_id'] ?? 0);
        if ($authorId > 0) {
            $params[] = $authorId;
            $where[] = 'r.author_id = $' . count($params);
        }

        $universityId = (int) ($filters['university_id'] ?? 0);
        if ($universityId > 0) {
            $params[] = $universityId;
            $where[] = 'u.university_id = $' . count($params);
        }

        if (!empty($filters['featured'])) {
            $where[] = 'r.featured = true';
        }

        $sort = (string) ($filters['sort'] ?? 'recientes');
        $orderSql = match ($sort) {
            'relevancia' => $q !== '' ? $rankSql . ' desc, r.published_at desc nulls last' : 'r.published_at desc nulls last',
            'vistos' => 'r.views desc, r.published_at desc nulls last',
            'antiguos' => 'r.published_at asc nulls last',
            default => 'r.published_at desc nulls last, r.created_at desc',
        };

        $offset = max(0, ($page - 1) * $perPage);
        $params[] = $perPage;
        $limit = '$' . count($params);
        $params[] = $offset;
        $offsetParam = '$' . count($params);

        $items = Database::select(
            "select r.id, r.slug, r.folio, r.title, r.summary, r.period_start, r.period_end, r.currency,
                    r.revenue, r.net_income, r.total_assets, r.views, r.published_at, r.created_at,
                    r.featured, r.is_demo, r.status, r.company_type,
                    s.name as sector_name, s.code as sector_code, s.emoji as sector_emoji,
                    ch.name as chain_name, ch.emoji as chain_emoji, st.name as state_name,
                    u.name as author_name, u.avatar_url as author_avatar, u.role as author_role,
                    un.acronym as university_acronym,
                    (select count(*) from report_files rf where rf.report_id = r.id) as files_count,
                    (select count(*) from comments cc where cc.target_type = 'reporte' and cc.target_id = r.id and cc.status = 'visible') as comments_count,
                    count(*) over() as total_count,
                    {$rankSql} as rank
             from reports r
             join sectors s on s.id = r.sector_id
             left join chains ch on ch.id = r.chain_id
             left join states st on st.code = r.state_code
             join users u on u.id = r.author_id
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
            $item['files_count'] = (int) $item['files_count'];
            $item['comments_count'] = (int) $item['comments_count'];
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
        $row = Database::first($this->detailSql() . " where r.slug = $1 and r.status = 'publicado' and r.deleted_at is null", [$slug]);
        return $row !== null ? $this->hydrate($row) : null;
    }

    /** @return array<string, mixed>|null */
    public function findBySlug(string $slug): ?array
    {
        $row = Database::first($this->detailSql() . ' where r.slug = $1 and r.deleted_at is null', [$slug]);
        return $row !== null ? $this->hydrate($row) : null;
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $row = Database::first($this->detailSql() . ' where r.id = $1 and r.deleted_at is null', [$id]);
        return $row !== null ? $this->hydrate($row) : null;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $slug = $this->uniqueSlug((string) $data['title']);
        $folio = 'REP-' . date('Y') . '-' . str_pad((string) Database::value("select nextval('report_folio_seq')"), 4, '0', STR_PAD_LEFT);
        $row = Database::insert(
            'insert into reports (slug, folio, author_id, title, summary, sector_id, chain_id, state_code, company_type,
                                  period_start, period_end, currency, revenue, cogs, opex, other_income, net_income,
                                  total_assets, current_assets, cash, receivables, inventory, total_liabilities,
                                  current_liabilities, debt, equity, analysis, findings, recommendations, assumptions,
                                  references_text, license, status, featured, is_demo, published_at)
             values ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23,$24,$25,$26,
                     $27,$28,$29,$30,$31,$32,$33,$34,$35,$36)
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
                $data['company_type'] ?? 'mipyme',
                $data['period_start'] ?: null,
                $data['period_end'] ?: null,
                $data['currency'] ?? 'MXN',
                ...$this->numericValues($data),
                $data['analysis'] ?? '',
                $data['findings'] ?? '',
                $data['recommendations'] ?? '',
                $data['assumptions'] ?? '',
                $data['references_text'] ?? '',
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
        $params = [
            $data['title'],
            $data['summary'] ?? '',
            $data['sector_id'] ?: null,
            $data['chain_id'] ?: null,
            $data['state_code'] ?: null,
            $data['company_type'] ?? 'mipyme',
            $data['period_start'] ?: null,
            $data['period_end'] ?: null,
            $data['currency'] ?? 'MXN',
            ...$this->numericValues($data),
            $data['analysis'] ?? '',
            $data['findings'] ?? '',
            $data['recommendations'] ?? '',
            $data['assumptions'] ?? '',
            $data['references_text'] ?? '',
            $data['license'] ?? 'CC BY-NC-SA 4.0',
        ];
        $params[] = $id;

        Database::execute(
            'update reports set title = $1, summary = $2, sector_id = $3, chain_id = $4, state_code = $5,
                    company_type = $6, period_start = $7, period_end = $8, currency = $9,
                    revenue = $10, cogs = $11, opex = $12, other_income = $13, net_income = $14,
                    total_assets = $15, current_assets = $16, cash = $17, receivables = $18, inventory = $19,
                    total_liabilities = $20, current_liabilities = $21, debt = $22, equity = $23,
                    analysis = $24, findings = $25, recommendations = $26, assumptions = $27,
                    references_text = $28, license = $29
             where id = $30',
            $params
        );
    }

    public function updateStatus(int $id, string $status, bool $featured = false): void
    {
        Database::execute(
            'update reports set status = $1, featured = $2,
                    published_at = case when $1 = \'publicado\' and published_at is null then now()
                                        when $1 <> \'publicado\' then null else published_at end
             where id = $3',
            [$status, $featured, $id]
        );
    }

    public function addFile(int $reportId, array $file): void
    {
        Database::execute(
            'insert into report_files (report_id, path, original_name, mime, size_bytes, kind)
             values ($1, $2, $3, $4, $5, $6)',
            [$reportId, $file['path'], $file['original_name'], $file['mime'], $file['size'], $file['kind'] ?? 'anexo']
        );
    }

    public function deleteFile(int $fileId, int $reportId): ?string
    {
        $path = Database::value('select path from report_files where id = $1 and report_id = $2', [$fileId, $reportId]);
        Database::execute('delete from report_files where id = $1 and report_id = $2', [$fileId, $reportId]);
        return is_string($path) ? $path : null;
    }

    /** @return array<int, array<string, mixed>> */
    public function files(int $reportId): array
    {
        return Database::select('select * from report_files where report_id = $1 order by id', [$reportId]);
    }

    /** @return array<int, array<string, mixed>> */
    public function mine(int $authorId): array
    {
        return Database::select(
            "select r.*, s.name as sector_name, s.emoji as sector_emoji, st.name as state_name
             from reports r
             join sectors s on s.id = r.sector_id
             left join states st on st.code = r.state_code
             where r.author_id = $1 and r.deleted_at is null
             order by r.updated_at desc",
            [$authorId]
        );
    }

    /** @return array<string, int> */
    public function stats(): array
    {
        $row = Database::first(
            "select count(*) filter (where status = 'publicado') as published,
                    count(*) filter (where status = 'en_revision') as review,
                    count(*) filter (where status = 'borrador') as drafts,
                    coalesce(sum(views) filter (where status = 'publicado'), 0) as views
             from reports where deleted_at is null"
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
        Database::execute('update reports set views = views + 1 where id = $1', [$id]);
    }

    public function uniqueSlug(string $title): string
    {
        $base = Str::slug($title, 70);
        $slug = $base;
        $i = 2;
        while ((bool) Database::value('select exists(select 1 from reports where slug = $1)', [$slug])) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }

    /** @return array<int, float|null> */
    private function numericValues(array $data): array
    {
        $values = [];
        foreach (self::NUMERIC_FIELDS as $field) {
            $value = $data[$field] ?? null;
            $values[] = ($value === '' || $value === null) ? null : (float) str_replace([',', '$', ' '], '', (string) $value);
        }
        return $values;
    }

    private function detailSql(): string
    {
        return "select r.*, s.name as sector_name, s.code as sector_code, s.emoji as sector_emoji,
                       ch.name as chain_name, ch.code as chain_code, ch.emoji as chain_emoji,
                       st.name as state_name,
                       u.name as author_name, u.email as author_email, u.bio as author_bio,
                       u.avatar_url as author_avatar_url, u.role as author_role, u.status as author_status,
                       u.career as author_career, u.semester as author_semester,
                       un.name as university_name, un.acronym as university_acronym, un.slug as university_slug,
                       (select count(*) from comments cc where cc.target_type = 'reporte' and cc.target_id = r.id and cc.status = 'visible') as comments_count,
                       (select count(*) from reactions cr where cr.target_type = 'reporte' and cr.target_id = r.id) as reactions_count,
                       (select count(*) from bookmarks b where b.target_type = 'reporte' and b.target_id = r.id) as bookmarks_count
                from reports r
                join sectors s on s.id = r.sector_id
                left join chains ch on ch.id = r.chain_id
                left join states st on st.code = r.state_code
                join users u on u.id = r.author_id
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
        $row['comments_count'] = (int) $row['comments_count'];
        $row['reactions_count'] = (int) $row['reactions_count'];
        $row['bookmarks_count'] = (int) ($row['bookmarks_count'] ?? 0);
        $row['featured'] = $row['featured'] === true || $row['featured'] === 't';
        $row['is_demo'] = $row['is_demo'] === true || $row['is_demo'] === 't';
        foreach (self::NUMERIC_FIELDS as $field) {
            $row[$field] = ($row[$field] ?? null) === null ? null : (float) $row[$field];
        }
        return $row;
    }
}
