<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/shared/security/csrf.php';
require_once dirname(__DIR__, 2) . '/shared/middleware/auth.php';
require_once dirname(__DIR__, 2) . '/auth/authService.php';
require_once dirname(__DIR__) . '/complaintService.php';

require_authenticated();
$userId = authenticated_user_id();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: complaint.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_auth_flash('error', 'The form expired. Please try again.');
    header('Location: complaint.php');
    exit;
}

$reasonId = filter_var($_POST['reason_id'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$message = trim((string) ($_POST['message'] ?? ''));

if ($reasonId === false || $message === '' || mb_strlen($message) > 10000) {
    set_auth_flash('error', 'Select a reason and enter a valid complaint message.');
    header('Location: complaint.php');
    exit;
}

try {
    $validatedAttachments = validate_complaint_attachments($_FILES['attachments'] ?? []);
    $complaintId = create_complaint($userId, $reasonId, $message, $validatedAttachments);
} catch (InvalidArgumentException $exception) {
    set_auth_flash('error', 'The selected complaint reason is unavailable.');
    header('Location: complaint.php');
    exit;
} catch (RuntimeException $exception) {
    set_auth_flash('error', $exception->getMessage());
    header('Location: complaint.php');
    exit;
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    set_auth_flash('error', 'The complaint could not be submitted right now. Please try again later.');
    header('Location: complaint.php');
    exit;
}

set_auth_flash('success', 'Complaint #' . $complaintId . ' was submitted successfully.');
header('Location: complaint.php');
exit;
