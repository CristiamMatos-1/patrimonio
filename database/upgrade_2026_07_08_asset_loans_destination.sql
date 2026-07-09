-- Upgrade para adicionar o campo destination_location na tabela asset_loans
ALTER TABLE asset_loans 
    ADD COLUMN destination_location VARCHAR(255) NULL AFTER expected_return_date;
