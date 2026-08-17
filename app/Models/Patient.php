<?php
namespace Medical\Clinic\Models;

use Medical\Clinic\Core\Model;

class patient extends Model{
    protected string $table='patients';

    public function findAllWithDetails():array
    {
         $stmt = $this->db->prepare("
            SELECT p.*,
                   u.first_name, u.last_name, u.email, u.is_active
            FROM patients p
            JOIN users u ON u.id = p.user_id
            ORDER BY u.last_name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function findByUserId(int $userId):?array{

    $stmt=$this->db->prepare(
        "SELECT * FROM  patients WHERE user_id = :user_id LIMIT 1"
    );
    $stmt->execute([':user_id' => $userId]);
    $result=$stmt->fetch();
    return $result ?:null;

    }
    public function findByIdFull(int $id): ?array {
        $stmt=$this->db->prepare("
         SELECT p.*,
                   u.first_name, u.last_name, u.email, u.is_active, u.created_at AS user_created_at
            FROM patients p
            JOIN users u ON u.id = p.user_id
            WHERE p.id = :id LIMIT 1
        ");
        $stmt->execute([':id'=>$id]);
        $result = $stmt->fetch();
        return $result ?: null;

    }
    public function count():int{
        $stmt=$this->db->query("SELECT COUNT(*)FROM patients");
        return(int)$stmt->fetchColumn();
    }
    }

?>