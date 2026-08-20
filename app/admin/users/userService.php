<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/config/database.php';

const ADMIN_USER_STATUSES = ['pending', 'approved', 'blocked'];

function get_admin_users(): array
{
    $statement = database()->prepare(
        'SELECT users.id, users.name, users.email, roles.name AS role_name,
                users.status, users.created_at, users.updated_at
         FROM users
         INNER JOIN roles ON roles.id = users.role_id
         ORDER BY users.created_at DESC, users.id DESC'
    );
    $statement->execute();

    return $statement->fetchAll();
}

function get_admin_user(int $userId): ?array
{
    $statement = database()->prepare(
        'SELECT users.id, users.name, users.email, roles.name AS role_name,
                users.status, users.created_at, users.updated_at
         FROM users
         INNER JOIN roles ON roles.id = users.role_id
         WHERE users.id = :user_id
         LIMIT 1'
    );
    $statement->execute(['user_id' => $userId]);
    $user = $statement->fetch();

    return is_array($user) ? $user : null;
}

function update_admin_user_status(int $userId, string $status): bool
{
    $statement = database()->prepare(
        'UPDATE users
         SET status = :status
         WHERE id = :user_id'
    );

    return $statement->execute([
        'status' => $status,
        'user_id' => $userId,
    ]);
}
