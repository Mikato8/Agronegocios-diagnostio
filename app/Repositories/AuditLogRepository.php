<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Auth;
use App\Core\Database;

final class AuditLogRepository
{
    /** @param array<string, mixed> $meta */
    public function log(?int $actorId, string $action, string $entityType = '', ?int $entityId = null, array $meta = []): void
    {
        Database::execute(
            'insert into audit_log (actor_id, action, entity_type, entity_id, meta, ip)
             values ($1, $2, $3, $4, $5::jsonb, $6::inet)',
            [
                $actorId,
                $action,
                $entityType,
                $entityId,
                json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
                $this->ip(),
            ]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function recent(int $limit = 40): array
    {
        return Database::select(
            'select a.*, u.name as actor_name from audit_log a
             left join users u on u.id = a.actor_id
             order by a.created_at desc limit $1',
            [$limit]
        );
    }

    private function ip(): string
    {
        $ip = Auth::ip();
        return filter_var($ip, FILTER_VALIDATE_IP) !== false ? $ip : '127.0.0.1';
    }
}
