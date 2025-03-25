-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS alugafacil CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE alugafacil;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefone VARCHAR(20) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'cliente', 'proprietario') NOT NULL DEFAULT 'cliente',
    status ENUM('ativo', 'pendente', 'inativo') NOT NULL DEFAULT 'pendente',
    token VARCHAR(64) DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    data_cadastro DATETIME NOT NULL,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (email),
    INDEX (status)
);

-- Tabela de imóveis
CREATE TABLE IF NOT EXISTS imoveis (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_proprietario INT UNSIGNED NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    descricao TEXT NOT NULL,
    quartos TINYINT UNSIGNED NOT NULL,
    banheiros TINYINT UNSIGNED NOT NULL,
    capacidade TINYINT UNSIGNED NOT NULL,
    camas TINYINT UNSIGNED NOT NULL,
    area FLOAT UNSIGNED NULL,
    valor_diaria DECIMAL(10,2) NOT NULL,
    taxa_limpeza DECIMAL(10,2) DEFAULT 0.00,
    taxa_servico DECIMAL(10,2) DEFAULT 0.00,
    cep VARCHAR(10) NOT NULL,
    endereco VARCHAR(255) NOT NULL,
    numero VARCHAR(10) NOT NULL,
    complemento VARCHAR(50) NULL,
    bairro VARCHAR(100) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado VARCHAR(2) NOT NULL,
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    distancia_praia INT UNSIGNED NULL,
    foto_principal VARCHAR(255) NOT NULL,
    destaque BOOLEAN DEFAULT FALSE,
    status ENUM('ativo', 'inativo', 'manutencao') NOT NULL DEFAULT 'ativo',
    data_cadastro DATETIME NOT NULL,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (cidade),
    INDEX (estado),
    INDEX (quartos),
    INDEX (status),
    INDEX (destaque),
    FOREIGN KEY (id_proprietario) REFERENCES usuarios(id)
);

-- Tabela de características dos imóveis
CREATE TABLE IF NOT EXISTS caracteristicas_imoveis (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_imovel INT UNSIGNED NOT NULL,
    wifi BOOLEAN DEFAULT FALSE,
    ar_condicionado BOOLEAN DEFAULT FALSE,
    piscina BOOLEAN DEFAULT FALSE,
    churrasqueira BOOLEAN DEFAULT FALSE,
    estacionamento BOOLEAN DEFAULT FALSE,
    tv BOOLEAN DEFAULT FALSE,
    cozinha BOOLEAN DEFAULT FALSE,
    maquina_lavar BOOLEAN DEFAULT FALSE,
    pet_friendly BOOLEAN DEFAULT FALSE,
    vista_mar BOOLEAN DEFAULT FALSE,
    varanda BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_imovel) REFERENCES imoveis(id) ON DELETE CASCADE
);

-- Tabela de imagens dos imóveis
CREATE TABLE IF NOT EXISTS imagens_imoveis (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_imovel INT UNSIGNED NOT NULL,
    imagem VARCHAR(255) NOT NULL,
    descricao VARCHAR(255) NULL,
    ordem TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (id_imovel) REFERENCES imoveis(id) ON DELETE CASCADE
);

-- Tabela de regras dos imóveis
CREATE TABLE IF NOT EXISTS regras_imoveis (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_imovel INT UNSIGNED NOT NULL,
    regra VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_imovel) REFERENCES imoveis(id) ON DELETE CASCADE
);

-- Tabela de reservas
CREATE TABLE IF NOT EXISTS reservas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    id_imovel INT UNSIGNED NOT NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    data_entrada DATE NOT NULL,
    data_saida DATE NOT NULL,
    adultos TINYINT UNSIGNED NOT NULL,
    criancas TINYINT UNSIGNED DEFAULT 0,
    valor_diaria DECIMAL(10,2) NOT NULL,
    valor_taxas DECIMAL(10,2) NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    forma_pagamento ENUM('pix', 'cartao', 'boleto', 'transferencia') NOT NULL,
    status ENUM('pendente', 'confirmada', 'cancelada', 'concluida') NOT NULL DEFAULT 'pendente',
    observacoes TEXT NULL,
    data_reserva DATETIME NOT NULL,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (id_imovel) REFERENCES imoveis(id),
    INDEX (codigo),
    INDEX (status),
    INDEX (data_entrada),
    INDEX (data_saida)
);

-- Tabela de pagamentos
CREATE TABLE IF NOT EXISTS pagamentos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_reserva INT UNSIGNED NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    metodo ENUM('pix', 'cartao', 'boleto', 'transferencia') NOT NULL,
    status ENUM('pendente', 'confirmado', 'cancelado', 'estornado') NOT NULL DEFAULT 'pendente',
    codigo_transacao VARCHAR(100) NULL,
    data_pagamento DATETIME NULL,
    data_cadastro DATETIME NOT NULL,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_reserva) REFERENCES reservas(id),
    INDEX (status)
);

-- Tabela de avaliações
CREATE TABLE IF NOT EXISTS avaliacoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    id_imovel INT UNSIGNED NOT NULL,
    id_reserva INT UNSIGNED NOT NULL,
    nota TINYINT UNSIGNED NOT NULL,
    comentario TEXT NULL,
    resposta TEXT NULL,
    status ENUM('pendente', 'aprovada', 'rejeitada') NOT NULL DEFAULT 'pendente',
    data_avaliacao DATETIME NOT NULL,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (id_imovel) REFERENCES imoveis(id),
    FOREIGN KEY (id_reserva) REFERENCES reservas(id),
    INDEX (status)
);

-- Tabela de favoritos
CREATE TABLE IF NOT EXISTS favoritos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    id_imovel INT UNSIGNED NOT NULL,
    data_cadastro DATETIME NOT NULL,
    UNIQUE KEY unique_favorito (id_usuario, id_imovel),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_imovel) REFERENCES imoveis(id) ON DELETE CASCADE
);

-- Tabela de mensagens
CREATE TABLE IF NOT EXISTS mensagens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_remetente INT UNSIGNED NULL,
    id_destinatario INT UNSIGNED NULL,
    assunto VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    lida BOOLEAN DEFAULT FALSE,
    data_envio DATETIME NOT NULL,
    data_leitura DATETIME NULL,
    FOREIGN KEY (id_remetente) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (id_destinatario) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de contatos do site
CREATE TABLE IF NOT EXISTS contatos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NULL,
    assunto VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    status ENUM('novo', 'respondido', 'arquivado') NOT NULL DEFAULT 'novo',
    data_envio DATETIME NOT NULL,
    data_resposta DATETIME NULL,
    INDEX (status)
);

-- Inserindo um usuário administrador (senha: admin123)
INSERT INTO usuarios (nome, email, telefone, senha, tipo, status, data_cadastro)
VALUES ('Administrador', 'admin@alugafacil.com', '(12) 98765-4321', '$2y$10$wIbwPOQe0SBNs3KpBQoJi.yrFBm0OKBJswfLQCZQYghXTKd8yCmLy', 'admin', 'ativo', NOW());

-- Inserindo alguns imóveis de exemplo
INSERT INTO imoveis 
(id_proprietario, titulo, slug, descricao, quartos, banheiros, capacidade, camas, area, valor_diaria, taxa_limpeza, taxa_servico, 
 cep, endereco, numero, bairro, cidade, estado, foto_principal, destaque, status, data_cadastro)
VALUES
(1, 'Casa na Praia de Ubatuba com Vista para o Mar', 'casa-praia-ubatuba-vista-mar', 
 'Linda casa na praia de Ubatuba com vista privilegiada para o mar. Acomodação perfeita para famílias e grupos de amigos que buscam conforto e tranquilidade. A apenas 50 metros da praia.', 
 3, 2, 8, 4, 120, 550.00, 150.00, 50.00, '11680-000', 'Rua das Conchas', '123', 'Praia Grande', 'Ubatuba', 'SP', 
 'img/properties/casa1.jpg', TRUE, 'ativo', NOW()),
 
(1, 'Apartamento de Frente para o Mar em Caraguatatuba', 'apartamento-frente-mar-caraguatatuba', 
 'Apartamento moderno e completamente mobiliado de frente para o mar em Caraguatatuba. Vista panorâmica, com acesso direto à praia. Perfeito para quem busca conforto e praticidade.', 
 2, 1, 4, 2, 75, 350.00, 100.00, 30.00, '11665-000', 'Avenida da Praia', '456', 'Centro', 'Caraguatatuba', 'SP', 
 'img/properties/apto1.jpg', TRUE, 'ativo', NOW()),
 
(1, 'Chalé Aconchegante em Ilhabela', 'chale-aconchegante-ilhabela', 
 'Chalé aconchegante em meio à natureza em Ilhabela. Perfeito para casais e pequenas famílias que buscam tranquilidade e contato com a natureza. A 10 minutos da praia.', 
 1, 1, 3, 2, 50, 280.00, 80.00, 20.00, '11630-000', 'Estrada da Mata', '789', 'Vila', 'Ilhabela', 'SP', 
 'img/properties/chale1.jpg', FALSE, 'ativo', NOW()),
 
(1, 'Casa Ampla com Piscina em São Sebastião', 'casa-ampla-piscina-sao-sebastiao', 
 'Casa ampla e bem equipada com piscina privativa em São Sebastião. Ideal para grandes grupos de famílias e amigos. Localizada em condomínio fechado com segurança 24h.', 
 4, 3, 12, 6, 180, 750.00, 200.00, 80.00, '11600-000', 'Rua dos Coqueiros', '101', 'Juquehy', 'São Sebastião', 'SP', 
 'img/properties/casa2.jpg', TRUE, 'ativo', NOW()),
 
(1, 'Apartamento Mobiliado em Bertioga', 'apartamento-mobiliado-bertioga', 
 'Apartamento completamente mobiliado em Bertioga, a apenas 200 metros da praia. Conforto e praticidade para suas férias no litoral. Condomínio com piscina, academia e churrasqueira.', 
 2, 2, 6, 3, 80, 320.00, 100.00, 30.00, '11250-000', 'Avenida Vicente de Carvalho', '222', 'Centro', 'Bertioga', 'SP', 
 'img/properties/apto2.jpg', FALSE, 'ativo', NOW());

-- Inserindo características para os imóveis
INSERT INTO caracteristicas_imoveis 
(id_imovel, wifi, ar_condicionado, piscina, churrasqueira, estacionamento, tv, cozinha, maquina_lavar, pet_friendly, vista_mar, varanda)
VALUES
(1, TRUE, TRUE, FALSE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE),
(2, TRUE, TRUE, TRUE, FALSE, TRUE, TRUE, TRUE, FALSE, FALSE, TRUE, TRUE),
(3, TRUE, FALSE, FALSE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, FALSE, TRUE),
(4, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, FALSE, FALSE, TRUE),
(5, TRUE, TRUE, TRUE, FALSE, TRUE, TRUE, TRUE, TRUE, FALSE, FALSE, TRUE);

-- Inserindo imagens para os imóveis
INSERT INTO imagens_imoveis (id_imovel, imagem, descricao, ordem)
VALUES
(1, 'img/properties/casa1_1.jpg', 'Vista frontal da casa', 1),
(1, 'img/properties/casa1_2.jpg', 'Sala de estar', 2),
(1, 'img/properties/casa1_3.jpg', 'Quarto principal', 3),
(2, 'img/properties/apto1_1.jpg', 'Vista da sala para o mar', 1),
(2, 'img/properties/apto1_2.jpg', 'Cozinha americana', 2),
(3, 'img/properties/chale1_1.jpg', 'Vista externa do chalé', 1),
(3, 'img/properties/chale1_2.jpg', 'Interior aconchegante', 2),
(4, 'img/properties/casa2_1.jpg', 'Vista da piscina', 1),
(4, 'img/properties/casa2_2.jpg', 'Área gourmet', 2),
(5, 'img/properties/apto2_1.jpg', 'Vista da varanda', 1),
(5, 'img/properties/apto2_2.jpg', 'Sala de jantar', 2);

-- Inserindo regras para os imóveis
INSERT INTO regras_imoveis (id_imovel, regra)
VALUES
(1, 'Check-in a partir das 14h'),
(1, 'Check-out até as 11h'),
(1, 'Não é permitido fumar dentro do imóvel'),
(1, 'Festas e eventos somente com autorização prévia'),
(2, 'Check-in a partir das 15h'),
(2, 'Check-out até as 12h'),
(2, 'Não é permitido fumar'),
(2, 'Não são permitidos animais de estimação'),
(3, 'Check-in a partir das 14h'),
(3, 'Check-out até as 11h'),
(3, 'Pets de pequeno porte são bem-vindos'),
(4, 'Check-in a partir das 15h'),
(4, 'Check-out até as 11h'),
(4, 'Não é permitido fumar'),
(4, 'Festas e eventos mediante consulta'),
(5, 'Check-in a partir das 14h'),
(5, 'Check-out até as 10h'),
(5, 'Não são permitidos animais de estimação'),
(5, 'Não é permitido fumar');