<?php

namespace App\Models;

use Core\Model;

class MovementModel extends Model
{
    /**
     * Registra uma nova movimentação inalterável no histórico do patrimônio.
     */
    public function registerMovement(int $assetId, string $type, string $observations = null): bool
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) return false;

        $sql = "INSERT INTO asset_movements (asset_id, movement_type, user_id, movement_date, observations) 
                VALUES (:asset_id, :movement_type, :user_id, NOW(), :observations)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':asset_id' => $assetId,
            ':movement_type' => $type,
            ':user_id' => $userId,
            ':observations' => $observations
        ]);
    }

    /**
     * Busca o histórico de movimentações de um bem específico
     */
    public function getHistoryByAsset(int $assetId): array
    {
        $sql = "SELECT m.*, u.name as user_name 
                FROM asset_movements m
                INNER JOIN users u ON m.user_id = u.id
                WHERE m.asset_id = :asset_id
                ORDER BY m.movement_date DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':asset_id' => $assetId]);
        return $stmt->fetchAll();
    }
}
