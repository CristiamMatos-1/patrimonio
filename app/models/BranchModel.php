<?php

namespace App\Models;

use Core\Model;
use PDO;

class BranchModel extends Model
{
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM branches ORDER BY is_headquarters DESC, name ASC");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM branches WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $branch = $stmt->fetch();
        return $branch ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO branches (name, is_headquarters, cnpj, cep, address, number, neighborhood, city, state) 
                VALUES (:name, :is_headquarters, :cnpj, :cep, :address, :number, :neighborhood, :city, :state)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $data['name'],
            ':is_headquarters' => !empty($data['is_headquarters']) ? 1 : 0,
            ':cnpj' => preg_replace('/[^0-9]/', '', $data['cnpj'] ?? ''),
            ':cep' => preg_replace('/[^0-9]/', '', $data['cep'] ?? ''),
            ':address' => $data['address'] ?? null,
            ':number' => $data['number'] ?? null,
            ':neighborhood' => $data['neighborhood'] ?? null,
            ':city' => $data['city'] ?? null,
            ':state' => $data['state'] ?? null
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE branches SET 
                    name = :name, 
                    is_headquarters = :is_headquarters, 
                    cnpj = :cnpj, 
                    cep = :cep, 
                    address = :address, 
                    number = :number, 
                    neighborhood = :neighborhood, 
                    city = :city, 
                    state = :state 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':is_headquarters' => !empty($data['is_headquarters']) ? 1 : 0,
            ':cnpj' => preg_replace('/[^0-9]/', '', $data['cnpj'] ?? ''),
            ':cep' => preg_replace('/[^0-9]/', '', $data['cep'] ?? ''),
            ':address' => $data['address'] ?? null,
            ':number' => $data['number'] ?? null,
            ':neighborhood' => $data['neighborhood'] ?? null,
            ':city' => $data['city'] ?? null,
            ':state' => $data['state'] ?? null
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM branches WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function countAll(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM branches");
            $count = (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            $count = 0;
        }

        if ($count > 0 || !$this->tableExists('locais')) {
            return $count;
        }

        $stmt = $this->db->query("SELECT COUNT(*) FROM locais WHERE tipo IN ('filial', 'sede')");
        return (int) $stmt->fetchColumn();
    }
}
