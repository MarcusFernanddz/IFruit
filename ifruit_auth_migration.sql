-- Execute uma vez no banco de dados ifruit antes de usar o novo login.
-- A tabela administrador passa a representar todas as contas do sistema.

ALTER TABLE administrador
    MODIFY id_administrador INT(11) NOT NULL AUTO_INCREMENT,
    MODIFY nome VARCHAR(120) NOT NULL,
    MODIFY senha VARCHAR(255) NOT NULL,
    ADD nivel_acesso ENUM('admin', 'estagiario') NOT NULL DEFAULT 'admin',
    ADD ativo TINYINT(1) NOT NULL DEFAULT 1,
    ADD criado_por INT(11) DEFAULT NULL,
    ADD criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD KEY idx_administrador_criado_por (criado_por);

ALTER TABLE venda
    ADD registrado_por_email VARCHAR(80) DEFAULT NULL,
    ADD registrado_por_nome VARCHAR(120) DEFAULT NULL;

UPDATE venda v
LEFT JOIN administrador a ON a.id_administrador = v.id_administrador
SET v.registrado_por_email = a.email,
    v.registrado_por_nome = a.nome
WHERE v.registrado_por_email IS NULL;