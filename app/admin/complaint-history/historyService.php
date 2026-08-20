<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/config/database.php';

function get_admin_history(?int $complaintId): array
{
    $statement = database()->prepare(
        'SELECT complaint_history.complaint_id, complaint_history.action,
                complaint_history.old_status, complaint_history.new_status,
                complaint_history.description, complaint_history.created_at,
                performer.name AS performed_by_name,
                previous_assignee.name AS assigned_from_name,
                new_assignee.name AS assigned_to_name
         FROM complaint_history
         INNER JOIN users AS performer ON performer.id = complaint_history.performed_by
         LEFT JOIN users AS previous_assignee ON previous_assignee.id = complaint_history.assigned_from
         LEFT JOIN users AS new_assignee ON new_assignee.id = complaint_history.assigned_to
         WHERE (:complaint_id_filter IS NULL
                OR complaint_history.complaint_id = :complaint_id_value)
         ORDER BY complaint_history.created_at DESC, complaint_history.id DESC'
    );
    $value = $complaintId === null ? null : (string) $complaintId;
    $statement->bindValue(':complaint_id_filter', $value, $value === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $statement->bindValue(':complaint_id_value', $value, $value === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $statement->execute();

    return $statement->fetchAll();
}
