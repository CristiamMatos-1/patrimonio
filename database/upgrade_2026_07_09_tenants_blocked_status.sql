-- Permite bloqueio explícito de tenant pelo superadmin.
-- Se o arquivo começar com "<!DOCTYPE html>", foi baixada a página do GitHub em vez do .sql bruto.
ALTER TABLE tenants
    MODIFY COLUMN status ENUM('active', 'suspended', 'blocked') DEFAULT 'active';
