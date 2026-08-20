<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__) . '/authService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_auth_flash('error', 'The form expired. Please try again.');
    header('Location: register.php');
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = normalize_email((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$confirmation = (string) ($_POST['password_confirmation'] ?? '');

if ($name === '' || mb_strlen($name) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_auth_flash('error', 'Enter a valid name and email address.');
    header('Location: register.php');
    exit;
}

if (strlen($password) < 8 || $password !== $confirmation) {
    set_auth_flash('error', 'Use a password of at least 8 characters and confirm it correctly.');
    header('Location: register.php');
    exit;
}

try {
    if (!register_user($name, $email, $password)) {
        set_auth_flash('error', 'Unable to register with those details. The email may already be in use.');
        header('Location: register.php');
        exit;
    }
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    set_auth_flash('error', 'Registration is temporarily unavailable. Please try again later.');
    header('Location: register.php');
    exit;
}

set_auth_flash('success', 'Registration submitted. Your account will be available after approval.');
header('Location: ../login/login.php');
exit;
