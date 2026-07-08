<?php

namespace Core;

use PDO;
use PDOException;
use Exception;

/**
 * Classe de conexão com o Banco de Dados (Singleton)
 * Suporta roteamento dinâmico entre o Banco Master e o Banco Tenant.
 */
class Database
{
    private static ?Database $instance = null;
    
    private ?PDO $masterConnection = null;
    private ?PDO $tenantConnection = null;
    private string $activeConnection = 'master'; // 'master' ou 'tenant'

    private function __construct() {}

    /**
     * Retorna a instância única (Singleton).
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Conecta ao Banco de Dados Master
     */
    public function connectMaster(): PDO
    {
        if ($this->masterConnection === null) {
            $config = require BASE_PATH . '/config/app.php';
            $dbConfig = $config['db_master'] ?? $config['master'] ?? [];

            $host = $dbConfig['host'] ?? 'localhost';
            $dbname = $dbConfig['name'] ?? $dbConfig['dbname'] ?? 'patrimonio_master';
            $charset = $dbConfig['charset'] ?? 'utf8mb4';
            $user = $dbConfig['user'] ?? 'root';
            $password = $dbConfig['pass'] ?? $dbConfig['password'] ?? '';

            $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
            
            try {
                $this->masterConnection = new PDO($dsn, $user, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                // Em produção, deve logar o erro e não exibir detalhes na tela
                die("Erro de conexão com o banco master.");
            }
        }
        
        $this->activeConnection = 'master';
        return $this->masterConnection;
    }

    /**
     * Conecta a um Banco de Dados Tenant dinamicamente
     */
    public function connectTenant(string $host, string $dbname, string $user, string $password): PDO
    {
        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        
        try {
            $this->tenantConnection = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            
            $this->activeConnection = 'tenant';
            return $this->tenantConnection;
        } catch (PDOException $e) {
            die("Erro de conexão com o banco do cliente: " . $e->getMessage());
        }
    }

    /**
     * Retorna a conexão PDO ativa no momento.
     */
    public function getConnection(): PDO
    {
        if ($this->activeConnection === 'tenant' && $this->tenantConnection !== null) {
            return $this->tenantConnection;
        }

        if ($this->masterConnection === null) {
            $this->connectMaster();
        }

        return $this->masterConnection;
    }

    /**
     * Força a troca para a conexão Master
     */
    public function switchToMaster(): void
    {
        $this->activeConnection = 'master';
    }

    /**
     * Força a troca para a conexão Tenant
     */
    public function switchToTenant(): void
    {
        if ($this->tenantConnection === null) {
            throw new Exception("Nenhuma conexão de tenant estabelecida.");
        }
        $this->activeConnection = 'tenant';
    }
}
