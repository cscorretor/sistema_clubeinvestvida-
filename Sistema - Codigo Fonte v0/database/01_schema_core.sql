-- =====================================================================
-- Clube Investvida — Sistema (substituto do Segflex)
-- Schema do NÚCLEO — MariaDB 10.5  (plano Business / Hostinger)
-- Foco: Seguros de Pessoas (Vida, Previdência, Saúde, Viagem, Renda)
-- Codificação: utf8mb4  •  Engine: InnoDB
-- Versão 0.1 — primeira fatia (Cadastro de Cliente)
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------
-- Tabelas de apoio (lookups) — substituem os "Cadastros" do Segflex
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS produtores (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome          VARCHAR(120) NOT NULL,
  ativo         TINYINT(1) NOT NULL DEFAULT 1,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- CLIENTES — entidade-raiz (PF e PJ)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS clientes (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo          VARCHAR(20) UNIQUE,                      -- Código Cliente (Segflex)
  pessoa          ENUM('PF','PJ') NOT NULL DEFAULT 'PF',
  tipo_cliente    ENUM('EFETIVO','PROSPECT','RELACIONAMENTO','CONDUTOR','LOCADOR') NOT NULL DEFAULT 'PROSPECT',
  status          ENUM('ATIVO','INATIVO') NOT NULL DEFAULT 'ATIVO',
  produtor_id     INT UNSIGNED NULL,
  intermedio      VARCHAR(80) NULL,                        -- origem/indicação (ex.: Google, Indicação)

  -- Identificação (PF)
  nome            VARCHAR(150) NOT NULL,                   -- nome do cliente / razão social
  cpf_cnpj        VARCHAR(18) NULL,                        -- só dígitos validados na aplicação
  doc_tipo        VARCHAR(20) NULL,                        -- RG, CNH, etc.
  doc_orgao       VARCHAR(20) NULL,
  doc_numero      VARCHAR(30) NULL,
  doc_emissao     DATE NULL,
  doc_validade    DATE NULL,
  profissao       VARCHAR(120) NULL,
  estado_civil    ENUM('SOLTEIRO','CASADO','DIVORCIADO','VIUVO','UNIAO_ESTAVEL') NULL,
  nascimento      DATE NULL,
  sexo            ENUM('M','F','OUTRO') NULL,
  faixa_renda     VARCHAR(40) NULL,

  -- Identificação (PJ) — preenchidos quando pessoa='PJ'
  nome_fantasia   VARCHAR(150) NULL,
  inscricao_est   VARCHAR(30) NULL,
  data_abertura   DATE NULL,

  -- Comunicação rápida
  apelido         VARCHAR(80) NULL,                        -- usado em e-mails
  celular_padrao  VARCHAR(20) NULL,
  email_padrao    VARCHAR(150) NULL,

  observacoes     TEXT NULL,
  data_cadastro   DATE NOT NULL DEFAULT (CURRENT_DATE),
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_clientes_produtor FOREIGN KEY (produtor_id) REFERENCES produtores(id),
  INDEX idx_clientes_nome (nome),
  INDEX idx_clientes_cpf  (cpf_cnpj),
  INDEX idx_clientes_tipo (tipo_cliente, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- CÔNJUGE — exibido condicionalmente quando estado_civil = CASADO
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cliente_conjuge (
  cliente_id   BIGINT UNSIGNED PRIMARY KEY,
  nome         VARCHAR(150) NULL,
  cpf          VARCHAR(14) NULL,
  nascimento   DATE NULL,
  CONSTRAINT fk_conjuge_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- CNH — exibido quando a seção CNH está ativa
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cliente_cnh (
  cliente_id          BIGINT UNSIGNED PRIMARY KEY,
  numero_registro     VARCHAR(20) NULL,
  categoria           VARCHAR(5) NULL,
  validade            DATE NULL,
  primeira_habilitacao DATE NULL,
  CONSTRAINT fk_cnh_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- Múltiplos contatos (espelha as abas Endereços / Telefones / E-mails)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cliente_enderecos (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id  BIGINT UNSIGNED NOT NULL,
  padrao      TINYINT(1) NOT NULL DEFAULT 0,
  tipo        ENUM('RESIDENCIAL','COMERCIAL','COBRANCA','OUTRO') NOT NULL DEFAULT 'RESIDENCIAL',
  cep         VARCHAR(9) NULL,
  logradouro  VARCHAR(150) NULL,
  numero      VARCHAR(15) NULL,
  complemento VARCHAR(80) NULL,
  bairro      VARCHAR(80) NULL,
  cidade      VARCHAR(80) NULL,
  uf          CHAR(2) NULL,
  CONSTRAINT fk_end_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
  INDEX idx_end_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cliente_telefones (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id  BIGINT UNSIGNED NOT NULL,
  padrao      TINYINT(1) NOT NULL DEFAULT 0,
  tipo        ENUM('CELULAR','RESIDENCIAL','COMERCIAL','WHATSAPP','0800','OUTRO') NOT NULL DEFAULT 'CELULAR',
  numero      VARCHAR(20) NOT NULL,
  observacao  VARCHAR(120) NULL,
  CONSTRAINT fk_tel_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
  INDEX idx_tel_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cliente_emails (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id  BIGINT UNSIGNED NOT NULL,
  padrao      TINYINT(1) NOT NULL DEFAULT 0,
  email       VARCHAR(150) NOT NULL,
  observacao  VARCHAR(120) NULL,
  CONSTRAINT fk_eml_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
  INDEX idx_eml_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cliente_contas_bancarias (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id  BIGINT UNSIGNED NOT NULL,
  banco       VARCHAR(80) NULL,
  agencia     VARCHAR(15) NULL,
  conta       VARCHAR(25) NULL,
  tipo        ENUM('CORRENTE','POUPANCA') NULL,
  titular     VARCHAR(150) NULL,
  CONSTRAINT fk_cb_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
  INDEX idx_cb_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- LOG DE AUDITORIA — o que o Segflex NÃO tinha
-- Registra quem alterou o quê e quando.
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_log (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario      VARCHAR(120) NULL,
  entidade     VARCHAR(60) NOT NULL,     -- ex.: 'clientes'
  entidade_id  BIGINT UNSIGNED NULL,
  acao         ENUM('CRIAR','ALTERAR','EXCLUIR') NOT NULL,
  dados_antes  JSON NULL,
  dados_depois JSON NULL,
  ip           VARCHAR(45) NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_entidade (entidade, entidade_id),
  INDEX idx_audit_data (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
-- Fim do schema do núcleo v0.1
