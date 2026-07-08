<?php

namespace Core;

use PDO;

/**
 * Model Base
 * Fornece a conexão com o banco de dados ativo.
 */
abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        // Ao instanciar qualquer Model, ele já pega a conexão ativa 
        // (Master ou Tenant, dependendo de onde estamos no fluxo)
        $this->db = Database::getInstance()->getConnection();
    }

    protected function tableExists(string $tableName): bool
    {
        $stmt = $this->db->prepare("SHOW TABLES LIKE :table");
        $stmt->execute([':table' => $tableName]);
        return (bool) $stmt->fetchColumn();
    }
}
