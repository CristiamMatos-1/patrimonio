-- Estrutura Base para um Banco de Dados de Tenant (Cliente)
-- Este script será executado via PHP (PDO) toda vez que uma nova empresa for cadastrada.

-- Matriz e Filiais/Congregações
CREATE TABLE IF NOT EXISTS branches (
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
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    permissions JSON NOT NULL -- Ex: ["asset.create", "asset.delete", "user.view"]
);

-- Usuários Locais (Funcionários e Administrador)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cpf VARCHAR(14) UNIQUE NOT NULL, -- Permite CPF ou CNPJ do admin principal
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
CREATE TABLE IF NOT EXISTS assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    asset_number VARCHAR(50) UNIQUE NOT NULL, -- Número de Tombamento (Gerado automaticamente)
    internal_code VARCHAR(100),
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    brand VARCHAR(100),
    model VARCHAR(100),
    serial_number VARCHAR(100),
    acquisition_date DATE,
    value DECIMAL(10,2),
    useful_life_years INT DEFAULT 5, -- Vida útil para depreciação
    cost_center VARCHAR(100),
    sector VARCHAR(100),
    location VARCHAR(255),
    current_responsible VARCHAR(255),
    qr_code_hash VARCHAR(255) UNIQUE NOT NULL,
    photo_url VARCHAR(255),
    observations TEXT,
    status ENUM('active', 'maintenance', 'borrowed', 'written_off') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id)
);

-- Controle de Movimentações (Histórico inalterável)
CREATE TABLE IF NOT EXISTS asset_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    movement_type ENUM('transfer', 'responsible_change', 'loan', 'return', 'write_off') NOT NULL,
    user_id INT NOT NULL, -- Usuário que registrou a movimentação
    movement_date DATETIME NOT NULL,
    observations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Controle de Empréstimos Ativos/Histórico
CREATE TABLE IF NOT EXISTS asset_loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    borrowed_by VARCHAR(255) NOT NULL, -- Nome de quem retirou
    out_date DATE NOT NULL,
    expected_return_date DATE,
    destination_location VARCHAR(255) NULL,
    return_date DATE,
    status ENUM('active', 'returned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE
);

-- Inventário Patrimonial / Auditoria de Conferência
CREATE TABLE IF NOT EXISTS asset_inventory_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sector VARCHAR(100) NOT NULL,
    created_by INT NOT NULL,
    finalized_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS asset_inventory_items (
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
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT,
    old_data JSON,
    new_data JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Inserindo Roles Padrões
INSERT INTO roles (name, permissions) VALUES 
('Administrador', '["*"]'),
('Gestor de Patrimônio', '["asset.view", "asset.create", "asset.update"]'),
('Auditor', '["asset.view", "audit.view"]');
