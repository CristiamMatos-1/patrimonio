<?php

namespace App\Models;

use Core\Model;
use PDO;

class RoutingModel extends Model
{
    public function getTenantByDocument(string $document): ?array
    {
        $document = preg_replace('/[^0-9]/', '', $document);

        $sql = "
            SELECT t.id as tenant_id, t.cnpj, t.db_host, t.db_name, t.db_user, t.db_pass, t.status 
            FROM global_users_routing gur
            INNER JOIN tenants t ON t.id = gur.tenant_id
            WHERE gur.document = :document
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':document', $document, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Busca as credenciais do banco de dados do Tenant com base no documento (CPF/CNPJ)
     */
    public function getTenantCredentialsByDocument(string $document): ?array
    {
        $result = $this->getTenantByDocument($document);

        if ($result && $result['status'] === 'active') {
            return $result;
        }

        return null;
    }
}
