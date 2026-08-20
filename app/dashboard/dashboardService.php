<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';

function get_user_dashboard_counts(int $userId): array
{
    $statement = database()->prepare(
        "SELECT COUNT(*) AS total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) AS resolved
         FROM complaints
         WHERE user_id = :user_id"
    );
    $statement->execute(['user_id' => $userId]);
    $counts = $statement->fetch() ?: [];

    return array_map('intval', [
        'total' => $counts['total'] ?? 0,
        'pending' => $counts['pending'] ?? 0,
        'in_progress' => $counts['in_progress'] ?? 0,
        'resolved' => $counts['resolved'] ?? 0,
    ]);
}
