-- =====================================================================
-- Clube Investvida — Schema de APÓLICES (Seguros de Pessoas)
-- MariaDB 10.5  •  depende de 01_schema_core.sql
-- Ramos: Vida, Previdência, Saúde, Viagem, Renda
-- Versão 0.1
-- =====================================================================
SET NAMES utf8mb4; SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS seguradoras (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL, ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ramos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(60) NOT NULL,
  grupo ENUM('PESSOAS','PATRIMONIAL') NOT NULL DEFAULT 'PESSOAS'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- APÓLICE / PROPOSTA
CREATE TABLE IF NOT EXISTS apolices (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id      BIGINT UNSIGNED NOT NULL,
  ramo_id         INT UNSIGNED NOT NULL,
  seguradora_id   INT UNSIGNED NULL,
  produtor_id     INT UNSIGNED NULL,
  num_proposta    VARCHAR(40) NULL,
  num_apolice     VARCHAR(40) NULL,
  status          ENUM('PROSPECCAO','EM_EMISSAO','ATIVO','RENOVACAO','CANCELADO','INATIVO') NOT NULL DEFAULT 'EM_EMISSAO',
  inicio_vigencia DATE NULL,
  fim_vigencia    DATE NULL,
  capital_segurado DECIMAL(14,2) NULL,
  tipo_proposta   ENUM('NOVO','RENOVACAO','ENDOSSO') NOT NULL DEFAULT 'NOVO',
  apolice_origem_id BIGINT UNSIGNED NULL,                 -- p/ renovação/endosso
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_ap_cli FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  CONSTRAINT fk_ap_ramo FOREIGN KEY (ramo_id) REFERENCES ramos(id),
  CONSTRAINT fk_ap_seg FOREIGN KEY (seguradora_id) REFERENCES seguradoras(id),
  CONSTRAINT fk_ap_prod FOREIGN KEY (produtor_id) REFERENCES produtores(id),
  INDEX idx_ap_cliente (cliente_id), INDEX idx_ap_venc (fim_vigencia), INDEX idx_ap_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- VIDAS SEGURADAS (titular + dependentes) — núcleo de seguros de pessoas
CREATE TABLE IF NOT EXISTS apolice_vidas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  apolice_id BIGINT UNSIGNED NOT NULL,
  nome VARCHAR(150) NOT NULL,
  parentesco ENUM('TITULAR','CONJUGE','FILHO','PAI_MAE','OUTRO') NOT NULL DEFAULT 'TITULAR',
  nascimento DATE NULL,
  capital DECIMAL(14,2) NULL,
  CONSTRAINT fk_vida_ap FOREIGN KEY (apolice_id) REFERENCES apolices(id) ON DELETE CASCADE,
  INDEX idx_vida_ap (apolice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- BENEFICIÁRIOS (% deve somar 100 — validado na aplicação)
CREATE TABLE IF NOT EXISTS apolice_beneficiarios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  apolice_id BIGINT UNSIGNED NOT NULL,
  nome VARCHAR(150) NOT NULL,
  parentesco VARCHAR(30) NULL,
  percentual DECIMAL(5,2) NOT NULL DEFAULT 0,
  CONSTRAINT fk_benef_ap FOREIGN KEY (apolice_id) REFERENCES apolices(id) ON DELETE CASCADE,
  INDEX idx_benef_ap (apolice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- COBERTURAS contratadas
CREATE TABLE IF NOT EXISTS apolice_coberturas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  apolice_id BIGINT UNSIGNED NOT NULL,
  descricao VARCHAR(120) NOT NULL,
  capital DECIMAL(14,2) NULL,
  CONSTRAINT fk_cob_ap FOREIGN KEY (apolice_id) REFERENCES apolices(id) ON DELETE CASCADE,
  INDEX idx_cob_ap (apolice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PLANO FINANCEIRO / PARCELAS (fatura mensal típica de pessoas)
CREATE TABLE IF NOT EXISTS apolice_parcelas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  apolice_id BIGINT UNSIGNED NOT NULL,
  numero INT NOT NULL,
  vencimento DATE NULL,
  valor_cliente DECIMAL(12,2) NULL,
  valor_comissao DECIMAL(12,2) NULL,
  percentual_comissao DECIMAL(5,2) NULL,
  status ENUM('ABERTO','LIQUIDADO','CANCELADO') NOT NULL DEFAULT 'ABERTO',
  CONSTRAINT fk_parc_ap FOREIGN KEY (apolice_id) REFERENCES apolices(id) ON DELETE CASCADE,
  INDEX idx_parc_ap (apolice_id), INDEX idx_parc_venc (vencimento), INDEX idx_parc_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- RATEIO de comissão entre produtores
CREATE TABLE IF NOT EXISTS apolice_rateio (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  apolice_id BIGINT UNSIGNED NOT NULL,
  produtor_id INT UNSIGNED NOT NULL,
  percentual DECIMAL(5,2) NOT NULL,
  CONSTRAINT fk_rat_ap FOREIGN KEY (apolice_id) REFERENCES apolices(id) ON DELETE CASCADE,
  CONSTRAINT fk_rat_prod FOREIGN KEY (produtor_id) REFERENCES produtores(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados iniciais úteis
INSERT INTO ramos (nome,grupo) VALUES
 ('Vida','PESSOAS'),('Previdência','PESSOAS'),('Saúde','PESSOAS'),
 ('Viagem','PESSOAS'),('Renda','PESSOAS');

SET FOREIGN_KEY_CHECKS = 1;
-- Fim do schema de apólices v0.1
