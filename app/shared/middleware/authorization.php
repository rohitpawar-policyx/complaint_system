<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__, 3) . '/config/database.php';

function require_admin(): void
{
    require_authenticated();

    $userId = authenticated_user_id();
    try {
        $statement = database()->prepare(
            'SELECT roles.name
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             WHERE users.id = :user_id
             LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        $roleName = $statement->fetchColumn();
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        http_response_code(403);
        exit('Access denied.');
    }

    if ($roleName !== 'admin') {
        http_response_code(403);
        exit('Access denied.');
    }
}
