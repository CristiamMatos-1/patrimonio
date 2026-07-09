-- Permite bloqueio explícito de tenant pelo superadmin.
ALTER TABLE tenants
    MODIFY COLUMN status ENUM('active', 'suspended', 'blocked') DEFAULT 'active';
