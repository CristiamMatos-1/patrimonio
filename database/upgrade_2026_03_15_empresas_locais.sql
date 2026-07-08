CREATE TABLE IF NOT EXISTS empresas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  razao_social VARCHAR(200) NOT NULL,
  nome_fantasia VARCHAR(200),
  cnpj VARCHAR(20),
  endereco TEXT,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS locais (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id INT UNSIGNED NOT NULL,
  nome VARCHAR(200) NOT NULL,
  tipo ENUM('sede','filial','local_encontro') NOT NULL DEFAULT 'filial',
  endereco TEXT,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_locais_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bens ADD COLUMN local_id INT UNSIGNED NULL;
ALTER TABLE bens ADD CONSTRAINT fk_bens_local FOREIGN KEY (local_id) REFERENCES locais(id);

ALTER TABLE emprestimos
  ADD COLUMN retirado_por_nome VARCHAR(150),
  ADD COLUMN retirado_por_documento VARCHAR(50),
  ADD COLUMN retirado_por_contato VARCHAR(120),
  ADD COLUMN destino_local_id INT UNSIGNED NULL,
  ADD COLUMN destino_endereco TEXT;

ALTER TABLE emprestimos ADD CONSTRAINT fk_emp_destino_local FOREIGN KEY (destino_local_id) REFERENCES locais(id);

