-- Criação do Banco de Dados Master
CREATE DATABASE IF NOT EXISTS patrimonio_master CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE patrimonio_master;

-- Tabela de Tenants (Empresas)
CREATE TABLE IF NOT EXISTS tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cnpj VARCHAR(14) UNIQUE NOT NULL,
    razao_social VARCHAR(255) NOT NULL,
    nome_fantasia VARCHAR(255),
    db_host VARCHAR(255) NOT NULL,
    db_name VARCHAR(255) NOT NULL,
    db_user VARCHAR(255) NOT NULL,
    db_pass TEXT NOT NULL, -- Senha criptografada do BD do Tenant
    status ENUM('active', 'suspended', 'blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de Roteamento de Login (Mapeia qual CPF/CNPJ pertence a qual BD)
CREATE TABLE IF NOT EXISTS global_users_routing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document VARCHAR(14) UNIQUE NOT NULL, -- CPF do funcionário ou CNPJ do Admin
    tenant_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- Superusuários (Gestores do SaaS)
CREATE TABLE IF NOT EXISTS super_admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------
-- DADOS DE TESTE (SEED)
-- ---------------------------------------------------------

-- 1. Super Admin
INSERT INTO super_admins (email, password_hash) 
VALUES ('cristiammatos@icloud.com', '$2y$12$NKeCmEp.wwUWrF1X.pFtfOILyw1Z6y0PF.nc1QHebRe1L36tliQnC')
ON DUPLICATE KEY UPDATE
    password_hash = VALUES(password_hash);

-- 2. Inserindo um Tenant Fictício para testes
-- Nota: db_pass está criptografado (simulação para testes, na prática será gerado via PHP)
-- A senha real do BD mockada aqui será processada via Core\Security::encrypt('senha_secreta_do_bd')
-- Vou deixar um placeholder. Ao rodarmos o PHP, testaremos com dados reais.
