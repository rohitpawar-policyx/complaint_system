<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__) . '/authService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_auth_flash('error', 'The form expired. Please try again.');
    header('Location: login.php');
    exit;
}

$email = normalize_email((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    set_auth_flash('error', 'Invalid email or password.');
    header('Location: login.php');
    exit;
}

try {
    $user = authenticate_user($email, $password);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    set_auth_flash('error', 'Login is temporarily unavailable. Please try again later.');
    header('Location: login.php');
    exit;
}

if ($user === null) {
    set_auth_flash('error', 'Invalid email or password, or the account is not approved.');
    header('Location: login.php');
    exit;
}

login_user($user);
header('Location: ../../../index.php');
exit;
