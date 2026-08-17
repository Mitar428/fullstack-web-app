<?php

namespace Medical\Clinic\Models;

use Medical\Clinic\Core\Model;

class ActivityLog extends Model
{
    protected string $table = 'activity_log';

    public function findAllWithUsers(int $limit = 200): array
    {
        $stmt = $this->db->prepare("
            SELECT al.*,
                   CONCAT(u.first_name, ' ', u.last_name) AS user_name,
                   u.email AS user_email
            FROM activity_log al
            LEFT JOIN users u ON u.id = al.user_id
            ORDER BY al.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
