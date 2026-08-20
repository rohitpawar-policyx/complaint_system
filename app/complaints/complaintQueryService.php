<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';

function get_user_complaints(int $userId): array
{
    $statement = database()->prepare(
        'SELECT complaints.id, complaint_reasons.name AS reason_name,
                complaints.priority, complaints.status,
                complaints.created_at, complaints.updated_at
         FROM complaints
         INNER JOIN complaint_reasons ON complaint_reasons.id = complaints.reason_id
         WHERE complaints.user_id = :user_id
         ORDER BY complaints.created_at DESC, complaints.id DESC'
    );
    $statement->execute(['user_id' => $userId]);

    return $statement->fetchAll();
}

function get_user_complaint(int $complaintId, int $userId): ?array
{
    $statement = database()->prepare(
        'SELECT complaints.id, complaints.message, complaint_reasons.name AS reason_name,
                complaints.priority, complaints.status,
                complaints.created_at, complaints.updated_at
         FROM complaints
         INNER JOIN complaint_reasons ON complaint_reasons.id = complaints.reason_id
         WHERE complaints.id = :complaint_id
           AND complaints.user_id = :user_id
         LIMIT 1'
    );
    $statement->execute([
        'complaint_id' => $complaintId,
        'user_id' => $userId,
    ]);
    $complaint = $statement->fetch();

    return is_array($complaint) ? $complaint : null;
}

function get_user_complaint_attachments(int $complaintId, int $userId): array
{
    $statement = database()->prepare(
        'SELECT complaint_attachments.id, complaint_attachments.original_name,
                complaint_attachments.file_path, complaint_attachments.mime_type,
                complaint_attachments.file_size
         FROM complaint_attachments
         INNER JOIN complaints ON complaints.id = complaint_attachments.complaint_id
         WHERE complaint_attachments.complaint_id = :complaint_id
           AND complaints.user_id = :user_id
         ORDER BY complaint_attachments.id ASC'
    );
    $statement->execute([
        'complaint_id' => $complaintId,
        'user_id' => $userId,
    ]);

    return $statement->fetchAll();
}

function get_user_complaint_history(int $complaintId, int $userId): array
{
    $statement = database()->prepare(
        'SELECT complaint_history.action, complaint_history.old_status,
                complaint_history.new_status, complaint_history.description,
                complaint_history.created_at, performer.name AS performed_by_name
         FROM complaint_history
         INNER JOIN complaints ON complaints.id = complaint_history.complaint_id
         INNER JOIN users AS performer ON performer.id = complaint_history.performed_by
         WHERE complaint_history.complaint_id = :complaint_id
           AND complaints.user_id = :user_id
         ORDER BY complaint_history.created_at ASC, complaint_history.id ASC'
    );
    $statement->execute([
        'complaint_id' => $complaintId,
        'user_id' => $userId,
    ]);

    return $statement->fetchAll();
}

function get_user_attachment(int $attachmentId, int $userId): ?array
{
    $statement = database()->prepare(
        'SELECT complaint_attachments.id, complaint_attachments.original_name,
                complaint_attachments.file_path, complaint_attachments.mime_type,
                complaint_attachments.file_size
         FROM complaint_attachments
         INNER JOIN complaints ON complaints.id = complaint_attachments.complaint_id
         WHERE complaint_attachments.id = :attachment_id
           AND complaints.user_id = :user_id
         LIMIT 1'
    );
    $statement->execute([
        'attachment_id' => $attachmentId,
        'user_id' => $userId,
    ]);
    $attachment = $statement->fetch();

    return is_array($attachment) ? $attachment : null;
}
