<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/config/database.php';

function get_admin_dashboard_counts(): array
{
    $connection = database();

    $userStatement = $connection->prepare(
        "SELECT COUNT(*) AS total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved,
                SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) AS blocked
         FROM users"
    );
    $userStatement->execute();
    $userCounts = $userStatement->fetch() ?: [];

    $complaintStatement = $connection->prepare(
        "SELECT COUNT(*) AS total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) AS resolved,
                SUM(CASE WHEN priority = 'HIGH' THEN 1 ELSE 0 END) AS high_priority_count
         FROM complaints"
    );
    $complaintStatement->execute();
    $complaintCounts = $complaintStatement->fetch() ?: [];

    return [
        'users' => array_map('intval', [
            'total' => $userCounts['total'] ?? 0,
            'pending' => $userCounts['pending'] ?? 0,
            'approved' => $userCounts['approved'] ?? 0,
            'blocked' => $userCounts['blocked'] ?? 0,
        ]),
        'complaints' => array_map('intval', [
            'total' => $complaintCounts['total'] ?? 0,
            'pending' => $complaintCounts['pending'] ?? 0,
            'in_progress' => $complaintCounts['in_progress'] ?? 0,
            'resolved' => $complaintCounts['resolved'] ?? 0,
            'high_priority' => $complaintCounts['high_priority_count'] ?? 0,
        ]),
    ];
}
