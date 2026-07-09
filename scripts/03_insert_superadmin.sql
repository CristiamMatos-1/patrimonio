-- Script para criar/atualizar o superadmin no banco master.
-- Hash gerado com password_hash(PASSWORD_DEFAULT).
INSERT INTO super_admins (email, password_hash)
VALUES ('cristiammatos@icloud.com', '$2y$12$NKeCmEp.wwUWrF1X.pFtfOILyw1Z6y0PF.nc1QHebRe1L36tliQnC')
ON DUPLICATE KEY UPDATE
    password_hash = VALUES(password_hash);
