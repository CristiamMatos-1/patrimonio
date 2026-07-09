<?php

namespace App\Models;

use Core\Model;

class TenantModel extends Model
{
    public function getAll(): array
    {
        $sql = "SELECT id, cnpj, razao_social, nome_fantasia, db_host, db_name, db_user, status, created_at
                FROM tenants
                ORDER BY created_at DESC";

        return $this->db->query($sql)->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, cnpj, razao_social, nome_fantasia, db_host, db_name, db_user, db_pass, status, created_at
             FROM tenants
             WHERE id = :id
             LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $tenant = $stmt->fetch();

        return $tenant ?: null;
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE tenants SET status = :status WHERE id = :id");
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
        ]);
    }
}
