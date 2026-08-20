<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';

function get_user_profile(int $userId): ?array
{
    $statement = database()->prepare(
        'SELECT users.id, users.name, users.email, users.status, users.created_at,
                roles.name AS role_name
         FROM users
         INNER JOIN roles ON roles.id = users.role_id
         WHERE users.id = :user_id
         LIMIT 1'
    );
    $statement->execute(['user_id' => $userId]);
    $profile = $statement->fetch();

    return is_array($profile) ? $profile : null;
}

function update_user_profile(int $userId, string $name, string $email): bool
{
    $statement = database()->prepare(
        'UPDATE users
         SET name = :name, email = :email
         WHERE id = :user_id'
    );

    try {
        return $statement->execute([
            'name' => $name,
            'email' => $email,
            'user_id' => $userId,
        ]);
    } catch (PDOException $exception) {
        if ($exception->getCode() === '23000') {
            return false;
        }

        throw $exception;
    }
}
