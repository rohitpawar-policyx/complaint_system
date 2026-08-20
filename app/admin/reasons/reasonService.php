<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/config/database.php';

const COMPLAINT_REASON_PRIORITIES = ['LOW', 'MEDIUM', 'HIGH'];

function get_admin_reasons(): array
{
    $statement = database()->prepare(
        'SELECT id, name, description, priority, active, created_at, updated_at
         FROM complaint_reasons
         ORDER BY name ASC'
    );
    $statement->execute();

    return $statement->fetchAll();
}

function get_admin_reason(int $reasonId): ?array
{
    $statement = database()->prepare(
        'SELECT id, name, description, priority, active, created_at, updated_at
         FROM complaint_reasons
         WHERE id = :reason_id
         LIMIT 1'
    );
    $statement->execute(['reason_id' => $reasonId]);
    $reason = $statement->fetch();

    return is_array($reason) ? $reason : null;
}

function create_admin_reason(string $name, string $description, string $priority, bool $active): bool
{
    $statement = database()->prepare(
        'INSERT INTO complaint_reasons (name, description, priority, active)
         VALUES (:name, :description, :priority, :active)'
    );

    return execute_reason_statement($statement, [
        'name' => $name,
        'description' => $description,
        'priority' => $priority,
        'active' => $active ? 1 : 0,
    ]);
}

function update_admin_reason(int $reasonId, string $name, string $description, string $priority, bool $active): bool
{
    $statement = database()->prepare(
        'UPDATE complaint_reasons
         SET name = :name, description = :description,
             priority = :priority, active = :active
         WHERE id = :reason_id'
    );

    return execute_reason_statement($statement, [
        'name' => $name,
        'description' => $description,
        'priority' => $priority,
        'active' => $active ? 1 : 0,
        'reason_id' => $reasonId,
    ]);
}

function delete_admin_reason(int $reasonId): string
{
    $connection = database();
    if (get_admin_reason($reasonId) === null) {
        return 'not_found';
    }

    $referenceStatement = $connection->prepare(
        'SELECT COUNT(*) FROM complaints WHERE reason_id = :reason_id'
    );
    $referenceStatement->execute(['reason_id' => $reasonId]);
    if ((int) $referenceStatement->fetchColumn() > 0) {
        return 'referenced';
    }

    $deleteStatement = $connection->prepare('DELETE FROM complaint_reasons WHERE id = :reason_id');
    $deleteStatement->execute(['reason_id' => $reasonId]);

    return $deleteStatement->rowCount() === 1 ? 'deleted' : 'not_found';
}

function execute_reason_statement(PDOStatement $statement, array $parameters): bool
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
