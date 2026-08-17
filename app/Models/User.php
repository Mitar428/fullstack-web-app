<?php

namespace Medical\Clinic\Models;

use Medical\Clinic\Core\Model;

class User extends Model
{
    protected string $table = 'users';


    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT u.*, r.slug as role_slug, r.name as role_name 
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.email = :email 
             LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        if (!$user['is_active']) {
            return null;
        }

        return $user;
    }

    public function createUser(array $data): int
    {

        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        
       
        unset($data['password']);

        return $this->create($data);
    }

    
    public function findAllWithRoles(): array
    {
        $stmt = $this->db->prepare(
            "SELECT u.*, r.name as role_name, r.slug as role_slug
             FROM users u
             JOIN roles r ON r.id = u.role_id
             ORDER BY u.created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function toggleActive(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET is_active = NOT is_active WHERE id = :id"
        );
        return $stmt->execute([':id' => $id]);
    }
}