<?php

namespace Medical\Clinic\Models;

use Medical\Clinic\Core\Model;

class Doctor extends Model
{
    protected string $table = 'doctors';

    public function findAllWithDetails(): array
    {
        $stmt = $this->db->prepare("
            SELECT d.*,
                   u.first_name, u.last_name, u.email, u.is_active,
                   s.name AS specialization_name
            FROM doctors d
            JOIN users u ON u.id = d.user_id
            JOIN specializations s ON s.id = d.specialization_id
            ORDER BY u.last_name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, s.name AS specialization_name
            FROM doctors d
            JOIN specializations s ON s.id = d.specialization_id
            WHERE d.user_id = :user_id LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findAllForSelect(): array
    {
        $stmt = $this->db->prepare("
            SELECT d.id,
                   CONCAT(u.first_name, ' ', u.last_name) AS doctor_name,
                   s.name AS specialization_name
            FROM doctors d
            JOIN users u ON u.id = d.user_id
            JOIN specializations s ON s.id = d.specialization_id
            ORDER BY u.last_name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM doctors");
        return (int) $stmt->fetchColumn();
    }
}
