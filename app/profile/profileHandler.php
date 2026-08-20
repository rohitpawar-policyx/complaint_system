<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/shared/security/csrf.php';
require_once dirname(__DIR__) . '/shared/middleware/auth.php';
require_once dirname(__DIR__) . '/auth/authService.php';
require_once __DIR__ . '/profileService.php';

require_authenticated();
$userId = authenticated_user_id();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_auth_flash('error', 'The form expired. Please try again.');
    header('Location: profile.php');
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = normalize_email((string) ($_POST['email'] ?? ''));

if ($name === '' || mb_strlen($name) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_auth_flash('error', 'Enter a valid name and email address.');
    header('Location: profile.php');
    exit;
}

try {
    if (!update_user_profile($userId, $name, $email)) {
        set_auth_flash('error', 'That email address is already in use.');
        header('Location: profile.php');
        exit;
    }
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    set_auth_flash('error', 'The profile could not be updated right now. Please try again later.');
    header('Location: profile.php');
    exit;
}

set_auth_flash('success', 'Your profile was updated successfully.');
header('Location: profile.php');
exit;
