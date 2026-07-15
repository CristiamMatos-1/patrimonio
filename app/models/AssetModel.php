<?php

namespace App\Models;

use Core\Model;
use PDO;
use PDOException;

class AssetModel extends Model
{
    /**
     * Retorna todos os patrimônios com a filial vinculada.
     */
    public function getAll(): array
    {
        $sql = "SELECT a.*, b.name as branch_name 
                FROM assets a 
                INNER JOIN branches b ON a.branch_id = b.id 
                ORDER BY a.created_at DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Busca patrimônios baseado em termo (Nome, Número ou QR Code).
     */
    public function search(string $term): array
    {
        $term = "%{$term}%";
        $sql = "SELECT a.*, b.name as branch_name 
                FROM assets a 
                INNER JOIN branches b ON a.branch_id = b.id 
                WHERE a.name LIKE :term 
                   OR a.asset_number LIKE :term 
                   OR a.qr_code_hash LIKE :term
                   OR a.internal_code LIKE :term
                   OR a.serial_number LIKE :term
                ORDER BY a.name ASC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':term' => $term]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $sql = "SELECT a.*, b.name AS branch_name, b.cnpj AS branch_cnpj 
                FROM assets a 
                INNER JOIN branches b ON a.branch_id = b.id 
                WHERE a.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $asset = $stmt->fetch();
        return $asset ?: null;
    }

    public function findByHash(string $hash): ?array
    {
        $sql = "SELECT a.*, b.name AS branch_name, b.cnpj AS branch_cnpj 
                FROM assets a 
                INNER JOIN branches b ON a.branch_id = b.id 
                WHERE a.qr_code_hash = :hash LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':hash' => $hash]);
        $asset = $stmt->fetch();
        return $asset ?: null;
    }

    public function findByAssetNumber(string $assetNumber): ?array
    {
        $stmt = $this->db->prepare("SELECT a.*, b.name AS branch_name, b.cnpj AS branch_cnpj 
                FROM assets a 
                INNER JOIN branches b ON a.branch_id = b.id 
                WHERE a.asset_number = :asset_number LIMIT 1");
        $stmt->execute([':asset_number' => $assetNumber]);
        $asset = $stmt->fetch();
        return $asset ?: null;
    }

    public function update(int $id, array $data): bool
    {
        $value = null;
        if (!empty($data['value'])) {
            $value = str_replace('.', '', $data['value']);
            $value = str_replace(',', '.', $value);
        }

        $sql = "UPDATE assets SET 
                    branch_id = :branch_id, 
                    internal_code = :internal_code, 
                    name = :name, 
                    description = :description, 
                    category = :category, 
                    brand = :brand, 
                    model = :model, 
                    serial_number = :serial_number, 
                    acquisition_date = :acquisition_date, 
                    value = :value, 
                    useful_life_years = :useful_life_years, 
                    cost_center = :cost_center, 
                    sector = :sector, 
                    location = :location, 
                    current_responsible = :current_responsible, 
                    photo_url = :photo_url, 
                    observations = :observations, 
                    status = :status 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':branch_id' => $data['branch_id'],
            ':internal_code' => $data['internal_code'] ?? null,
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':category' => $data['category'] ?? null,
            ':brand' => $data['brand'] ?? null,
            ':model' => $data['model'] ?? null,
            ':serial_number' => $data['serial_number'] ?? null,
            ':acquisition_date' => !empty($data['acquisition_date']) ? $data['acquisition_date'] : null,
            ':value' => $value,
            ':useful_life_years' => !empty($data['useful_life_years']) ? (int)$data['useful_life_years'] : 5,
            ':cost_center' => $data['cost_center'] ?? null,
            ':sector' => $data['sector'] ?? null,
            ':location' => $data['location'] ?? null,
            ':current_responsible' => $data['current_responsible'] ?? null,
            ':photo_url' => $data['photo_url'] ?? null,
            ':observations' => $data['observations'] ?? null,
            ':status' => $data['status'] ?? 'active',
            ':id' => $id
        ]);
    }

    public function countAll(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM assets");
            $count = (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            if ($e->getCode() !== '42S02') {
                throw $e;
            }
            $count = 0;
        }

        if ($count > 0 || !$this->tableExists('bens')) {
            return $count;
        }

        $stmt = $this->db->query("SELECT COUNT(*) FROM bens");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Gera o próximo número de tombamento (Sequencial com prefixo)
     */
    public function generateNextAssetNumber(): string
    {
        // Busca o último número gerado
        $sql = "SELECT asset_number FROM assets ORDER BY id DESC LIMIT 1";
        $lastAsset = $this->db->query($sql)->fetchColumn();

        if (!$lastAsset) {
            return 'PAT-000001';
        }

        // Extrai apenas os números do último tombamento e incrementa
        $numberOnly = preg_replace('/[^0-9]/', '', $lastAsset);
        $nextNumber = intval($numberOnly) + 1;

        // Retorna formatado com zeros à esquerda
        return 'PAT-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Retorna patrimônios pelos IDs informados.
     *
     * @param int[] $ids
     */
    public function findByIds(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids), fn ($id) => $id > 0));
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT a.*, b.name as branch_name
                FROM assets a
                INNER JOIN branches b ON a.branch_id = b.id
                WHERE a.id IN ($placeholders)
                ORDER BY a.asset_number ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($ids);
        return $stmt->fetchAll();
    }

    /**
     * Cadastra um novo bem patrimonial.
     *
     * @return int|false ID do registro inserido, ou false em caso de falha.
     */
    public function create(array $data): int|false
    {
        // Se não vier um número de tombamento (deve ser automático), nós geramos um
        if (empty($data['asset_number'])) {
            $data['asset_number'] = $this->generateNextAssetNumber();
        }

        // Trata o valor monetário de BRL (ex: 1.500,00) para Decimal MySQL (1500.00)
        $value = null;
        if (!empty($data['value'])) {
            $value = str_replace('.', '', $data['value']); // Remove os pontos de milhar
            $value = str_replace(',', '.', $value);        // Troca a vírgula decimal por ponto
        }

        $sql = "INSERT INTO assets (
                    branch_id, asset_number, internal_code, name, description, 
                    category, brand, model, serial_number, acquisition_date, 
                    value, useful_life_years, cost_center, sector, location, 
                    current_responsible, qr_code_hash, photo_url, observations, status
                ) VALUES (
                    :branch_id, :asset_number, :internal_code, :name, :description, 
                    :category, :brand, :model, :serial_number, :acquisition_date, 
                    :value, :useful_life_years, :cost_center, :sector, :location, 
                    :current_responsible, :qr_code_hash, :photo_url, :observations, :status
                )";
        
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            ':branch_id' => $data['branch_id'],
            ':asset_number' => $data['asset_number'],
            ':internal_code' => $data['internal_code'] ?? null,
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':category' => $data['category'] ?? null,
            ':brand' => $data['brand'] ?? null,
            ':model' => $data['model'] ?? null,
            ':serial_number' => $data['serial_number'] ?? null,
            ':acquisition_date' => !empty($data['acquisition_date']) ? $data['acquisition_date'] : null,
            ':value' => $value,
            ':useful_life_years' => !empty($data['useful_life_years']) ? (int)$data['useful_life_years'] : 5,
            ':cost_center' => $data['cost_center'] ?? null,
            ':sector' => $data['sector'] ?? null,
            ':location' => $data['location'] ?? null,
            ':current_responsible' => $data['current_responsible'] ?? null,
            ':qr_code_hash' => $data['qr_code_hash'],
            ':photo_url' => $data['photo_url'] ?? null,
            ':observations' => $data['observations'] ?? null,
            ':status' => $data['status'] ?? 'active'
        ]);

        if ($success) {
            $recordId = (int) $this->db->lastInsertId();
            \Core\Services\AuditService::log('CREATE', 'assets', $recordId, null, $data);
            return $recordId;
        }

        return false;
    }
}
