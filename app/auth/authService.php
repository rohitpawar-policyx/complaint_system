<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__) . '/shared/security/csrf.php';
require_once dirname(__DIR__) . '/shared/security/session.php';

function normalize_email(string $email): string
{
    return strtolower(trim($email));
}

function register_user(string $name, string $email, string $password): bool
{
    $connection = database();
    $roleStatement = $connection->prepare('SELECT id FROM roles WHERE name = :name LIMIT 1');
    $roleStatement->execute(['name' => 'user']);
    $roleId = $roleStatement->fetchColumn();

    if ($roleId === false) {
        return false;
    }

    $statement = $connection->prepare(
        'INSERT INTO users (name, email, password, status, role_id)
         VALUES (:name, :email, :password, :status, :role_id)'
    );

    try {
        $statement->execute([
            'name' => $name,
            'email' => normalize_email($email),
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'status' => 'pending',
            'role_id' => $roleId,
        ]);
    } catch (PDOException $exception) {
        if ($exception->getCode() === '23000') {
            return false;
        }

        throw $exception;
    }

    return true;
}

function authenticate_user(string $email, string $password): ?array
{
    $statement = database()->prepare(
        'SELECT users.id, users.password, users.status, roles.name AS role_name
         FROM users
         INNER JOIN roles ON roles.id = users.role_id
         WHERE users.email = :email
         LIMIT 1'
    );
    $statement->execute(['email' => normalize_email($email)]);
    $user = $statement->fetch();

    if (!is_array($user) || !password_verify($password, $user['password'])) {
        return null;
    }

    if ($user['status'] !== 'approved') {
        return null;
    }

    return [
        'id' => (int) $user['id'],
        'role_name' => (string) $user['role_name'],
    ];
}

function login_user(array $user): void
{
    start_secure_session();
    session_regenerate_id(true);

    $_SESSION = [
        'user_id' => $user['id'],
        'role_name' => $user['role_name'],
        'csrf_token' => bin2hex(random_bytes(32)),
    ];
}

function logout_user(): void
{
    start_secure_session();
    $_SESSION = [];

    $cookieParameters = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $cookieParameters['path'] ?: '/',
        'domain' => $cookieParameters['domain'],
        'secure' => $cookieParameters['secure'],
        'httponly' => $cookieParameters['httponly'],
        'samesite' => $cookieParameters['samesite'] ?? 'Lax',
    ]);

    session_destroy();
}

function set_auth_flash(string $type, string $message): void
{
    start_secure_session();
    $_SESSION['auth_flash'] = ['type' => $type, 'message' => $message];
}

function consume_auth_flash(): ?array
{
    start_secure_session();
    $flash = $_SESSION['auth_flash'] ?? null;
    unset($_SESSION['auth_flash']);

    return is_array($flash) ? $flash : null;
}
