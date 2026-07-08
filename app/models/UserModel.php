<?php

namespace App\Models;

use Core\Model;
use PDO;

class UserModel extends Model
{
    /**
     * Busca um usuário pelo CPF (ou CNPJ caso o Admin também esteja na tabela local)
     * Lembre-se: Este Model atuará sob a conexão PDO do Tenant após o switch.
     */
    public function getUserByDocument(string $document): ?array
    {
        $document = preg_replace('/[^0-9]/', '', $document);
        $sql = "SELECT * FROM users WHERE cpf = :document AND status = 'active' LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':document', $document, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getAll(): array
    {
        $sql = "SELECT u.*, b.name as branch_name, r.name as role_name 
                FROM users u 
                LEFT JOIN branches b ON u.branch_id = b.id 
                LEFT JOIN roles r ON u.role_id = r.id 
                ORDER BY u.name ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function countAll(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM users");
            $count = (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            $count = 0;
        }

        if ($count > 0 || !$this->tableExists('usuarios')) {
            return $count;
        }

        $stmt = $this->db->query("SELECT COUNT(*) FROM usuarios WHERE ativo = 1");
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO users (cpf, name, email, password_hash, role_id, branch_id, status) 
                VALUES (:cpf, :name, :email, :password_hash, :role_id, :branch_id, :status)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':cpf' => preg_replace('/[^0-9]/', '', $data['cpf']),
            ':name' => $data['name'],
            ':email' => $data['email'] ?? null,
            ':password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':role_id' => $data['role_id'],
            ':branch_id' => $data['branch_id'],
            ':status' => $data['status'] ?? 'active'
        ]);
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT u.*, b.name as branch_name, r.name as role_name 
                FROM users u 
                LEFT JOIN branches b ON u.branch_id = b.id 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function update(int $id, array $data): bool
    {
        $fields = [
            'cpf' => preg_replace('/[^0-9]/', '', $data['cpf']),
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'role_id' => $data['role_id'],
            'branch_id' => $data['branch_id'],
            'status' => $data['status'] ?? 'active',
        ];

        $set = "cpf = :cpf, name = :name, email = :email, role_id = :role_id, branch_id = :branch_id, status = :status";
        $params = [
            ':id' => $id,
            ':cpf' => $fields['cpf'],
            ':name' => $fields['name'],
            ':email' => $fields['email'],
            ':role_id' => $fields['role_id'],
            ':branch_id' => $fields['branch_id'],
            ':status' => $fields['status'],
        ];

        if (!empty($data['password'])) {
            $set .= ", password_hash = :password_hash";
            $params[':password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql = "UPDATE users SET {$set} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
