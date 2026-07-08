<?php

namespace App\Models;

use Core\Model;
use PDO;

class AssetInventoryModel extends Model
{
    public function createSession(string $sector, int $createdBy): int
    {
        $stmt = $this->db->prepare('INSERT INTO asset_inventory_sessions (sector, created_by) VALUES (:sector, :created_by)');
        $stmt->execute([':sector' => $sector, ':created_by' => $createdBy]);
        $sessionId = (int) $this->db->lastInsertId();

        $insertItems = $this->db->prepare('INSERT IGNORE INTO asset_inventory_items (inventory_session_id, asset_id)
            SELECT :session_id, id FROM assets WHERE sector = :sector');
        $insertItems->execute([':session_id' => $sessionId, ':sector' => $sector]);

        return $sessionId;
    }

    public function getAllSessions(): array
    {
        $stmt = $this->db->query('SELECT s.*, u.name AS created_by_name, 
            (SELECT COUNT(*) FROM asset_inventory_items i WHERE i.inventory_session_id = s.id) AS total_items,
            (SELECT COUNT(*) FROM asset_inventory_items i WHERE i.inventory_session_id = s.id AND i.checked_at IS NOT NULL) AS checked_items
            FROM asset_inventory_sessions s
            LEFT JOIN users u ON s.created_by = u.id
            ORDER BY s.created_at DESC');
        return $stmt->fetchAll();
    }

    public function findSession(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT s.*, u.name AS created_by_name FROM asset_inventory_sessions s LEFT JOIN users u ON s.created_by = u.id WHERE s.id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $session = $stmt->fetch();
        return $session ?: null;
    }

    public function countTotalItems(int $inventoryId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS c FROM asset_inventory_items WHERE inventory_session_id = :id');
        $stmt->execute([':id' => $inventoryId]);
        return (int) $stmt->fetchColumn();
    }

    public function countCheckedItems(int $inventoryId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS c FROM asset_inventory_items WHERE inventory_session_id = :id AND checked_at IS NOT NULL');
        $stmt->execute([':id' => $inventoryId]);
        return (int) $stmt->fetchColumn();
    }

    public function getItems(int $inventoryId, bool $pendingOnly = false): array
    {
        $sql = 'SELECT i.*, a.asset_number, a.name AS asset_name, a.location, a.current_responsible
                FROM asset_inventory_items i
                JOIN assets a ON a.id = i.asset_id
                WHERE i.inventory_session_id = :id';

        if ($pendingOnly) {
            $sql .= ' AND i.checked_at IS NULL';
        }

        $sql .= ' ORDER BY i.checked_at IS NULL DESC, a.asset_number ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $inventoryId]);
        return $stmt->fetchAll();
    }

    public function verifyItem(int $inventoryId, int $assetId, int $checkedBy): bool
    {
        $stmt = $this->db->prepare('INSERT INTO asset_inventory_items (inventory_session_id, asset_id, checked_at, checked_by)
            VALUES (:inventory_session_id, :asset_id, :checked_at, :checked_by)
            ON DUPLICATE KEY UPDATE checked_at = VALUES(checked_at), checked_by = VALUES(checked_by)');

        return $stmt->execute([
            ':inventory_session_id' => $inventoryId,
            ':asset_id' => $assetId,
            ':checked_at' => (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s'),
            ':checked_by' => $checkedBy,
        ]);
    }

    public function finalize(int $inventoryId): bool
    {
        $stmt = $this->db->prepare('UPDATE asset_inventory_sessions SET finalized_at = CURRENT_TIMESTAMP WHERE id = :id AND finalized_at IS NULL');
        return $stmt->execute([':id' => $inventoryId]);
    }
}
