-- =====================================================================
-- Clube Investvida — Schema CRM/Leads, Chamados e Usuários
-- MariaDB 10.5 • depende de 01_schema_core.sql
-- Versão 0.1
-- =====================================================================
SET NAMES utf8mb4; SET FOREIGN_KEY_CHECKS = 0;

-- ===================== USUÁRIOS / PERMISSÕES =====================
CREATE TABLE IF NOT EXISTS usuarios (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome          VARCHAR(120) NOT NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  senha_hash    VARCHAR(255) NOT NULL,          -- hash (bcrypt/argon) — nunca senha pura
  perfil        ENUM('ADMIN','COMUM','PRODUTOR') NOT NULL DEFAULT 'COMUM',
  produtor_id   INT UNSIGNED NULL,              -- vínculo quando perfil=PRODUTOR
  duas_etapas   TINYINT(1) NOT NULL DEFAULT 0,  -- 2FA habilitado
  ativo         TINYINT(1) NOT NULL DEFAULT 1,
  ultimo_acesso DATETIME NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_user_prod FOREIGN KEY (produtor_id) REFERENCES produtores(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissoes (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  modulo     VARCHAR(40) NOT NULL,   -- clientes, financeiro, relatorios, config...
  pode_ver   TINYINT(1) NOT NULL DEFAULT 1,
  pode_editar TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_perm_user FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  UNIQUE KEY uq_perm (usuario_id, modulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================== LEADS / CRM =====================
CREATE TABLE IF NOT EXISTS leads (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome         VARCHAR(150) NOT NULL,
  email        VARCHAR(150) NULL,
  telefone     VARCHAR(20) NULL,
  cpf          VARCHAR(14) NULL,
  origem       VARCHAR(60) NULL,      -- Landing Page, Instagram, Indicação, Google Contatos, Lista...
  ramo_interesse VARCHAR(40) NULL,    -- Vida, Previdência, Saúde, Viagem, Renda
  etapa        ENUM('NOVO','QUALIFICADO','PROPOSTA','FECHADO','PERDIDO') NOT NULL DEFAULT 'NOVO',
  score        ENUM('QUENTE','MORNO','FRIO','DESCARTAR') NULL,   -- classificação ICP
  score_valor  INT NULL,             -- 0-100 (calculado)
  produtor_id  INT UNSIGNED NULL,
  cliente_id   BIGINT UNSIGNED NULL, -- preenchido quando o lead vira cliente
  motivo_perda VARCHAR(120) NULL,
  google_contact_id VARCHAR(80) NULL, -- de-duplicação da importação Google
  proximo_contato DATE NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_lead_prod FOREIGN KEY (produtor_id) REFERENCES produtores(id),
  CONSTRAINT fk_lead_cli  FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  INDEX idx_lead_etapa (etapa), INDEX idx_lead_score (score), INDEX idx_lead_contato (proximo_contato)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lead_interacoes (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lead_id    BIGINT UNSIGNED NOT NULL,
  tipo       ENUM('LIGACAO','WHATSAPP','EMAIL','REUNIAO','NOTA') NOT NULL,
  descricao  TEXT NULL,
  usuario_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_int_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
  INDEX idx_int_lead (lead_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================== CAMPANHAS (evolução da Comunicação do Segflex) =====
CREATE TABLE IF NOT EXISTS campanhas (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome       VARCHAR(120) NOT NULL,
  canal      ENUM('EMAIL','WHATSAPP') NOT NULL DEFAULT 'EMAIL',
  gatilho    VARCHAR(60) NULL,     -- aniversario, vencimento_cnh, renovacao, licenciamento, parcela, manual
  segmento   VARCHAR(120) NULL,    -- filtro aplicado
  template   TEXT NULL,
  status     ENUM('RASCUNHO','AGENDADA','ENVIANDO','CONCLUIDA') NOT NULL DEFAULT 'RASCUNHO',
  agendada_para DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================== CHAMADOS / SINISTROS =====================
CREATE TABLE IF NOT EXISTS chamados (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tipo          ENUM('CLIENTE','SEGURO','FINANCEIRO','SINISTRO') NOT NULL DEFAULT 'CLIENTE',
  subtipo       VARCHAR(80) NULL,
  cliente_id    BIGINT UNSIGNED NULL,
  apolice_id    BIGINT UNSIGNED NULL,
  descricao     TEXT NULL,
  status        ENUM('PENDENTE','EM_ANDAMENTO','FINALIZADO') NOT NULL DEFAULT 'PENDENTE',
  prioridade    ENUM('BAIXA','MEDIA','ALTA') NOT NULL DEFAULT 'MEDIA',
  data_resolucao DATE NULL,
  responsavel_id INT UNSIGNED NULL,
  quem_fecha    ENUM('CRIADOR','QUALQUER') NOT NULL DEFAULT 'CRIADOR',
  created_by    INT UNSIGNED NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_cham_cli  FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  CONSTRAINT fk_cham_resp FOREIGN KEY (responsavel_id) REFERENCES usuarios(id),
  INDEX idx_cham_status (status), INDEX idx_cham_data (data_resolucao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chamado_movimentos (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  chamado_id BIGINT UNSIGNED NOT NULL,
  historico  TEXT NULL,
  novo_status ENUM('PENDENTE','EM_ANDAMENTO','FINALIZADO') NULL,
  nova_data  DATE NULL,
  usuario_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mov_cham FOREIGN KEY (chamado_id) REFERENCES chamados(id) ON DELETE CASCADE,
  INDEX idx_mov_cham (chamado_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
-- Fim do schema 03 v0.1
