<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__, 2) . '/shared/middleware/authorization.php';
require_once dirname(__DIR__, 2) . '/auth/authService.php';
require_once __DIR__ . '/reasonService.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_auth_flash('error', 'Invalid complaint reason request.');
    header('Location: reasonList.php');
    exit;
}

$action = (string) ($_POST['action'] ?? '');
$reasonId = filter_var($_POST['reason_id'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

try {
    if ($action === 'delete') {
        if ($reasonId === false) {
            set_auth_flash('error', 'Invalid complaint reason.');
            header('Location: reasonList.php');
            exit;
        }

        $result = delete_admin_reason($reasonId);
        [$type, $message] = match ($result) {
            'deleted' => ['success', 'Complaint reason deleted successfully.'],
            'referenced' => ['error', 'This reason is used by complaints and cannot be deleted. Deactivate it instead.'],
            default => ['error', 'Complaint reason not found.'],
        };
        set_auth_flash($type, $message);
        header('Location: reasonList.php');
        exit;
    }

    $name = trim((string) ($_POST['name'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $priority = (string) ($_POST['priority'] ?? '');
    $active = ($_POST['active'] ?? '') === '1';

    if ($name === '' || mb_strlen($name) > 150 || !in_array($priority, COMPLAINT_REASON_PRIORITIES, true)) {
        set_auth_flash('error', 'Enter a valid reason name and priority.');
        header('Location: reasonForm.php' . ($reasonId !== false ? '?id=' . $reasonId : ''));
        exit;
    }

    if ($action === 'create') {
        $saved = create_admin_reason($name, $description, $priority, $active);
    } elseif ($action === 'update' && $reasonId !== false) {
        $saved = update_admin_reason($reasonId, $name, $description, $priority, $active);
    } else {
        set_auth_flash('error', 'Invalid complaint reason request.');
        header('Location: reasonList.php');
        exit;
    }

    set_auth_flash($saved ? 'success' : 'error', $saved ? 'Complaint reason saved successfully.' : 'That complaint reason name is already in use.');
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    set_auth_flash('error', 'The complaint reason could not be saved right now.');
}

header('Location: reasonList.php');
exit;
