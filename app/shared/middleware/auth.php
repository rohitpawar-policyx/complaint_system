<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/security/session.php';
require_once dirname(__DIR__, 3) . '/config/database.php';

function authenticated_user_id(): ?int
{
    start_secure_session();

    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function require_authenticated(): void
{
    $userId = authenticated_user_id();
    if ($userId === null) {
        http_response_code(401);
        exit('Authentication required.');
    }

    try {
        $statement = database()->prepare(
            'SELECT status FROM users WHERE id = :user_id LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        $status = $statement->fetchColumn();
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        http_response_code(401);
        exit('Authentication required.');
    }

    if ($status !== 'approved') {
        http_response_code(401);
        exit('Authentication required.');
    }
}
