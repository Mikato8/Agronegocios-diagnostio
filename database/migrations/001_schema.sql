-- ===========================================================================
-- AgroNegocios MX · esquema base
-- PostgreSQL 15+ (probado en Neon PostgreSQL 18)
-- ===========================================================================

create extension if not exists citext;
create extension if not exists pg_trgm;

-- ---------------------------------------------------------------------------
-- Catálogos geográficos y sectoriales
-- ---------------------------------------------------------------------------
create table if not exists states (
    code varchar(5) primary key,
    name text not null,
    region text not null default 'Centro'
);

create table if not exists sectors (
    id serial primary key,
    code text not null unique,
    name text not null,
    emoji text not null default '🌱',
    description text not null default '',
    sort_order int not null default 100
);

create table if not exists chains (
    id serial primary key,
    code text not null unique,
    name text not null,
    emoji text not null default '🌾',
    sector_code text,
    description text not null default ''
);

create table if not exists universities (
    id serial primary key,
    name text not null,
    acronym text not null default '',
    slug text not null unique,
    state_code varchar(5) references states(code),
    type text not null default 'publica' check (type in ('publica', 'privada')),
    website text not null default '',
    email_domains text[] not null default '{}',
    has_agronegocios boolean not null default true,
    is_demo boolean not null default false,
    created_at timestamptz not null default now()
);

-- ---------------------------------------------------------------------------
-- Cuentas
-- ---------------------------------------------------------------------------
create table if not exists users (
    id bigserial primary key,
    name text not null,
    email citext not null unique,
    password_hash text not null,
    role text not null default 'estudiante' check (role in ('estudiante', 'docente', 'editor', 'admin')),
    status text not null default 'pendiente' check (status in ('pendiente', 'verificado', 'suspendido')),
    university_id int references universities(id) on delete set null,
    institution_other text not null default '',
    career text not null default '',
    semester int check (semester between 1 and 12),
    student_id text not null default '',
    state_code varchar(5) references states(code),
    bio text not null default '',
    avatar_url text,
    proof_path text,
    verification_method text not null default 'manual' check (verification_method in ('dominio_institucional', 'manual', 'admin')),
    verification_note text not null default '',
    terms_accepted_at timestamptz,
    email_verified boolean not null default false,
    last_login_at timestamptz,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    deleted_at timestamptz
);

create index if not exists users_status_idx on users (status) where deleted_at is null;
create index if not exists users_university_idx on users (university_id);

-- ---------------------------------------------------------------------------
-- Casos de agronegocios
-- ---------------------------------------------------------------------------
create table if not exists cases (
    id bigserial primary key,
    slug text not null unique,
    folio text not null unique,
    author_id bigint not null references users(id) on delete restrict,
    title text not null,
    summary text not null default '',
    sector_id int references sectors(id) on delete set null,
    chain_id int references chains(id) on delete set null,
    state_code varchar(5) references states(code),
    municipality text not null default '',
    company_type text not null default 'mipyme' check (company_type in ('micro', 'pequena', 'mediana', 'grande', 'cooperativa', 'ejido', 'asociacion')),
    stage text not null default 'operacion' check (stage in ('idea', 'arranque', 'operacion', 'crecimiento', 'consolidacion', 'crisis')),
    year int not null default extract(year from now())::int,
    challenge text not null default '',
    context text not null default '',
    diagnosis text not null default '',
    strategy text not null default '',
    results text not null default '',
    lessons text not null default '',
    financial_highlights text not null default '',
    kpis text not null default '',
    references_text text not null default '',
    license text not null default 'CC BY-NC-SA 4.0',
    status text not null default 'borrador' check (status in ('borrador', 'en_revision', 'cambios_solicitados', 'publicado', 'rechazado', 'archivado')),
    featured boolean not null default false,
    views int not null default 0,
    is_demo boolean not null default false,
    published_at timestamptz,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    deleted_at timestamptz,
    search_vector tsvector
);

create index if not exists cases_status_idx on cases (status, published_at desc) where deleted_at is null;
create index if not exists cases_sector_idx on cases (sector_id);
create index if not exists cases_state_idx on cases (state_code);
create index if not exists cases_search_idx on cases using gin (search_vector);
create index if not exists cases_title_trgm_idx on cases using gin (title gin_trgm_ops);
create index if not exists cases_author_idx on cases (author_id);

create table if not exists case_images (
    id bigserial primary key,
    case_id bigint not null references cases(id) on delete cascade,
    path text not null,
    caption text not null default '',
    alt_text text not null default '',
    width int not null default 0,
    height int not null default 0,
    sort_order int not null default 0,
    created_at timestamptz not null default now()
);

create index if not exists case_images_case_idx on case_images (case_id, sort_order);

-- ---------------------------------------------------------------------------
-- Reportes financieros
-- ---------------------------------------------------------------------------
create table if not exists reports (
    id bigserial primary key,
    slug text not null unique,
    folio text not null unique,
    author_id bigint not null references users(id) on delete restrict,
    title text not null,
    summary text not null default '',
    sector_id int references sectors(id) on delete set null,
    chain_id int references chains(id) on delete set null,
    state_code varchar(5) references states(code),
    company_type text not null default 'mipyme' check (company_type in ('micro', 'pequena', 'mediana', 'grande', 'cooperativa', 'ejido', 'asociacion')),
    period_start date,
    period_end date,
    currency char(3) not null default 'MXN',
    revenue numeric(16,2),
    cogs numeric(16,2),
    opex numeric(16,2),
    other_income numeric(16,2),
    net_income numeric(16,2),
    total_assets numeric(16,2),
    current_assets numeric(16,2),
    cash numeric(16,2),
    receivables numeric(16,2),
    inventory numeric(16,2),
    total_liabilities numeric(16,2),
    current_liabilities numeric(16,2),
    debt numeric(16,2),
    equity numeric(16,2),
    analysis text not null default '',
    findings text not null default '',
    recommendations text not null default '',
    assumptions text not null default '',
    references_text text not null default '',
    license text not null default 'CC BY-NC-SA 4.0',
    status text not null default 'borrador' check (status in ('borrador', 'en_revision', 'cambios_solicitados', 'publicado', 'rechazado', 'archivado')),
    featured boolean not null default false,
    views int not null default 0,
    is_demo boolean not null default false,
    published_at timestamptz,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    deleted_at timestamptz,
    search_vector tsvector
);

create index if not exists reports_status_idx on reports (status, published_at desc) where deleted_at is null;
create index if not exists reports_search_idx on reports using gin (search_vector);
create index if not exists reports_sector_idx on reports (sector_id);

create table if not exists report_files (
    id bigserial primary key,
    report_id bigint not null references reports(id) on delete cascade,
    path text not null,
    original_name text not null,
    mime text not null,
    size_bytes bigint not null default 0,
    kind text not null default 'anexo',
    created_at timestamptz not null default now()
);

create index if not exists report_files_report_idx on report_files (report_id);

-- ---------------------------------------------------------------------------
-- Oportunidades de agronegocios
-- ---------------------------------------------------------------------------
create table if not exists opportunities (
    id bigserial primary key,
    slug text not null unique,
    folio text not null unique,
    author_id bigint not null references users(id) on delete restrict,
    title text not null,
    summary text not null default '',
    sector_id int references sectors(id) on delete set null,
    chain_id int references chains(id) on delete set null,
    state_code varchar(5) references states(code),
    municipality text not null default '',
    opportunity_type text not null default 'produccion' check (opportunity_type in ('produccion', 'comercializacion', 'agroindustria', 'servicios', 'exportacion', 'turismo_rural', 'financiamiento', 'tecnologia')),
    stage text not null default 'idea' check (stage in ('idea', 'validacion', 'operacion', 'expansion')),
    investment_min numeric(16,2),
    investment_max numeric(16,2),
    currency char(3) not null default 'MXN',
    expected_return_pct numeric(6,2),
    payback_months int,
    market_size text not null default '',
    description text not null default '',
    value_proposition text not null default '',
    requirements text not null default '',
    risks text not null default '',
    contact_name text not null default '',
    contact_email citext,
    contact_phone text not null default '',
    is_confidential boolean not null default false,
    expires_at date,
    license text not null default 'CC BY-NC-SA 4.0',
    status text not null default 'borrador' check (status in ('borrador', 'en_revision', 'cambios_solicitados', 'publicado', 'rechazado', 'archivado')),
    featured boolean not null default false,
    views int not null default 0,
    is_demo boolean not null default false,
    published_at timestamptz,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    deleted_at timestamptz,
    search_vector tsvector
);

create index if not exists opportunities_status_idx on opportunities (status, published_at desc) where deleted_at is null;
create index if not exists opportunities_search_idx on opportunities using gin (search_vector);
create index if not exists opportunities_sector_idx on opportunities (sector_id);
create index if not exists opportunities_state_idx on opportunities (state_code);

-- ---------------------------------------------------------------------------
-- Interacción comunitaria
-- ---------------------------------------------------------------------------
create table if not exists comments (
    id bigserial primary key,
    target_type text not null check (target_type in ('caso', 'reporte', 'oportunidad')),
    target_id bigint not null,
    user_id bigint not null references users(id) on delete cascade,
    body text not null,
    status text not null default 'visible' check (status in ('visible', 'oculto')),
    created_at timestamptz not null default now()
);

create index if not exists comments_target_idx on comments (target_type, target_id, created_at desc);
create index if not exists comments_user_idx on comments (user_id);

create table if not exists reactions (
    id bigserial primary key,
    target_type text not null check (target_type in ('caso', 'reporte', 'oportunidad')),
    target_id bigint not null,
    user_id bigint not null references users(id) on delete cascade,
    created_at timestamptz not null default now(),
    unique (target_type, target_id, user_id)
);

create index if not exists reactions_target_idx on reactions (target_type, target_id);

create table if not exists bookmarks (
    id bigserial primary key,
    target_type text not null check (target_type in ('caso', 'reporte', 'oportunidad')),
    target_id bigint not null,
    user_id bigint not null references users(id) on delete cascade,
    created_at timestamptz not null default now(),
    unique (target_type, target_id, user_id)
);

create index if not exists bookmarks_user_idx on bookmarks (user_id, created_at desc);

create table if not exists reviews (
    id bigserial primary key,
    target_type text not null check (target_type in ('caso', 'reporte', 'oportunidad')),
    target_id bigint not null,
    reviewer_id bigint not null references users(id) on delete restrict,
    decision text not null check (decision in ('publicar', 'cambios', 'rechazar')),
    notes text not null default '',
    created_at timestamptz not null default now()
);

create index if not exists reviews_target_idx on reviews (target_type, target_id, created_at desc);

create table if not exists interests (
    id bigserial primary key,
    opportunity_id bigint not null references opportunities(id) on delete cascade,
    user_id bigint not null references users(id) on delete cascade,
    message text not null default '',
    status text not null default 'nuevo' check (status in ('nuevo', 'contactado', 'cerrado')),
    created_at timestamptz not null default now(),
    unique (opportunity_id, user_id)
);

-- ---------------------------------------------------------------------------
-- Seguridad y auditoría
-- ---------------------------------------------------------------------------
create table if not exists login_attempts (
    id bigserial primary key,
    email citext not null,
    ip inet,
    successful boolean not null default false,
    created_at timestamptz not null default now()
);

create index if not exists login_attempts_idx on login_attempts (created_at desc);

create table if not exists password_resets (
    id bigserial primary key,
    user_id bigint not null references users(id) on delete cascade,
    token_hash text not null,
    expires_at timestamptz not null,
    used_at timestamptz,
    created_at timestamptz not null default now()
);

create index if not exists password_resets_idx on password_resets (token_hash);

create table if not exists audit_log (
    id bigserial primary key,
    actor_id bigint references users(id) on delete set null,
    action text not null,
    entity_type text not null default '',
    entity_id bigint,
    meta jsonb not null default '{}'::jsonb,
    ip inet,
    created_at timestamptz not null default now()
);

create index if not exists audit_log_idx on audit_log (created_at desc);

-- ---------------------------------------------------------------------------
-- Búsqueda full-text y updated_at
-- ---------------------------------------------------------------------------
create or replace function cases_search_refresh() returns trigger
language plpgsql as $$
begin
    new.search_vector := to_tsvector('spanish',
        coalesce(new.title, '') || ' ' ||
        coalesce(new.summary, '') || ' ' ||
        coalesce(new.challenge, '') || ' ' ||
        coalesce(new.context, '') || ' ' ||
        coalesce(new.diagnosis, '') || ' ' ||
        coalesce(new.strategy, '') || ' ' ||
        coalesce(new.results, '') || ' ' ||
        coalesce(new.lessons, '') || ' ' ||
        coalesce(new.kpis, '')
    );
    return new;
end;
$$;

drop trigger if exists cases_search_trigger on cases;
create trigger cases_search_trigger
before insert or update on cases
for each row execute function cases_search_refresh();

create or replace function reports_search_refresh() returns trigger
language plpgsql as $$
begin
    new.search_vector := to_tsvector('spanish',
        coalesce(new.title, '') || ' ' ||
        coalesce(new.summary, '') || ' ' ||
        coalesce(new.analysis, '') || ' ' ||
        coalesce(new.findings, '') || ' ' ||
        coalesce(new.recommendations, '') || ' ' ||
        coalesce(new.assumptions, '')
    );
    return new;
end;
$$;

drop trigger if exists reports_search_trigger on reports;
create trigger reports_search_trigger
before insert or update on reports
for each row execute function reports_search_refresh();

create or replace function opportunities_search_refresh() returns trigger
language plpgsql as $$
begin
    new.search_vector := to_tsvector('spanish',
        coalesce(new.title, '') || ' ' ||
        coalesce(new.summary, '') || ' ' ||
        coalesce(new.description, '') || ' ' ||
        coalesce(new.value_proposition, '') || ' ' ||
        coalesce(new.requirements, '') || ' ' ||
        coalesce(new.market_size, '')
    );
    return new;
end;
$$;

drop trigger if exists opportunities_search_trigger on opportunities;
create trigger opportunities_search_trigger
before insert or update on opportunities
for each row execute function opportunities_search_refresh();

create or replace function touch_updated_at() returns trigger
language plpgsql as $$
begin
    new.updated_at := now();
    return new;
end;
$$;

drop trigger if exists users_touch on users;
create trigger users_touch before update on users
for each row execute function touch_updated_at();

drop trigger if exists cases_touch on cases;
create trigger cases_touch before update on cases
for each row execute function touch_updated_at();

drop trigger if exists reports_touch on reports;
create trigger reports_touch before update on reports
for each row execute function touch_updated_at();

drop trigger if exists opportunities_touch on opportunities;
create trigger opportunities_touch before update on opportunities
for each row execute function touch_updated_at();

-- Folios legibles por tipo de contenido
create sequence if not exists case_folio_seq start 1;
create sequence if not exists report_folio_seq start 1;
create sequence if not exists opportunity_folio_seq start 1;
