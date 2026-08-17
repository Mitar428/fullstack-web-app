<?php

namespace Medical\Clinic\Models;

use Medical\Clinic\Core\Model;
use PDO;

class Appointment extends Model
{
    protected string $table = 'appointments';

    public function findAllFull(string $status = ''): array
    {
        $where = $status ? "WHERE a.status = :status" : '';
        $params = $status ? [':status' => $status] : [];

        $stmt = $this->db->prepare("
            SELECT a.*,
                   CONCAT(pu.first_name, ' ', pu.last_name) AS patient_name,
                   CONCAT(du.first_name, ' ', du.last_name) AS doctor_name,
                   s.name AS specialization
            FROM appointments a
            JOIN patients p  ON p.id  = a.patient_id
            JOIN users pu    ON pu.id = p.user_id
            JOIN doctors d   ON d.id  = a.doctor_id
            JOIN users du    ON du.id = d.user_id
            JOIN specializations s ON s.id = d.specialization_id
            {$where}
            ORDER BY a.scheduled_at DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findByPatientAndDoctor(int $patientId, int $doctorId): array
    {
        $stmt = $this->db->prepare("
            SELECT a.*,
                   CONCAT(du.first_name, ' ', du.last_name) AS doctor_name,
                   s.name AS specialization
            FROM appointments a
            JOIN doctors d ON d.id = a.doctor_id
            JOIN users du  ON du.id = d.user_id
            JOIN specializations s ON s.id = d.specialization_id
            WHERE a.patient_id = :patient_id AND a.doctor_id = :doctor_id
            ORDER BY a.scheduled_at DESC
        ");
        $stmt->execute([':patient_id' => $patientId, ':doctor_id' => $doctorId]);
        return $stmt->fetchAll();
    }

    public function findByPatient(int $patientId): array
    {
        $stmt = $this->db->prepare("
            SELECT a.*,
                   CONCAT(du.first_name, ' ', du.last_name) AS doctor_name,
                   s.name AS specialization
            FROM appointments a
            JOIN doctors d ON d.id = a.doctor_id
            JOIN users du  ON du.id = d.user_id
            JOIN specializations s ON s.id = d.specialization_id
            WHERE a.patient_id = :patient_id
            ORDER BY a.scheduled_at DESC
        ");
        $stmt->execute([':patient_id' => $patientId]);
        return $stmt->fetchAll();
    }

    public function countToday(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM appointments WHERE DATE(scheduled_at) = CURDATE()"
        );
        return (int) $stmt->fetchColumn();
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM appointments WHERE status = :status"
        );
        $stmt->execute([':status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE appointments SET status = :status WHERE id = :id"
        );
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function findFullById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT a.*,
                   CONCAT(pu.first_name, ' ', pu.last_name) AS patient_name,
                   CONCAT(du.first_name, ' ', du.last_name) AS doctor_name,
                   s.name AS specialization
            FROM appointments a
            JOIN patients p  ON p.id  = a.patient_id
            JOIN users pu    ON pu.id = p.user_id
            JOIN doctors d   ON d.id  = a.doctor_id
            JOIN users du    ON du.id = d.user_id
            JOIN specializations s ON s.id = d.specialization_id
            WHERE a.id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
