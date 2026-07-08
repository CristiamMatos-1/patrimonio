<?php

namespace App\Services;

use Core\Database;
use Core\Security;
use Exception;
use PDO;

class TenantProvisioningService
{
    /**
     * Provisiona um novo Tenant (Empresa) e configura seu banco de dados isolado.
     */
    public function provision(array $data): bool
    {
        $dbInstance = Database::getInstance();
        $masterDb = $dbInstance->connectMaster();

        $cnpj = preg_replace('/[^0-9]/', '', $data['cnpj']);
        // Aplicando trim para evitar erros de espaço em branco copiados sem querer
        $tenantDbName = trim($data['db_name']);
        $tenantDbUser = trim($data['db_user']); 
        $tenantDbPass = trim($data['db_pass']); 
        $tenantDbHost = 'localhost'; // Host do banco no cPanel geralmente é localhost

        try {
            // 1. Inicia Transação no Master
            $masterDb->beginTransaction();

            // Criptografa a senha antes de salvar no master
            $encryptedPass = Security::encrypt($tenantDbPass);

            // 2. Insere na tabela tenants
            $stmt = $masterDb->prepare("
                INSERT INTO tenants (cnpj, razao_social, nome_fantasia, db_host, db_name, db_user, db_pass) 
                VALUES (:cnpj, :razao_social, :nome_fantasia, :db_host, :db_name, :db_user, :db_pass)
            ");
            $stmt->execute([
                ':cnpj' => $cnpj,
                ':razao_social' => $data['razao_social'],
                ':nome_fantasia' => $data['nome_fantasia'],
                ':db_host' => $tenantDbHost,
                ':db_name' => $tenantDbName,
                ':db_user' => $tenantDbUser,
                ':db_pass' => $encryptedPass
            ]);

            $tenantId = $masterDb->lastInsertId();

            // 3. Adiciona roteamento do CNPJ para este tenant
            $stmtRouting = $masterDb->prepare("
                INSERT INTO global_users_routing (document, tenant_id) VALUES (:document, :tenant_id)
            ");
            $stmtRouting->execute([
                ':document' => $cnpj,
                ':tenant_id' => $tenantId
            ]);

            // 4. NÃO VAMOS CRIAR O BANCO DE DADOS PELO PHP.
            // O cPanel proíbe (Access Denied for CREATE DATABASE) por questões de segurança de hospedagem compartilhada.
            // Assumimos que o Superadmin já criou o banco e o usuário pelo painel do cPanel, 
            // e apenas informou as credenciais no formulário.

            // 5. Conecta no novo banco de dados para rodar as migrations (scripts)
            $tenantDb = $dbInstance->connectTenant($tenantDbHost, $tenantDbName, $tenantDbUser, $tenantDbPass);
            
            $sqlScript = file_get_contents(BASE_PATH . '/scripts/02_tenant_db.sql');
            if (!$sqlScript) {
                throw new Exception("Script de criação do tenant não encontrado.");
            }

            // Executa o script inteiro no novo banco
            $tenantDb->exec($sqlScript);

            // 6. Inserir a Matriz e o Administrador local no banco do Tenant
            $this->seedTenantInitialData($tenantDb, $cnpj, $data);

            // Confirma tudo no master
            $masterDb->commit();

            return true;

        } catch (Exception $e) {
            $masterDb->rollBack();
            // Como não criamos o banco via PHP, não precisamos tentar dar DROP nele aqui.
            // Apenas repassamos a exceção para a tela.
            throw $e;
        }
    }

    /**
     * Insere os dados iniciais do Tenant no seu próprio banco de dados
     */
    private function seedTenantInitialData(PDO $tenantDb, string $cnpj, array $data): void
    {
        // Cria a Matriz
        $stmtBranch = $tenantDb->prepare("
            INSERT INTO branches (name, is_headquarters, cnpj) 
            VALUES (:name, 1, :cnpj)
        ");
        $stmtBranch->execute([
            ':name' => 'Matriz - ' . $data['nome_fantasia'],
            ':cnpj' => $cnpj
        ]);
        $branchId = $tenantDb->lastInsertId();

        // O Role ID 1 é 'Administrador' conforme o script 02_tenant_db.sql
        $adminRoleId = 1;

        // Cria o usuário administrador local usando o CNPJ como login
        $passwordHash = password_hash($data['admin_password'], PASSWORD_DEFAULT);

        $stmtUser = $tenantDb->prepare("
            INSERT INTO users (cpf, name, email, password_hash, role_id, branch_id) 
            VALUES (:cpf, :name, :email, :password_hash, :role_id, :branch_id)
        ");
        $stmtUser->execute([
            ':cpf' => $cnpj, // O Admin fará login com CNPJ
            ':name' => 'Administrador',
            ':email' => $data['admin_email'],
            ':password_hash' => $passwordHash,
            ':role_id' => $adminRoleId,
            ':branch_id' => $branchId
        ]);
    }
}
