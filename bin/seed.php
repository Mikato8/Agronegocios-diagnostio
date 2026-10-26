<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Str;

$options = getopt('', ['demo', 'force', 'no-demo']);
$withDemo = !isset($options['no-demo']);
$force = isset($options['force']);

$catalog = require BASE_PATH . '/database/seeds/catalog.php';

echo "→ Sembrando catálogos…\n";
foreach ($catalog['states'] as [$code, $name, $region]) {
    Database::execute(
        'insert into states (code, name, region) values ($1, $2, $3)
         on conflict (code) do update set name = excluded.name, region = excluded.region',
        [$code, $name, $region]
    );
}

foreach ($catalog['sectors'] as $index => [$code, $name, $emoji, $description]) {
    Database::execute(
        'insert into sectors (code, name, emoji, description, sort_order) values ($1, $2, $3, $4, $5)
         on conflict (code) do update set name = excluded.name, emoji = excluded.emoji,
             description = excluded.description, sort_order = excluded.sort_order',
        [$code, $name, $emoji, $description, ($index + 1) * 10]
    );
}

foreach ($catalog['chains'] as [$code, $name, $emoji, $sector]) {
    Database::execute(
        'insert into chains (code, name, emoji, sector_code) values ($1, $2, $3, $4)
         on conflict (code) do update set name = excluded.name, emoji = excluded.emoji, sector_code = excluded.sector_code',
        [$code, $name, $emoji, $sector]
    );
}

foreach ($catalog['universities'] as [$name, $acronym, $domain, $state, $type, $website, $domains]) {
    $slug = Str::slug($name, 70);
    $arrayLiteral = '{' . implode(',', array_map(static fn (string $item): string => '"' . str_replace('"', '\\"', $item) . '"', $domains)) . '}';
    Database::execute(
        'insert into universities (name, acronym, slug, state_code, type, website, email_domains, is_demo)
         values ($1, $2, $3, $4, $5, $6, $7::text[], true)
         on conflict (slug) do update set name = excluded.name, acronym = excluded.acronym,
             state_code = excluded.state_code, type = excluded.type, website = excluded.website,
             email_domains = excluded.email_domains',
        [$name, $acronym, $slug, $state, $type, $website, $arrayLiteral]
    );
}
echo '  ✓ ' . count($catalog['states']) . ' estados, ' . count($catalog['sectors']) . ' sectores, '
    . count($catalog['chains']) . ' cadenas, ' . count($catalog['universities']) . " universidades\n";

if (!$withDemo) {
    echo "Listo. No se sembró contenido demostrativo.\n";
    exit(0);
}

$existing = (int) Database::value('select count(*) from cases where is_demo = true');
if ($existing > 0 && !$force) {
    echo "· El contenido demostrativo ya existe ({$existing} casos). Usa --force para recrearlo.\n";
    exit(0);
}

if ($force) {
    echo "→ Eliminando contenido demostrativo anterior…\n";
    Database::execute('delete from opportunities where is_demo = true');
    Database::execute('delete from reports where is_demo = true');
    Database::execute('delete from cases where is_demo = true');
}

$demo = require BASE_PATH . '/database/seeds/demo.php';
$more = require BASE_PATH . '/database/seeds/demo_more.php';

$universityByDomain = [];
foreach (Database::select('select id, email_domains from universities') as $row) {
    foreach (pg_array($row['email_domains']) as $domain) {
        $universityByDomain[mb_strtolower($domain)] = (int) $row['id'];
    }
}

$sectorByCode = [];
foreach (Database::select('select id, code from sectors') as $row) {
    $sectorByCode[$row['code']] = (int) $row['id'];
}
$chainByCode = [];
foreach (Database::select('select id, code from chains') as $row) {
    $chainByCode[$row['code']] = (int) $row['id'];
}

$userIds = [];
echo "→ Creando cuentas demostrativas…\n";
foreach ($demo['users'] as $user) {
    $email = Str::normalizeEmail($user['email']);
    $universityId = $universityByDomain[$user['university']] ?? null;
    $status = $user['status'] ?? 'verificado';
    $existingId = Database::value('select id from users where email = $1', [$email]);
    if ($existingId !== null) {
        $userIds[$user['key']] = (int) $existingId;
        continue;
    }
    $row = Database::insert(
        'insert into users (name, email, password_hash, role, status, university_id, career, semester, state_code,
                            bio, verification_method, email_verified, terms_accepted_at, created_at)
         values ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, true, now(), now() - interval \'120 days\')
         returning id',
        [
            $user['name'],
            $email,
            password_hash($demo['password'], PASSWORD_DEFAULT),
            $user['role'],
            $status,
            $universityId,
            $user['career'],
            $user['semester'],
            $user['state'],
            $user['bio'],
            $status === 'verificado' ? 'dominio_institucional' : 'manual',
        ]
    );
    $userIds[$user['key']] = (int) ($row['id'] ?? 0);
}
$admin = (int) Database::value("select id from users where role = 'admin' order by id limit 1");
$reviewer = $userIds['ricardo'] ?? $admin;

$caseIds = [];
echo "→ Publicando casos demostrativos…\n";
foreach ($demo['cases'] as $case) {
    $slug = $case['slug'];
    $existingId = Database::value('select id from cases where slug = $1', [$slug]);
    if ($existingId !== null) {
        $caseIds[$slug] = (int) $existingId;
        continue;
    }
    $folio = 'CAS-DEMO-' . str_pad((string) (count($caseIds) + 1), 3, '0', STR_PAD_LEFT);
    $publishedAt = date('c', strtotime('-' . (int) $case['published_days_ago'] . ' days'));
    $row = Database::insert(
        'insert into cases (slug, folio, author_id, title, summary, sector_id, chain_id, state_code, municipality,
            company_type, stage, year, challenge, context, diagnosis, strategy, results, lessons,
            financial_highlights, kpis, references_text, license, status, featured, is_demo, published_at, created_at)
         values ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21,$22,
                 \'publicado\', $23, true, $24, $24)
         returning id',
        [
            $slug,
            $folio,
            $userIds[$case['author']],
            $case['title'],
            $case['summary'],
            $sectorByCode[$case['sector']] ?? null,
            $chainByCode[$case['chain']] ?? null,
            $case['state'],
            $case['municipality'],
            $case['company_type'],
            $case['stage'],
            $case['year'],
            $case['challenge'],
            $case['context'],
            $case['diagnosis'],
            $case['strategy'],
            $case['results'],
            $case['lessons'],
            $case['financial_highlights'],
            $case['kpis'],
            $case['references_text'],
            $case['license'],
            !empty($case['featured']),
            $publishedAt,
        ]
    );
    $caseIds[$slug] = (int) ($row['id'] ?? 0);
    if ($reviewer > 0) {
        Database::execute(
            "insert into reviews (target_type, target_id, reviewer_id, decision, notes, created_at)
             values ('caso', $1, $2, 'publicar', $3, now() - interval '3 days')",
            [$caseIds[$slug], $reviewer, 'Caso claro, con contexto, cifras y aprendizajes. Aprobado para publicación.']
        );
    }
}

$reportIds = [];
echo "→ Publicando reportes financieros…\n";
foreach ($more['reports'] as $report) {
    $slug = $report['slug'];
    $existingId = Database::value('select id from reports where slug = $1', [$slug]);
    if ($existingId !== null) {
        $reportIds[$slug] = (int) $existingId;
        continue;
    }
    $folio = 'REP-DEMO-' . str_pad((string) (count($reportIds) + 1), 3, '0', STR_PAD_LEFT);
    $publishedAt = date('c', strtotime('-' . (int) $report['published_days_ago'] . ' days'));
    $row = Database::insert(
        'insert into reports (slug, folio, author_id, title, summary, sector_id, chain_id, state_code, company_type,
            period_start, period_end, currency, revenue, cogs, opex, other_income, net_income, total_assets,
            current_assets, cash, receivables, inventory, total_liabilities, current_liabilities, debt, equity,
            analysis, findings, recommendations, assumptions, references_text, license, status, featured, is_demo,
            published_at, created_at)
         values ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23,$24,$25,$26,
                 $27,$28,$29,$30,$31,$32,\'publicado\',$33,true,$34,$34)
         returning id',
        [
            $slug, $folio, $userIds[$report['author']], $report['title'], $report['summary'],
            $sectorByCode[$report['sector']] ?? null, $chainByCode[$report['chain']] ?? null,
            $report['state'], $report['company_type'], $report['period_start'], $report['period_end'],
            $report['currency'], $report['revenue'], $report['cogs'], $report['opex'], $report['other_income'],
            $report['net_income'], $report['total_assets'], $report['current_assets'], $report['cash'],
            $report['receivables'], $report['inventory'], $report['total_liabilities'], $report['current_liabilities'],
            $report['debt'], $report['equity'], $report['analysis'], $report['findings'], $report['recommendations'],
            $report['assumptions'], $report['references_text'], $report['license'], !empty($report['featured']),
            $publishedAt,
        ]
    );
    $reportIds[$slug] = (int) ($row['id'] ?? 0);
    if ($reviewer > 0) {
        Database::execute(
            "insert into reviews (target_type, target_id, reviewer_id, decision, notes, created_at)
             values ('reporte', $1, $2, 'publicar', $3, now() - interval '2 days')",
            [$reportIds[$slug], $reviewer, 'Cifras consistentes y análisis con recomendaciones verificables.']
        );
    }
}

$opportunityIds = [];
echo "→ Publicando oportunidades…\n";
foreach ($more['opportunities'] as $opportunity) {
    $slug = $opportunity['slug'];
    $existingId = Database::value('select id from opportunities where slug = $1', [$slug]);
    if ($existingId !== null) {
        $opportunityIds[$slug] = (int) $existingId;
        continue;
    }
    $folio = 'OPO-DEMO-' . str_pad((string) (count($opportunityIds) + 1), 3, '0', STR_PAD_LEFT);
    $publishedAt = date('c', strtotime('-' . (int) $opportunity['published_days_ago'] . ' days'));
    $expiresAt = date('Y-m-d', strtotime('+' . (int) $opportunity['expires_in_days'] . ' days'));
    $row = Database::insert(
        'insert into opportunities (slug, folio, author_id, title, summary, sector_id, chain_id, state_code,
            municipality, opportunity_type, stage, investment_min, investment_max, currency, expected_return_pct,
            payback_months, market_size, description, value_proposition, requirements, risks, contact_name,
            contact_email, contact_phone, is_confidential, expires_at, license, status, featured, is_demo,
            published_at, created_at)
         values ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23,$24,$25,$26,
                 $27,\'publicado\',$28,true,$29,$29)
         returning id',
        [
            $slug, $folio, $userIds[$opportunity['author']], $opportunity['title'], $opportunity['summary'],
            $sectorByCode[$opportunity['sector']] ?? null, $chainByCode[$opportunity['chain']] ?? null,
            $opportunity['state'], $opportunity['municipality'], $opportunity['opportunity_type'],
            $opportunity['stage'], $opportunity['investment_min'], $opportunity['investment_max'],
            $opportunity['currency'], $opportunity['expected_return_pct'], $opportunity['payback_months'],
            $opportunity['market_size'], $opportunity['description'], $opportunity['value_proposition'],
            $opportunity['requirements'], $opportunity['risks'], $opportunity['contact_name'],
            $opportunity['contact_email'], $opportunity['contact_phone'], !empty($opportunity['is_confidential']),
            $expiresAt, $opportunity['license'], !empty($opportunity['featured']), $publishedAt,
        ]
    );
    $opportunityIds[$slug] = (int) ($row['id'] ?? 0);
    if ($reviewer > 0) {
        Database::execute(
            "insert into reviews (target_type, target_id, reviewer_id, decision, notes, created_at)
             values ('oportunidad', $1, $2, 'publicar', $3, now() - interval '1 day')",
            [$opportunityIds[$slug], $reviewer, 'Oportunidad con mercado, inversión y riesgos explícitos.']
        );
    }
}

echo "→ Sembrando comentarios, reacciones e intereses…\n";
$targetIds = [
    'caso' => $caseIds,
    'reporte' => $reportIds,
    'oportunidad' => $opportunityIds,
];

foreach ($more['comments'] as $comment) {
    $targetId = $targetIds[$comment['type']][$comment['slug']] ?? null;
    $userId = $userIds[$comment['author']] ?? null;
    if ($targetId === null || $userId === null) {
        continue;
    }
    $exists = Database::value(
        'select 1 from comments where target_type = $1 and target_id = $2 and user_id = $3 limit 1',
        [$comment['type'], $targetId, $userId]
    );
    if ($exists !== null) {
        continue;
    }
    Database::execute(
        'insert into comments (target_type, target_id, user_id, body, created_at) values ($1, $2, $3, $4, now() - interval \'2 days\')',
        [$comment['type'], $targetId, $userId, $comment['body']]
    );
}

$reactionAuthors = ['ana', 'carlos', 'lucia', 'diego', 'mariana', 'emilio', 'ximena'];
$reactionTargets = [
    ['caso', $caseIds['maiz-azul-nixtamal-tlaxcala'] ?? null],
    ['caso', $caseIds['yogurt-artesanal-altos-jalisco'] ?? null],
    ['caso', $caseIds['aguacate-hass-japon-certificacion'] ?? null],
    ['reporte', $reportIds['empacadora-limon-persa-veracruz-2025'] ?? null],
    ['reporte', $reportIds['granja-avicola-jalisco-2025'] ?? null],
    ['oportunidad', $opportunityIds['proveeduria-aguacate-organico-retail-cdmx'] ?? null],
];
foreach ($reactionTargets as $index => [$type, $targetId]) {
    if (!$targetId) {
        continue;
    }
    foreach (array_slice($reactionAuthors, 0, 3 + ($index % 3)) as $authorKey) {
        $userId = $userIds[$authorKey] ?? null;
        if (!$userId) {
            continue;
        }
        Database::execute(
            'insert into reactions (target_type, target_id, user_id) values ($1, $2, $3) on conflict do nothing',
            [$type, $targetId, $userId]
        );
    }
}

foreach ($more['interests'] as $interest) {
    $opportunityId = $opportunityIds[$interest['slug']] ?? null;
    $userId = $userIds[$interest['author']] ?? null;
    if (!$opportunityId || !$userId) {
        continue;
    }
    Database::execute(
        'insert into interests (opportunity_id, user_id, message, status) values ($1, $2, $3, $4) on conflict do nothing',
        [$opportunityId, $userId, $interest['message'], $interest['status']]
    );
}

foreach (['maiz-azul-nixtamal-tlaxcala', 'cafe-especialidad-sierra-norte-oaxaca', 'empacadora-limon-persa-veracruz-2025'] as $slug) {
    $caseId = $caseIds[$slug] ?? null;
    $userId = $userIds['ana'] ?? null;
    if ($caseId && $userId && $slug !== 'maiz-azul-nixtamal-tlaxcala') {
        Database::execute(
            "insert into bookmarks (target_type, target_id, user_id) values ('caso', $1, $2) on conflict do nothing",
            [$caseId, $userId]
        );
    }
}

echo "  ✓ {$demo['password']} es la contraseña de las cuentas demostrativas.\n";
echo "Listo. Cuentas: " . count($userIds) . ", casos: " . count($caseIds) . ", reportes: " . count($reportIds)
    . ', oportunidades: ' . count($opportunityIds) . ".\n";
