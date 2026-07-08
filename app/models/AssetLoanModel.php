<?php

namespace App\Models;

use Core\Model;

class AssetLoanModel extends Model
{
    public function create(array $data): int|false
    {
        $sql = "INSERT INTO asset_loans (
                    asset_id, borrowed_by, out_date, expected_return_date, return_date, status, destination_location
                ) VALUES (
                    :asset_id, :borrowed_by, :out_date, :expected_return_date, :return_date, :status, :destination_location
                )";

        $stmt = $this->db->prepare($sql);
        if ($stmt->execute([
            ':asset_id' => $data['asset_id'],
            ':borrowed_by' => $data['borrowed_by'],
            ':out_date' => $data['out_date'],
            ':expected_return_date' => $data['expected_return_date'] ?? null,
            ':return_date' => $data['return_date'] ?? null,
            ':status' => $data['status'] ?? 'active',
            ':destination_location' => $data['destination_location'] ?? null,
        ])) {
            return (int) $this->db->lastInsertId();
        }

        return false;
    }

    public function getActiveByAsset(int $assetId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM asset_loans WHERE asset_id = :asset_id AND status = 'active' ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([':asset_id' => $assetId]);
        $loan = $stmt->fetch();
        return $loan ?: null;
    }

    public function getByAsset(int $assetId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM asset_loans WHERE asset_id = :asset_id ORDER BY created_at DESC");
        $stmt->execute([':asset_id' => $assetId]);
        return $stmt->fetchAll();
    }
}
