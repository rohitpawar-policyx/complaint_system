<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__, 2) . '/shared/middleware/authorization.php';
require_once dirname(__DIR__, 2) . '/auth/authService.php';
require_once __DIR__ . '/userService.php';

require_admin();
$adminId = authenticated_user_id();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: userList.php');
    exit;
}

$userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$status = (string) ($_POST['status'] ?? '');

if ($userId === false || !in_array($status, ADMIN_USER_STATUSES, true)) {
    set_auth_flash('error', 'Invalid user status request.');
    header('Location: userList.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_auth_flash('error', 'The form expired. Please try again.');
    header('Location: userDetails.php?id=' . $userId);
    exit;
}

if ($userId === $adminId && $status === 'blocked') {
    set_auth_flash('error', 'You cannot block your own administrator account.');
    header('Location: userDetails.php?id=' . $userId);
    exit;
}

try {
    if (get_admin_user($userId) === null) {
        set_auth_flash('error', 'User not found.');
        header('Location: userList.php');
        exit;
    }

    update_admin_user_status($userId, $status);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    set_auth_flash('error', 'The user status could not be updated right now.');
    header('Location: userDetails.php?id=' . $userId);
    exit;
}

set_auth_flash('success', 'User status updated successfully.');
header('Location: userDetails.php?id=' . $userId);
exit;
