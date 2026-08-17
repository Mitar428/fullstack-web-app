<?php

namespace Medical\Clinic\Models;

use Medical\Clinic\Core\Model;

class Message extends Model
{
    protected string $table = 'messages';

    public function findForUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT m.*,
                   CONCAT(su.first_name, ' ', su.last_name) AS sender_name,
                   CONCAT(ru.first_name, ' ', ru.last_name) AS receiver_name
            FROM messages m
            JOIN users su ON su.id = m.sender_id
            JOIN users ru ON ru.id = m.receiver_id
            WHERE m.receiver_id = :uid1 OR m.sender_id = :uid2
            ORDER BY m.sent_at DESC
        ");
        $stmt->execute([':uid1' => $userId, ':uid2' => $userId]);
        return $stmt->fetchAll();
    }

    public function findInboxForUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT m.*,
                   CONCAT(su.first_name, ' ', su.last_name) AS sender_name
            FROM messages m
            JOIN users su ON su.id = m.sender_id
            WHERE m.receiver_id = :user_id
            ORDER BY m.sent_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function countUnread(int $userId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM messages WHERE receiver_id = :user_id AND is_read = 0"
        );
        $stmt->execute([':user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function markAsRead(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE messages SET is_read = 1 WHERE id = :id"
        );
        return $stmt->execute([':id' => $id]);
    }
}
