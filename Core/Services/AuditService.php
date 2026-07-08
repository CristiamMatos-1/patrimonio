<?php

namespace Core\Services;

use Core\Database;

class AuditService
{
    /**
     * Registra uma ação no log de auditoria do Tenant ativo.
     * 
     * @param string $action Ação realizada (CREATE, UPDATE, DELETE, LOGIN)
     * @param string $tableName Tabela afetada
     * @param int|null $recordId ID do registro afetado
     * @param array|null $oldData Dados anteriores (para UPDATE/DELETE)
     * @param array|null $newData Novos dados (para CREATE/UPDATE)
     */
    public static function log(string $action, string $tableName, ?int $recordId = null, ?array $oldData = null, ?array $newData = null): void
    {
        try {
            $db = Database::getInstance()->getConnection();

            $userId = $_SESSION['user_id'] ?? null;
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

            $sql = "INSERT INTO audit_logs (user_id, action, table_name, record_id, old_data, new_data, ip_address) 
                    VALUES (:user_id, :action, :table_name, :record_id, :old_data, :new_data, :ip_address)";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':action' => strtoupper($action),
                ':table_name' => $tableName,
                ':record_id' => $recordId,
                ':old_data' => $oldData ? json_encode($oldData) : null,
                ':new_data' => $newData ? json_encode($newData) : null,
                ':ip_address' => $ipAddress
            ]);
        } catch (\Exception $e) {
            // Em um sistema real, falhas de log de auditoria podem ser tratadas 
            // salvando em arquivo de texto local caso o BD falhe.
            error_log("Falha ao registrar auditoria: " . $e->getMessage());
        }
    }
}
