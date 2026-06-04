SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `ifruit`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `ifruit`;

CREATE TABLE `administrador` (
  `id_administrador` int(11)      NOT NULL AUTO_INCREMENT,
  `nome`             varchar(40)  NOT NULL,
  `email`            varchar(80)  NOT NULL,
  `senha`            varchar(256) NOT NULL,
  PRIMARY KEY (`id_administrador`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `comprador` (
  `id_comprador` int(11)     NOT NULL AUTO_INCREMENT,
  `nome`         varchar(40) NOT NULL,
  `cpf`          varchar(14) NOT NULL,
  `email`        varchar(80)  DEFAULT NULL,
  `telefone`     varchar(15)  DEFAULT NULL,
  PRIMARY KEY (`id_comprador`),
  UNIQUE KEY `cpf` (`cpf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `fruta` (
  `id_fruta`      int(11)     NOT NULL AUTO_INCREMENT,
  `nome`          varchar(40) NOT NULL,
  `precokg`       float       NOT NULL,
  PRIMARY KEY (`id_fruta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `venda` (
  `id_venda`         int(11)     NOT NULL AUTO_INCREMENT,
  `id_administrador` int(11)     NOT NULL,
  `id_comprador`     int(11)     NOT NULL,
  `valortotal`       double      NOT NULL,
  `datavenda`        date        NOT NULL,
  `numrecib`         int(11)     NOT NULL,
  `formapag`         varchar(30) NOT NULL,
  PRIMARY KEY (`id_venda`),
  UNIQUE KEY `numrecib` (`numrecib`),
  KEY `id_administrador` (`id_administrador`),
  KEY `id_comprador` (`id_comprador`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `itemvenda` (
  `id_itemvenda` int(11)     NOT NULL AUTO_INCREMENT,
  `id_venda`     int(11)     NOT NULL,
  `id_fruta`     int(11)     NOT NULL,
  `nome`         varchar(40) NOT NULL,
  `peso`         float       NOT NULL,
  `preco`        float       NOT NULL,
  PRIMARY KEY (`id_itemvenda`),
  KEY `id_venda` (`id_venda`),
  KEY `id_fruta` (`id_fruta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `venda`
  ADD CONSTRAINT `venda_fk_administrador` FOREIGN KEY (`id_administrador`) REFERENCES `administrador` (`id_administrador`),
  ADD CONSTRAINT `venda_fk_comprador`     FOREIGN KEY (`id_comprador`)     REFERENCES `comprador`     (`id_comprador`);

ALTER TABLE `itemvenda`
  ADD CONSTRAINT `itemvenda_fk_venda` FOREIGN KEY (`id_venda`) REFERENCES `venda` (`id_venda`) ON DELETE CASCADE,
  ADD CONSTRAINT `itemvenda_fk_fruta` FOREIGN KEY (`id_fruta`) REFERENCES `fruta` (`id_fruta`);

COMMIT;
