<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/config/database.php';

const PROTECTED_ROLE_NAMES = ['user', 'admin'];

function get_admin_roles(): array
{
    $statement = database()->prepare(
        'SELECT id, name, description, created_at, updated_at
         FROM roles
         ORDER BY name ASC'
    );
    $statement->execute();

    return $statement->fetchAll();
}

function get_admin_role(int $roleId): ?array
{
    $statement = database()->prepare(
        'SELECT id, name, description, created_at, updated_at
         FROM roles
         WHERE id = :role_id
         LIMIT 1'
    );
    $statement->execute(['role_id' => $roleId]);
    $role = $statement->fetch();

    return is_array($role) ? $role : null;
}

function create_admin_role(string $name, string $description): bool
{
    $statement = database()->prepare(
        'INSERT INTO roles (name, description)
         VALUES (:name, :description)'
    );

    return execute_role_statement($statement, [
        'name' => $name,
        'description' => $description,
    ]);
}

function update_admin_role(int $roleId, string $name, string $description): bool
{
    $statement = database()->prepare(
        'UPDATE roles
         SET name = :name, description = :description
         WHERE id = :role_id'
    );

    return execute_role_statement($statement, [
        'name' => $name,
        'description' => $description,
        'role_id' => $roleId,
    ]);
}

function delete_admin_role(int $roleId): string
{
    $connection = database();
    $role = get_admin_role($roleId);

    if ($role === null) {
        return 'not_found';
    }

    if (in_array(strtolower($role['name']), PROTECTED_ROLE_NAMES, true)) {
        return 'protected';
    }

    $userStatement = $connection->prepare(
        'SELECT COUNT(*) FROM users WHERE role_id = :role_id'
    );
    $userStatement->execute(['role_id' => $roleId]);
    if ((int) $userStatement->fetchColumn() > 0) {
        return 'referenced';
    }

    $deleteStatement = $connection->prepare('DELETE FROM roles WHERE id = :role_id');
    $deleteStatement->execute(['role_id' => $roleId]);

    return $deleteStatement->rowCount() === 1 ? 'deleted' : 'not_found';
}

function execute_role_statement(PDOStatement $statement, array $parameters): bool
{
    try {
        return $statement->execute($parameters);
    } catch (PDOException $exception) {
        if ($exception->getCode() === '23000') {
            return false;
        }

        throw $exception;
    }
}
