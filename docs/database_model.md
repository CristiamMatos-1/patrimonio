# Modelagem de Dados

## Banco de Dados Master (`db_master`)
Responsável por gerenciar os tenants, mapeamento de acessos e credenciais globais.

```sql
-- Armazena os dados das empresas contratantes e as credenciais do seu BD isolado
CREATE TABLE tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cnpj VARCHAR(14) UNIQUE NOT NULL,
    razao_social VARCHAR(255) NOT NULL,
    nome_fantasia VARCHAR(255),
    db_host VARCHAR(255) NOT NULL,
    db_name VARCHAR(255) NOT NULL,
    db_user VARCHAR(255) NOT NULL,
    db_pass VARCHAR(255) NOT NULL, -- Deve ser armazenado fortemente criptografado (ex: OpenSSL AES-256-CBC)
    status ENUM('active', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de roteamento para identificar qual Tenant o CPF pertence no momento do login
CREATE TABLE global_users_routing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document VARCHAR(14) UNIQUE NOT NULL, -- CPF (Funcionário) ou CNPJ (Admin)
    tenant_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- Acesso exclusivo do desenvolvedor/dono do SaaS
CREATE TABLE super_admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## Banco de Dados Tenant (`db_tenant_xxx`)
Gerado dinamicamente para cada empresa. Contém exclusivamente os dados de negócio daquele CNPJ.

```sql
-- Matriz e Filiais/Congregações
CREATE TABLE branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    is_headquarters BOOLEAN DEFAULT FALSE,
    cnpj VARCHAR(14) NULL,
    cep VARCHAR(8),
    address VARCHAR(255),
    number VARCHAR(50),
    neighborhood VARCHAR(100),
    city VARCHAR(100),
    state VARCHAR(2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Controle de Acesso (ACL)
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    permissions JSON NOT NULL -- Ex: ["asset.create", "asset.delete", "user.view"]
);

-- Usuários Locais (Funcionários e Administrador)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cpf VARCHAR(11) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    branch_id INT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id)
);

-- Core: Gestão Patrimonial
CREATE TABLE assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    asset_number VARCHAR(50) UNIQUE NOT NULL,
    qr_code_hash VARCHAR(255) UNIQUE NOT NULL,
    acquisition_date DATE,
    value DECIMAL(10,2),
    status ENUM('active', 'maintenance', 'written_off') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id)
);

-- Inventário Patrimonial / Auditoria de Conferência
CREATE TABLE asset_inventory_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sector VARCHAR(100) NOT NULL,
    created_by INT NOT NULL,
    finalized_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE asset_inventory_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inventory_session_id INT NOT NULL,
    asset_id INT NOT NULL,
    checked_at DATETIME NULL,
    checked_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_inventory_asset (inventory_session_id, asset_id),
    FOREIGN KEY (inventory_session_id) REFERENCES asset_inventory_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    FOREIGN KEY (checked_by) REFERENCES users(id)
);

-- Logs de Auditoria de Segurança
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL, -- CREATE, UPDATE, DELETE, LOGIN
    table_name VARCHAR(50) NOT NULL,
    record_id INT,
    old_data JSON,
    new_data JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```