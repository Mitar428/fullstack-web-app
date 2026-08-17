<?php

namespace Medical\Clinic\Models;

use Medical\Clinic\Core\Model;

class MedicalRecord extends Model
{
    protected string $table = 'medical_records';

    public function findByPatient(int $patientId): array
    {
        $stmt = $this->db->prepare("
            SELECT mr.*,
                   CONCAT(du.first_name, ' ', du.last_name) AS doctor_name,
                   s.name AS specialization,
                   GROUP_CONCAT(CONCAT(diag.icd_code, ' - ', diag.name) SEPARATOR ', ') AS diagnoses
            FROM medical_records mr
            JOIN doctors d       ON d.id   = mr.doctor_id
            JOIN users du        ON du.id  = d.user_id
            JOIN specializations s ON s.id = d.specialization_id
            LEFT JOIN record_diagnoses rd   ON rd.record_id   = mr.id
            LEFT JOIN diagnoses diag        ON diag.id        = rd.diagnosis_id
            WHERE mr.patient_id = :patient_id
            GROUP BY mr.id
            ORDER BY mr.created_at DESC
        ");
        $stmt->execute([':patient_id' => $patientId]);
        return $stmt->fetchAll();
    }

    public function createWithDiagnoses(array $data, array $diagnosisIds): int
    {
        $recordId = $this->create($data);

        if (!empty($diagnosisIds)) {
            $stmt = $this->db->prepare(
                "INSERT INTO record_diagnoses (record_id, diagnosis_id, severity) VALUES (:record_id, :diagnosis_id, 'mild')"
            );
            foreach ($diagnosisIds as $diagId) {
                $stmt->execute([':record_id' => $recordId, ':diagnosis_id' => (int) $diagId]);
            }
        }

        return $recordId;
    }

    public function findAllFull(): array
    {
        $stmt = $this->db->prepare("
            SELECT mr.*,
                   CONCAT(pu.first_name, ' ', pu.last_name) AS patient_name,
                   CONCAT(du.first_name, ' ', du.last_name) AS doctor_name
            FROM medical_records mr
            JOIN patients p ON p.id  = mr.patient_id
            JOIN users pu   ON pu.id = p.user_id
            JOIN doctors d  ON d.id  = mr.doctor_id
            JOIN users du   ON du.id = d.user_id
            ORDER BY mr.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
