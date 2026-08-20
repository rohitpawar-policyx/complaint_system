<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__, 2) . '/shared/middleware/authorization.php';
require_once dirname(__DIR__, 2) . '/auth/authService.php';
require_once __DIR__ . '/roleService.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_auth_flash('error', 'Invalid role request.');
    header('Location: roleList.php');
    exit;
}

$action = (string) ($_POST['action'] ?? '');
$roleId = filter_var($_POST['role_id'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

try {
    if ($action === 'delete') {
        if ($roleId === false) {
            set_auth_flash('error', 'Invalid role.');
            header('Location: roleList.php');
            exit;
        }

        $result = delete_admin_role($roleId);
        $messages = [
            'deleted' => ['success', 'Role deleted successfully.'],
            'protected' => ['error', 'System roles cannot be deleted.'],
            'referenced' => ['error', 'This role is assigned to users and cannot be deleted.'],
            'not_found' => ['error', 'Role not found.'],
        ];
        [$type, $message] = $messages[$result];
        set_auth_flash($type, $message);
        header('Location: roleList.php');
        exit;
    }

    $name = trim((string) ($_POST['name'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    if ($name === '' || mb_strlen($name) > 50 || mb_strlen($description) > 255) {
        set_auth_flash('error', 'Enter a valid role name and description.');
        header('Location: roleForm.php' . ($roleId !== false ? '?id=' . $roleId : ''));
        exit;
    }

    if ($action === 'create') {
        $saved = create_admin_role($name, $description);
    } elseif ($action === 'update' && $roleId !== false) {
        $existingRole = get_admin_role($roleId);
        if ($existingRole === null) {
            set_auth_flash('error', 'Role not found.');
            header('Location: roleList.php');
            exit;
        }

        if (in_array(strtolower($existingRole['name']), PROTECTED_ROLE_NAMES, true)
            && strtolower($existingRole['name']) !== strtolower($name)) {
            set_auth_flash('error', 'System role names cannot be changed.');
            header('Location: roleForm.php?id=' . $roleId);
            exit;
        }

        $saved = update_admin_role($roleId, $name, $description);
    } else {
        set_auth_flash('error', 'Invalid role request.');
        header('Location: roleList.php');
        exit;
    }

    set_auth_flash($saved ? 'success' : 'error', $saved ? 'Role saved successfully.' : 'That role name is already in use.');
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    set_auth_flash('error', 'The role could not be saved right now.');
}

header('Location: roleList.php');
exit;
