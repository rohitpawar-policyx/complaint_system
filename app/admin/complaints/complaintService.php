<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/config/database.php';

const ADMIN_COMPLAINT_STATUSES = ['pending', 'in_progress', 'resolved', 'closed', 'rejected'];
const ADMIN_COMPLAINT_PRIORITIES = ['LOW', 'MEDIUM', 'HIGH'];
const COMPLAINT_ASSIGNMENT_ACTION = 'complaint_assigned';
const COMPLAINT_STATUS_ACTION = 'complaint_status_changed';

function get_admin_complaints(string $status, string $priority, string $search): array
{
    $statement = database()->prepare(
        "SELECT complaints.id, complainant.name AS complainant_name,
                complaint_reasons.name AS reason_name, complaints.priority,
                complaints.status, assignee.name AS assignee_name,
                complaints.created_at, complaints.updated_at
         FROM complaints
         INNER JOIN users AS complainant ON complainant.id = complaints.user_id
         INNER JOIN complaint_reasons ON complaint_reasons.id = complaints.reason_id
         LEFT JOIN users AS assignee ON assignee.id = complaints.assigned_to
                 WHERE (:status_filter = '' OR complaints.status = :status_value)
                     AND (:priority_filter = '' OR complaints.priority = :priority_value)
                     AND (:search_filter = ''
                OR complainant.name LIKE :search_name
                OR complainant.email LIKE :search_email
                OR complaint_reasons.name LIKE :search_reason
                OR CAST(complaints.id AS CHAR) LIKE :search_id)
            ORDER BY complaints.created_at DESC, complaints.id DESC"
    );
    $searchValue = '%' . $search . '%';
    $statement->execute([
        'status_filter' => $status,
        'status_value' => $status,
        'priority_filter' => $priority,
        'priority_value' => $priority,
        'search_filter' => $search,
        'search_name' => $searchValue,
        'search_email' => $searchValue,
        'search_reason' => $searchValue,
        'search_id' => $searchValue,
    ]);

    return $statement->fetchAll();
}

function get_admin_complaint(int $complaintId): ?array
{
    $statement = database()->prepare(
        'SELECT complaints.id, complaints.message, complaints.priority,
                complaints.status, complaints.created_at, complaints.updated_at,
                complainant.name AS complainant_name, complainant.email AS complainant_email,
                complaint_reasons.name AS reason_name,
                assignee.id AS assignee_id, assignee.name AS assignee_name
         FROM complaints
         INNER JOIN users AS complainant ON complainant.id = complaints.user_id
         INNER JOIN complaint_reasons ON complaint_reasons.id = complaints.reason_id
         LEFT JOIN users AS assignee ON assignee.id = complaints.assigned_to
         WHERE complaints.id = :complaint_id
         LIMIT 1'
    );
    $statement->execute(['complaint_id' => $complaintId]);
    $complaint = $statement->fetch();

    return is_array($complaint) ? $complaint : null;
}

function get_admin_complaint_attachments(int $complaintId): array
{
    $statement = database()->prepare(
        'SELECT id, original_name, file_path, mime_type, file_size
         FROM complaint_attachments
         WHERE complaint_id = :complaint_id
         ORDER BY id ASC'
    );
    $statement->execute(['complaint_id' => $complaintId]);

    return $statement->fetchAll();
}

function get_admin_complaint_history(int $complaintId): array
{
    $statement = database()->prepare(
        'SELECT complaint_history.action, complaint_history.old_status,
                complaint_history.new_status, complaint_history.assigned_from,
                complaint_history.assigned_to, complaint_history.description,
                complaint_history.created_at,
                performer.name AS performed_by_name,
                previous_assignee.name AS assigned_from_name,
                new_assignee.name AS assigned_to_name
         FROM complaint_history
         INNER JOIN users AS performer ON performer.id = complaint_history.performed_by
         LEFT JOIN users AS previous_assignee ON previous_assignee.id = complaint_history.assigned_from
         LEFT JOIN users AS new_assignee ON new_assignee.id = complaint_history.assigned_to
         WHERE complaint_history.complaint_id = :complaint_id
         ORDER BY complaint_history.created_at ASC, complaint_history.id ASC'
    );
    $statement->execute(['complaint_id' => $complaintId]);

    return $statement->fetchAll();
}

function get_eligible_assignees(): array
{
    $statement = database()->prepare(
        'SELECT users.id, users.name, users.email
         FROM users
         INNER JOIN roles ON roles.id = users.role_id
         WHERE users.status = :status AND roles.name = :role_name
         ORDER BY users.name ASC, users.id ASC'
    );
    $statement->execute(['status' => 'approved', 'role_name' => 'user']);

    return $statement->fetchAll();
}

function get_admin_attachment(int $attachmentId): ?array
{
    $statement = database()->prepare(
        'SELECT complaint_attachments.original_name, complaint_attachments.file_path,
                complaint_attachments.mime_type, complaint_attachments.file_size
         FROM complaint_attachments
         INNER JOIN complaints ON complaints.id = complaint_attachments.complaint_id
         WHERE complaint_attachments.id = :attachment_id
         LIMIT 1'
    );
    $statement->execute(['attachment_id' => $attachmentId]);
    $attachment = $statement->fetch();

    return is_array($attachment) ? $attachment : null;
}

function assign_admin_complaint(int $complaintId, int $assigneeId, int $performedBy): void
{
    $connection = database();

    try {
        $connection->beginTransaction();
        $complaint = lock_admin_complaint($connection, $complaintId);
        if ($complaint === null) {
            throw new InvalidArgumentException('Complaint not found.');
        }

        $assigneeStatement = $connection->prepare(
            'SELECT users.id
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             WHERE users.id = :assignee_id
               AND users.status = :status
               AND roles.name = :role_name
             LIMIT 1'
        );
        $assigneeStatement->execute([
            'assignee_id' => $assigneeId,
            'status' => 'approved',
            'role_name' => 'user',
        ]);
        if ($assigneeStatement->fetchColumn() === false) {
            throw new InvalidArgumentException('Assignee is not eligible.');
        }

        if ((int) ($complaint['assigned_to'] ?? 0) === $assigneeId) {
            throw new InvalidArgumentException('Complaint is already assigned to this user.');
        }

        $updateStatement = $connection->prepare(
            'UPDATE complaints SET assigned_to = :assigned_to WHERE id = :complaint_id'
        );
        $updateStatement->execute([
            'assigned_to' => $assigneeId,
            'complaint_id' => $complaintId,
        ]);

        insert_admin_history($connection, [
            'complaint_id' => $complaintId,
            'action' => COMPLAINT_ASSIGNMENT_ACTION,
            'old_status' => null,
            'new_status' => null,
            'assigned_from' => $complaint['assigned_to'],
            'assigned_to' => $assigneeId,
            'performed_by' => $performedBy,
            'description' => 'Complaint assignment updated.',
        ]);
        $connection->commit();
    } catch (Throwable $exception) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
        throw $exception;
    }
}

function change_admin_complaint_status(int $complaintId, string $newStatus, int $performedBy): void
{
    $connection = database();

    try {
        $connection->beginTransaction();
        $complaint = lock_admin_complaint($connection, $complaintId);
        if ($complaint === null) {
            throw new InvalidArgumentException('Complaint not found.');
        }

        if ($complaint['status'] === $newStatus) {
            throw new InvalidArgumentException('Complaint already has this status.');
        }

        $updateStatement = $connection->prepare(
            'UPDATE complaints SET status = :status WHERE id = :complaint_id'
        );
        $updateStatement->execute([
            'status' => $newStatus,
            'complaint_id' => $complaintId,
        ]);

        insert_admin_history($connection, [
            'complaint_id' => $complaintId,
            'action' => COMPLAINT_STATUS_ACTION,
            'old_status' => $complaint['status'],
            'new_status' => $newStatus,
            'assigned_from' => null,
            'assigned_to' => null,
            'performed_by' => $performedBy,
            'description' => 'Complaint status updated.',
        ]);
        $connection->commit();
    } catch (Throwable $exception) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
        throw $exception;
    }
}

function lock_admin_complaint(PDO $connection, int $complaintId): ?array
{
    $statement = $connection->prepare(
        'SELECT id, status, assigned_to
         FROM complaints
         WHERE id = :complaint_id
         LIMIT 1
         FOR UPDATE'
    );
    $statement->execute(['complaint_id' => $complaintId]);
    $complaint = $statement->fetch();

    return is_array($complaint) ? $complaint : null;
}

function insert_admin_history(PDO $connection, array $history): void
{
    $statement = $connection->prepare(
        'INSERT INTO complaint_history
            (complaint_id, action, old_status, new_status, assigned_from,
             assigned_to, performed_by, description)
         VALUES (:complaint_id, :action, :old_status, :new_status, :assigned_from,
                 :assigned_to, :performed_by, :description)'
    );
    $statement->execute($history);
}
