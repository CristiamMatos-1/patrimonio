CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  perfil ENUM('administrador','operador','consulta') NOT NULL DEFAULT 'consulta',
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bens (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tombamento BIGINT UNSIGNED UNIQUE,
  codigo_interno VARCHAR(80),
  descricao TEXT NOT NULL,
  categoria VARCHAR(120),
  marca VARCHAR(120),
  modelo VARCHAR(120),
  numero_serie VARCHAR(120),
  valor_aquisicao DECIMAL(12,2),
  data_aquisicao DATE,
  vida_util INT,
  centro_custo VARCHAR(120),
  setor VARCHAR(120),
  localizacao VARCHAR(150),
  responsavel VARCHAR(150),
  local_id INT UNSIGNED NULL,
  status ENUM('ativo','emprestado','baixado') NOT NULL DEFAULT 'ativo',
  foto_url VARCHAR(255),
  observacoes TEXT,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

ALTER TABLE bens
  ADD CONSTRAINT fk_bens_local FOREIGN KEY (local_id) REFERENCES locais(id);

CREATE TABLE IF NOT EXISTS movimentacoes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  bem_id INT UNSIGNED NOT NULL,
  tipo VARCHAR(50) NOT NULL,
  usuario_id INT UNSIGNED,
  data DATETIME NOT NULL,
  observacao TEXT,
  dados_json JSON NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mov_bem FOREIGN KEY (bem_id) REFERENCES bens(id),
  CONSTRAINT fk_mov_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS emprestimos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  bem_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED,
  retirado_por_nome VARCHAR(150),
  retirado_por_documento VARCHAR(50),
  retirado_por_contato VARCHAR(120),
  destino_local_id INT UNSIGNED NULL,
  destino_endereco TEXT,
  data_saida DATETIME NOT NULL,
  data_prevista DATE,
  data_devolucao DATETIME NULL,
  status ENUM('aberto','devolvido','atrasado','cancelado') NOT NULL DEFAULT 'aberto',
  observacao TEXT,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_emp_bem FOREIGN KEY (bem_id) REFERENCES bens(id),
  CONSTRAINT fk_emp_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  CONSTRAINT fk_emp_destino_local FOREIGN KEY (destino_local_id) REFERENCES locais(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setor VARCHAR(120) NOT NULL,
  criado_por INT UNSIGNED,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  finalizado_em TIMESTAMP NULL,
  CONSTRAINT fk_inv_usuario FOREIGN KEY (criado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventario_itens (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  inventario_id INT UNSIGNED NOT NULL,
  bem_id INT UNSIGNED NOT NULL,
  conferido_em DATETIME NULL,
  conferido_por INT UNSIGNED,
  CONSTRAINT uq_inv_item UNIQUE (inventario_id, bem_id),
  CONSTRAINT fk_inv_item_inv FOREIGN KEY (inventario_id) REFERENCES inventarios(id),
  CONSTRAINT fk_inv_item_bem FOREIGN KEY (bem_id) REFERENCES bens(id),
  CONSTRAINT fk_inv_item_usuario FOREIGN KEY (conferido_por) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
