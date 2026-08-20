<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__, 2) . '/shared/middleware/authorization.php';
require_once dirname(__DIR__, 2) . '/auth/authService.php';
require_once __DIR__ . '/complaintService.php';

require_admin();
$adminId = authenticated_user_id();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_auth_flash('error', 'Invalid complaint request.');
    header('Location: complaintList.php');
    exit;
}

$action = (string) ($_POST['action'] ?? '');
$complaintId = filter_var($_POST['complaint_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($complaintId === false) {
    set_auth_flash('error', 'Invalid complaint.');
    header('Location: complaintList.php');
    exit;
}

try {
    if ($action === 'assign') {
        $assigneeId = filter_var($_POST['assignee_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($assigneeId === false) {
            throw new InvalidArgumentException('Invalid assignee.');
        }
        assign_admin_complaint($complaintId, $assigneeId, $adminId);
        set_auth_flash('success', 'Complaint assignment updated.');
    } elseif ($action === 'status') {
        $status = (string) ($_POST['status'] ?? '');
        if (!in_array($status, ADMIN_COMPLAINT_STATUSES, true)) {
            throw new InvalidArgumentException('Invalid complaint status.');
        }
        change_admin_complaint_status($complaintId, $status, $adminId);
        set_auth_flash('success', 'Complaint status updated.');
    } else {
        throw new InvalidArgumentException('Invalid complaint request.');
    }
} catch (InvalidArgumentException $exception) {
    set_auth_flash('error', $exception->getMessage());
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    set_auth_flash('error', 'The complaint could not be updated right now.');
}

header('Location: complaintDetails.php?id=' . $complaintId);
exit;
