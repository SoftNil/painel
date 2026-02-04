-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 03/12/2025 às 16:00
-- Versão do servidor: 5.7.44
-- Versão do PHP: 8.4.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `app`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `configuracoes_4`
--

DROP TABLE IF EXISTS `configuracoes_4`;
CREATE TABLE IF NOT EXISTS `configuracoes_4` (
  `id_4` int(11) NOT NULL AUTO_INCREMENT,
  `id_smtp_4` int(11) NOT NULL,
  `titulo_4` varchar(255) NOT NULL,
  `logo_4` varchar(255) NOT NULL,
  `descricao_4` varchar(255) NOT NULL,
  `termos_4` text NOT NULL,
  PRIMARY KEY (`id_4`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Despejando dados para a tabela `configuracoes_4`
--

INSERT INTO `configuracoes_4` (`id_4`, `id_smtp_4`, `titulo_4`, `logo_4`, `descricao_4`, `termos_4`) VALUES
(1, 1, 'SanddyCris', 'logo.png', 'Site de compra e venda de Imóveis', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `controle_login_3`
--

DROP TABLE IF EXISTS `controle_login_3`;
CREATE TABLE IF NOT EXISTS `controle_login_3` (
  `id_3` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario_3` int(11) NOT NULL,
  `ip_local_3` varchar(20) NOT NULL,
  `data_3` date NOT NULL,
  `hora_3` varchar(10) NOT NULL,
  `situacao_3` varchar(255) NOT NULL,
  PRIMARY KEY (`id_3`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Despejando dados para a tabela `controle_login_3`
--

INSERT INTO `controle_login_3` (`id_3`, `id_usuario_3`, `ip_local_3`, `data_3`, `hora_3`, `situacao_3`) VALUES
(1, 2, '::1', '2025-07-08', '09:22:24', 'Logou com sucesso'),
(2, 1, '::1', '2025-07-08', '09:46:55', 'Logou com sucesso');

-- --------------------------------------------------------

--
-- Estrutura para tabela `menu_admin_5`
--

DROP TABLE IF EXISTS `menu_admin_5`;
CREATE TABLE IF NOT EXISTS `menu_admin_5` (
  `id_5` int(11) NOT NULL AUTO_INCREMENT,
  `icone_5` varchar(80) NOT NULL,
  `texto_5` varchar(60) NOT NULL,
  `link_5` varchar(255) NOT NULL,
  PRIMARY KEY (`id_5`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Despejando dados para a tabela `menu_admin_5`
--

INSERT INTO `menu_admin_5` (`id_5`, `icone_5`, `texto_5`, `link_5`) VALUES
(1, '<i class=\"ri-home-2-fill\"></i>', 'Início', 'inicio'),
(2, '<i class=\"ri-facebook-circle-fill\"></i>', 'Facebook', 'https://www.facebook.com/nilton.cleverson.1/'),
(3, '<i class=\"ri-question-fill\"></i>', 'Sobre', 'sobre');

-- --------------------------------------------------------

--
-- Estrutura para tabela `smtp_6`
--

DROP TABLE IF EXISTS `smtp_6`;
CREATE TABLE IF NOT EXISTS `smtp_6` (
  `id_6` int(11) NOT NULL AUTO_INCREMENT,
  `nome_6` varchar(50) NOT NULL,
  `auth_6` tinyint(1) NOT NULL,
  `host_6` varchar(255) NOT NULL,
  `port_6` int(11) NOT NULL,
  `secure_6` varchar(3) NOT NULL,
  `username_6` varchar(255) NOT NULL,
  `password_6` varchar(255) NOT NULL,
  PRIMARY KEY (`id_6`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Despejando dados para a tabela `smtp_6`
--

INSERT INTO `smtp_6` (`id_6`, `nome_6`, `auth_6`, `host_6`, `port_6`, `secure_6`, `username_6`, `password_6`) VALUES
(1, 'zoho', 1, 'smtp.zoho.com', 587, 'tls', 'suporte@softnil.com.br', 'RGVsNDAyMDg2MTAhQCM=');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios_1`
--

DROP TABLE IF EXISTS `usuarios_1`;
CREATE TABLE IF NOT EXISTS `usuarios_1` (
  `id_1` int(11) NOT NULL AUTO_INCREMENT,
  `nome_1` varchar(255) NOT NULL,
  `email_1` varchar(255) NOT NULL,
  `senha_1` varchar(255) NOT NULL,
  `nivel_1` varchar(20) NOT NULL,
  `situacao_1` varchar(30) NOT NULL,
  `codigo_1` varchar(255) NOT NULL,
  `data_cadastro_1` date NOT NULL,
  PRIMARY KEY (`id_1`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Despejando dados para a tabela `usuarios_1`
--

INSERT INTO `usuarios_1` (`id_1`, `nome_1`, `email_1`, `senha_1`, `nivel_1`, `situacao_1`, `codigo_1`, `data_cadastro_1`) VALUES
(1, 'Administrador', 'admin@admin.com', '$2y$12$oiFA0UsS7n5PueHcmYMg1OrbzpKRxyQQXBvreRRO6CQBi5elNEVeW', 'admin', 'ativo', '', '2025-06-23'),
(2, 'Nilton Cleverson de Oliveira', 'joseunionfe@gmail.com', '$2y$10$SjyBwNIlnuDZQq8QLzTg6ev8sdAvkBZj3Jvp2oQrIb6B4zScDUrDm', 'usuarios', 'ativo', '', '2025-07-01');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
