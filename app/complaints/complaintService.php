<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once __DIR__ . '/attachmentService.php';

const INITIAL_COMPLAINT_STATUS = 'pending';
const INITIAL_COMPLAINT_ACTION = 'complaint_created';

function get_active_complaint_reasons(): array
{
    $statement = database()->prepare(
        'SELECT id, name, description
         FROM complaint_reasons
         WHERE active = 1
         ORDER BY name ASC'
    );
    $statement->execute();

    return $statement->fetchAll();
}

function get_active_complaint_reason(PDO $connection, int $reasonId): ?array
{
    $statement = $connection->prepare(
        'SELECT id, priority
         FROM complaint_reasons
         WHERE id = :reason_id AND active = 1
         LIMIT 1'
    );
    $statement->execute(['reason_id' => $reasonId]);
    $reason = $statement->fetch();

    return is_array($reason) ? $reason : null;
}

function create_complaint(
    int $userId,
    int $reasonId,
    string $message,
    array $validatedAttachments
): int {
    $connection = database();
    $movedFiles = [];

    try {
        $connection->beginTransaction();

        $reason = get_active_complaint_reason($connection, $reasonId);
        if ($reason === null) {
            throw new InvalidArgumentException('The selected complaint reason is unavailable.');
        }

        $complaintStatement = $connection->prepare(
            'INSERT INTO complaints (user_id, reason_id, message, priority, status)
             VALUES (:user_id, :reason_id, :message, :priority, :status)'
        );
        $complaintStatement->execute([
            'user_id' => $userId,
            'reason_id' => $reason['id'],
            'message' => $message,
            'priority' => $reason['priority'],
            'status' => INITIAL_COMPLAINT_STATUS,
        ]);
        $complaintId = (int) $connection->lastInsertId();

        $historyStatement = $connection->prepare(
            'INSERT INTO complaint_history
                (complaint_id, action, old_status, new_status, performed_by, description)
             VALUES (:complaint_id, :action, :old_status, :new_status, :performed_by, :description)'
        );
        $historyStatement->execute([
            'complaint_id' => $complaintId,
            'action' => INITIAL_COMPLAINT_ACTION,
            'old_status' => null,
            'new_status' => INITIAL_COMPLAINT_STATUS,
            'performed_by' => $userId,
            'description' => 'Complaint created.',
        ]);

        $attachmentStatement = $connection->prepare(
            'INSERT INTO complaint_attachments
                (complaint_id, original_name, stored_name, file_path, mime_type, file_size)
             VALUES (:complaint_id, :original_name, :stored_name, :file_path, :mime_type, :file_size)'
        );

        foreach ($validatedAttachments as $attachment) {
            $storedAttachment = move_complaint_attachment($attachment);
            $movedFiles[] = $storedAttachment['absolute_path'];
            $attachmentStatement->execute([
                'complaint_id' => $complaintId,
                'original_name' => $storedAttachment['original_name'],
                'stored_name' => $storedAttachment['stored_name'],
                'file_path' => $storedAttachment['file_path'],
                'mime_type' => $storedAttachment['mime_type'],
                'file_size' => $storedAttachment['file_size'],
            ]);
        }

        $connection->commit();

        return $complaintId;
    } catch (Throwable $exception) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }

        foreach ($movedFiles as $filePath) {
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }

        throw $exception;
    }
}
