-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 15/09/2026 às 01:20
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `ifruit`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `administrador`
--

CREATE TABLE `administrador` (
  `id_administrador` int(11) NOT NULL,
  `nome` varchar(40) NOT NULL,
  `email` varchar(80) NOT NULL,
  `senha` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `administrador`
--

INSERT INTO `administrador` (`id_administrador`, `nome`, `email`, `senha`) VALUES
(1, 'Administrador', 'admin@local', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `comprador`
--

CREATE TABLE `comprador` (
  `id_comprador` int(11) NOT NULL,
  `nome` varchar(40) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `email` varchar(80) DEFAULT NULL,
  `telefone` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `comprador`
--

INSERT INTO `comprador` (`id_comprador`, `nome`, `cpf`, `email`, `telefone`) VALUES
(5, 'Viaod', '70239498100', 'paulocatatau5@gmail.com', '64984118391'),
(7, 'lena', '29953743002', '', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `fruta`
--

CREATE TABLE `fruta` (
  `id_fruta` int(11) NOT NULL,
  `nome` varchar(40) NOT NULL,
  `precokg` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fruta`
--

INSERT INTO `fruta` (`id_fruta`, `nome`, `precokg`) VALUES
(4, 'Marcus', 12.4),
(5, '\\sas', 1.5),
(7, 'ANdre', 12),
(12, 'dff', 13),
(13, 'Matheus', 1000);

-- --------------------------------------------------------

--
-- Estrutura para tabela `itemvenda`
--

CREATE TABLE `itemvenda` (
  `id_itemvenda` int(11) NOT NULL,
  `id_venda` int(11) NOT NULL,
  `id_fruta` int(11) DEFAULT NULL,
  `nome` varchar(40) NOT NULL,
  `peso` float NOT NULL,
  `preco` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `itemvenda`
--

INSERT INTO `itemvenda` (`id_itemvenda`, `id_venda`, `id_fruta`, `nome`, `peso`, `preco`) VALUES
(1, 2, 7, 'ANdre', 1, 12),
(2, 3, 12, 'dff', 1.006, 13),
(3, 4, 4, 'Marcus', 20, 12.4),
(4, 5, 13, 'Matheus', 10, 1000),
(5, 5, 5, '\\sas', 1, 1.5),
(6, 6, 7, 'ANdre', 0.951, 12),
(7, 6, 5, '\\sas', 1, 1.5),
(8, 7, 4, 'Marcus', 4, 12.4),
(9, 8, 13, 'Matheus', 12, 1000),
(10, 8, NULL, '', 1, 0),
(11, 9, 12, 'dff', 1, 13),
(12, 9, 13, 'Matheus', 1.004, 1000),
(13, 10, 12, 'dff', 1.003, 13);

-- --------------------------------------------------------

--
-- Estrutura para tabela `venda`
--

CREATE TABLE `venda` (
  `id_venda` int(11) NOT NULL,
  `id_administrador` int(11) NOT NULL,
  `id_comprador` int(11) DEFAULT NULL,
  `cliente_nome` varchar(40) NOT NULL,
  `valortotal` double NOT NULL,
  `datavenda` date NOT NULL,
  `numrecib` int(11) NOT NULL,
  `formapag` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `venda`
--

INSERT INTO `venda` (`id_venda`, `id_administrador`, `id_comprador`, `cliente_nome`, `valortotal`, `datavenda`, `numrecib`, `formapag`) VALUES
(2, 1, 5, 'Viaod', 12, '2026-08-18', 918367, 'Dinheiro'),
(3, 1, 7, 'lena', 13.078, '2026-08-25', 9495113, 'Dinheiro'),
(4, 1, 7, 'lena', 248, '2026-09-01', 4379337, 'Dinheiro'),
(5, 1, NULL, 'PInto mole', 10001.5, '2026-09-01', 1334664, 'Dinheiro'),
(6, 1, 7, 'lena', 12.911999999999999, '2026-09-01', 3067844, 'Dinheiro'),
(7, 1, NULL, 'Teste1', 49.6, '2026-09-08', 3906772, 'Pix'),
(8, 1, NULL, 'PInto mole', 12000, '2026-09-08', 8149012, 'Boleto'),
(9, 1, 7, 'lena', 1017, '2026-09-14', 7713560, 'Transferência'),
(10, 1, 7, 'lena', 13.038999999999998, '2026-09-15', 5397088, 'Dinheiro');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`id_administrador`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `comprador`
--
ALTER TABLE `comprador`
  ADD PRIMARY KEY (`id_comprador`),
  ADD UNIQUE KEY `cpf` (`cpf`);

--
-- Índices de tabela `fruta`
--
ALTER TABLE `fruta`
  ADD PRIMARY KEY (`id_fruta`),
  ADD UNIQUE KEY `nome_unique` (`nome`);

--
-- Índices de tabela `itemvenda`
--
ALTER TABLE `itemvenda`
  ADD PRIMARY KEY (`id_itemvenda`),
  ADD KEY `id_venda` (`id_venda`),
  ADD KEY `id_fruta` (`id_fruta`);

--
-- Índices de tabela `venda`
--
ALTER TABLE `venda`
  ADD PRIMARY KEY (`id_venda`),
  ADD UNIQUE KEY `numrecib` (`numrecib`),
  ADD KEY `id_administrador` (`id_administrador`),
  ADD KEY `id_comprador` (`id_comprador`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `administrador`
--
ALTER TABLE `administrador`
  MODIFY `id_administrador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `comprador`
--
ALTER TABLE `comprador`
  MODIFY `id_comprador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `fruta`
--
ALTER TABLE `fruta`
  MODIFY `id_fruta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `itemvenda`
--
ALTER TABLE `itemvenda`
  MODIFY `id_itemvenda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `venda`
--
ALTER TABLE `venda`
  MODIFY `id_venda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `itemvenda`
--
ALTER TABLE `itemvenda`
  ADD CONSTRAINT `itemvenda_fk_fruta` FOREIGN KEY (`id_fruta`) REFERENCES `fruta` (`id_fruta`) ON DELETE SET NULL,
  ADD CONSTRAINT `itemvenda_fk_venda` FOREIGN KEY (`id_venda`) REFERENCES `venda` (`id_venda`) ON DELETE CASCADE;

--
-- Restrições para tabelas `venda`
--
ALTER TABLE `venda`
  ADD CONSTRAINT `venda_fk_administrador` FOREIGN KEY (`id_administrador`) REFERENCES `administrador` (`id_administrador`),
  ADD CONSTRAINT `venda_fk_comprador` FOREIGN KEY (`id_comprador`) REFERENCES `comprador` (`id_comprador`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
