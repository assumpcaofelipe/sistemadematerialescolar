-- =============================================================
-- Central de Pedidos Escolares
-- Schema do banco de dados (MySQL / utf8mb4)
-- =============================================================

CREATE DATABASE IF NOT EXISTS central_pedidos
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE central_pedidos;

-- -------------------------------------------------------------
-- usuarios (escolas e admin)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(150) NOT NULL,
    email       VARCHAR(190) NOT NULL UNIQUE,
    senha       VARCHAR(255) NOT NULL,
    tipo        ENUM('escola','admin','supervisor') NOT NULL DEFAULT 'escola',
    nome_escola VARCHAR(150) NULL,
    status      TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- categorias
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categorias (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(150) NOT NULL,
    slug       VARCHAR(160) NOT NULL UNIQUE,
    status     TINYINT(1) NOT NULL DEFAULT 1,
    ordem      INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- produtos
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS produtos (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria_id       INT UNSIGNED NOT NULL,
    nome               VARCHAR(600) NOT NULL,
    slug               VARCHAR(190) NOT NULL UNIQUE,
    descricao          TEXT NOT NULL,
    imagem             VARCHAR(255) NULL,
    quantidade_estoque INT NOT NULL DEFAULT 0,
    status             TINYINT(1) NOT NULL DEFAULT 1,
    created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_produtos_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_produtos_categoria (categoria_id),
    INDEX idx_produtos_status (status),
    INDEX idx_produtos_status_estoque (status, quantidade_estoque)
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- pedidos
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pedidos (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero     INT UNSIGNED NOT NULL UNIQUE,
    usuario_id INT UNSIGNED NOT NULL,
    status     ENUM('realizado','em_andamento','concluido','cancelado') NOT NULL DEFAULT 'realizado',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pedidos_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_pedidos_usuario (usuario_id),
    INDEX idx_pedidos_status (status),
    INDEX idx_pedidos_usuario_status (usuario_id, status),
    INDEX idx_pedidos_created_at (created_at)
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- itens_pedido
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS itens_pedido (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id    INT UNSIGNED NOT NULL,
    produto_id   INT UNSIGNED NOT NULL,
    produto_nome VARCHAR(200) NOT NULL,
    quantidade   INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_itens_pedido
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_itens_produto
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_itens_pedido (pedido_id)
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- configuracoes (chave/valor)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS configuracoes (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chave      VARCHAR(100) NOT NULL UNIQUE,
    valor      VARCHAR(255) NOT NULL DEFAULT '',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Inserções padrão -------------------------------------------------

INSERT INTO configuracoes (chave, valor) VALUES
    ('whatsapp_secretaria', ''),
    ('email_secretaria', '')
ON DUPLICATE KEY UPDATE chave = chave;

-- Categoria padrão "Material Escolar" (usada se o CSV não existir)
INSERT INTO categorias (nome, slug, status, ordem)
SELECT 'Material Escolar', 'material-escolar', 1, 0
WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE slug = 'material-escolar');

-- Usuario administrador inicial (login: teste@teste.com.br / senha: teste@123)
-- IMPORTANTE: trocar a senha apos o primeiro acesso antes de colocar em producao.
INSERT INTO usuarios (nome, email, senha, tipo, nome_escola, status)
SELECT 'Administrador', 'teste@teste.com.br',
       '$2y$12$fCWIUjxmVI/Y7QkdY2ZkmuQcD4o0JxGzRjCeaNDcu0k54BT9r2uHi',
       'admin', NULL, 1
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'teste@teste.com.br');