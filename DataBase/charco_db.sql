-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 18-Mar-2026 às 11:26
-- Versão do servidor: 9.1.0
-- versão do PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `charco_db`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `leituras`
--

DROP TABLE IF EXISTS `leituras`;
CREATE TABLE IF NOT EXISTS `leituras` (
  `cod_leituras` int NOT NULL AUTO_INCREMENT,
  `cod_sensor` int NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `unidade` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_hora` datetime NOT NULL,
  `observacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`cod_leituras`),
  KEY `cod_sensor` (`cod_sensor`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `leituras`
--

INSERT INTO `leituras` (`cod_leituras`, `cod_sensor`, `valor`, `unidade`, `data_hora`, `observacoes`) VALUES
(1, 1, 18.40, '°C', '2026-03-06 08:00:00', 'Manhã fresca'),
(2, 1, 19.10, '°C', '2026-03-06 09:00:00', NULL),
(3, 1, 20.30, '°C', '2026-03-06 10:00:00', NULL),
(4, 1, 21.50, '°C', '2026-03-06 11:00:00', NULL),
(5, 1, 22.10, '°C', '2026-03-06 12:00:00', 'Sol forte'),
(6, 1, 215.90, '°C', '2026-03-06 13:00:00', ''),
(7, 2, 82.00, '%', '2026-03-06 08:00:00', NULL),
(8, 2, 79.50, '%', '2026-03-06 09:00:00', NULL),
(9, 2, 75.20, '%', '2026-03-06 10:00:00', NULL),
(10, 2, 72.10, '%', '2026-03-06 11:00:00', NULL),
(11, 2, 70.00, '%', '2026-03-06 12:00:00', 'Ar mais seco'),
(12, 2, 68.40, '%', '2026-03-06 13:00:00', NULL),
(13, 3, 120.00, 'lux', '2026-03-06 08:00:00', 'Céu parcialmente nublado'),
(14, 3, 350.00, 'lux', '2026-03-06 09:00:00', NULL),
(15, 3, 620.00, 'lux', '2026-03-06 10:00:00', NULL),
(16, 3, 850.00, 'lux', '2026-03-06 11:00:00', NULL),
(17, 3, 920.00, 'lux', '2026-03-06 12:00:00', 'Pico de luz'),
(18, 3, 870.00, 'lux', '2026-03-06 13:00:00', NULL),
(19, 4, 12.00, 'µg/m3', '2026-03-06 08:00:00', 'Qualidade do ar boa'),
(20, 4, 14.20, 'µg/m3', '2026-03-06 09:00:00', NULL),
(21, 4, 16.80, 'µg/m3', '2026-03-06 10:00:00', NULL),
(22, 4, 15.40, 'µg/m3', '2026-03-06 11:00:00', NULL),
(23, 4, 18.10, 'µg/m3', '2026-03-06 12:00:00', NULL),
(24, 4, 17.50, 'µg/m3', '2026-03-06 13:00:00', NULL),
(25, 5, 42.00, 'cm', '2026-03-06 08:00:00', 'Após chuva da noite'),
(26, 5, 41.80, 'cm', '2026-03-06 09:00:00', NULL),
(27, 5, 41.50, 'cm', '2026-03-06 10:00:00', NULL),
(28, 5, 41.20, 'cm', '2026-03-06 11:00:00', NULL),
(29, 5, 41.00, 'cm', '2026-03-06 12:00:00', NULL),
(30, 5, 40.80, 'cm', '2026-03-06 13:00:00', 'Evaporação gradual'),
(31, 1, 17.80, '°C', '2026-03-05 08:00:00', NULL),
(32, 1, 18.30, '°C', '2026-03-05 09:00:00', NULL),
(33, 1, 19.60, '°C', '2026-03-05 10:00:00', NULL),
(34, 1, 20.40, '°C', '2026-03-05 11:00:00', NULL),
(35, 1, 21.20, '°C', '2026-03-05 12:00:00', 'Sol forte'),
(36, 1, 21.60, '°C', '2026-03-05 13:00:00', NULL),
(37, 1, 22.00, '°C', '2026-03-05 14:00:00', NULL),
(38, 1, 21.70, '°C', '2026-03-05 15:00:00', NULL),
(39, 1, 21.00, '°C', '2026-03-05 16:00:00', NULL),
(40, 1, 20.10, '°C', '2026-03-05 17:00:00', NULL),
(41, 1, 19.30, '°C', '2026-03-05 18:00:00', NULL),
(42, 1, 18.70, '°C', '2026-03-05 19:00:00', NULL),
(43, 1, 18.10, '°C', '2026-03-05 20:00:00', NULL),
(44, 1, 17.60, '°C', '2026-03-05 21:00:00', NULL),
(45, 1, 17.20, '°C', '2026-03-05 22:00:00', 'Noite fria'),
(46, 1, 17.00, '°C', '2026-03-05 23:00:00', NULL),
(47, 1, 16.90, '°C', '2026-03-06 00:00:00', NULL),
(48, 1, 16.80, '°C', '2026-03-06 01:00:00', NULL),
(49, 1, 16.70, '°C', '2026-03-06 02:00:00', NULL),
(50, 1, 16.90, '°C', '2026-03-06 03:00:00', NULL),
(51, 2, 84.00, '%', '2026-03-05 08:00:00', NULL),
(52, 2, 82.00, '%', '2026-03-05 09:00:00', NULL),
(53, 2, 79.00, '%', '2026-03-05 10:00:00', NULL),
(54, 2, 76.00, '%', '2026-03-05 11:00:00', NULL),
(55, 2, 74.00, '%', '2026-03-05 12:00:00', NULL),
(56, 2, 72.00, '%', '2026-03-05 13:00:00', NULL),
(57, 2, 71.00, '%', '2026-03-05 14:00:00', NULL),
(58, 2, 70.00, '%', '2026-03-05 15:00:00', NULL),
(59, 2, 72.00, '%', '2026-03-05 16:00:00', NULL),
(60, 2, 75.00, '%', '2026-03-05 17:00:00', NULL),
(61, 2, 78.00, '%', '2026-03-05 18:00:00', NULL),
(62, 2, 80.00, '%', '2026-03-05 19:00:00', NULL),
(63, 2, 82.00, '%', '2026-03-05 20:00:00', NULL),
(64, 2, 83.00, '%', '2026-03-05 21:00:00', NULL),
(65, 2, 85.00, '%', '2026-03-05 22:00:00', NULL),
(66, 2, 86.00, '%', '2026-03-05 23:00:00', NULL),
(67, 2, 87.00, '%', '2026-03-06 00:00:00', NULL),
(68, 2, 88.00, '%', '2026-03-06 01:00:00', NULL),
(69, 2, 89.00, '%', '2026-03-06 02:00:00', NULL),
(70, 2, 90.00, '%', '2026-03-06 03:00:00', 'Humidade alta'),
(71, 3, 90.00, 'lux', '2026-03-05 08:00:00', NULL),
(72, 3, 250.00, 'lux', '2026-03-05 09:00:00', NULL),
(73, 3, 520.00, 'lux', '2026-03-05 10:00:00', NULL),
(74, 3, 730.00, 'lux', '2026-03-05 11:00:00', NULL),
(75, 3, 890.00, 'lux', '2026-03-05 12:00:00', 'Pico solar'),
(76, 3, 910.00, 'lux', '2026-03-05 13:00:00', NULL),
(77, 3, 880.00, 'lux', '2026-03-05 14:00:00', NULL),
(78, 3, 760.00, 'lux', '2026-03-05 15:00:00', NULL),
(79, 3, 540.00, 'lux', '2026-03-05 16:00:00', NULL),
(80, 3, 320.00, 'lux', '2026-03-05 17:00:00', NULL),
(81, 3, 120.00, 'lux', '2026-03-05 18:00:00', NULL),
(82, 3, 30.00, 'lux', '2026-03-05 19:00:00', 'Anoitecer'),
(83, 3, 5.00, 'lux', '2026-03-05 20:00:00', NULL),
(84, 3, 1.00, 'lux', '2026-03-05 21:00:00', NULL),
(85, 3, 0.00, 'lux', '2026-03-05 22:00:00', 'Sem luz'),
(86, 3, 0.00, 'lux', '2026-03-05 23:00:00', NULL),
(87, 3, 0.00, 'lux', '2026-03-06 00:00:00', NULL),
(88, 3, 0.00, 'lux', '2026-03-06 01:00:00', NULL),
(89, 3, 0.00, 'lux', '2026-03-06 02:00:00', NULL),
(90, 3, 0.00, 'lux', '2026-03-06 03:00:00', NULL),
(91, 4, 13.00, 'µg/m3', '2026-03-05 08:00:00', NULL),
(92, 4, 14.00, 'µg/m3', '2026-03-05 09:00:00', NULL),
(93, 4, 16.00, 'µg/m3', '2026-03-05 10:00:00', NULL),
(94, 4, 17.00, 'µg/m3', '2026-03-05 11:00:00', NULL),
(95, 4, 18.00, 'µg/m3', '2026-03-05 12:00:00', NULL),
(96, 4, 17.00, 'µg/m3', '2026-03-05 13:00:00', NULL),
(97, 4, 16.00, 'µg/m3', '2026-03-05 14:00:00', NULL),
(98, 4, 15.00, 'µg/m3', '2026-03-05 15:00:00', NULL),
(99, 4, 15.00, 'µg/m3', '2026-03-05 16:00:00', NULL),
(100, 4, 14.00, 'µg/m3', '2026-03-05 17:00:00', NULL),
(101, 4, 14.00, 'µg/m3', '2026-03-05 18:00:00', NULL),
(102, 4, 13.00, 'µg/m3', '2026-03-05 19:00:00', NULL),
(103, 4, 12.00, 'µg/m3', '2026-03-05 20:00:00', NULL),
(104, 4, 12.00, 'µg/m3', '2026-03-05 21:00:00', NULL),
(105, 4, 11.00, 'µg/m3', '2026-03-05 22:00:00', NULL),
(106, 4, 11.00, 'µg/m3', '2026-03-05 23:00:00', NULL),
(107, 4, 10.00, 'µg/m3', '2026-03-06 00:00:00', NULL),
(108, 4, 10.00, 'µg/m3', '2026-03-06 01:00:00', NULL),
(109, 4, 10.00, 'µg/m3', '2026-03-06 02:00:00', NULL),
(110, 4, 11.00, 'µg/m3', '2026-03-06 03:00:00', NULL),
(111, 5, 42.50, 'cm', '2026-03-05 08:00:00', 'Após chuva'),
(112, 5, 42.30, 'cm', '2026-03-05 09:00:00', NULL),
(113, 5, 42.10, 'cm', '2026-03-05 10:00:00', NULL),
(114, 5, 41.90, 'cm', '2026-03-05 11:00:00', NULL),
(115, 5, 41.80, 'cm', '2026-03-05 12:00:00', NULL),
(116, 5, 41.70, 'cm', '2026-03-05 13:00:00', NULL),
(117, 5, 41.60, 'cm', '2026-03-05 14:00:00', NULL),
(118, 5, 41.50, 'cm', '2026-03-05 15:00:00', NULL),
(119, 5, 41.40, 'cm', '2026-03-05 16:00:00', NULL),
(120, 5, 41.30, 'cm', '2026-03-05 17:00:00', NULL),
(121, 5, 41.20, 'cm', '2026-03-05 18:00:00', NULL),
(122, 5, 41.10, 'cm', '2026-03-05 19:00:00', NULL),
(123, 5, 41.00, 'cm', '2026-03-05 20:00:00', NULL),
(124, 5, 40.90, 'cm', '2026-03-05 21:00:00', NULL),
(125, 5, 40.80, 'cm', '2026-03-05 22:00:00', NULL),
(126, 5, 40.70, 'cm', '2026-03-05 23:00:00', NULL),
(127, 5, 40.60, 'cm', '2026-03-06 00:00:00', NULL),
(128, 5, 40.50, 'cm', '2026-03-06 01:00:00', NULL),
(129, 5, 40.40, 'cm', '2026-03-06 02:00:00', NULL),
(130, 5, 40.30, 'cm', '2026-03-06 03:00:00', 'Evaporação gradual');

-- --------------------------------------------------------

--
-- Estrutura da tabela `sensores`
--

DROP TABLE IF EXISTS `sensores`;
CREATE TABLE IF NOT EXISTS `sensores` (
  `cod_sensor` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `localizacao` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modelo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fabricante` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_instalacao` date DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`cod_sensor`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `sensores`
--

INSERT INTO `sensores` (`cod_sensor`, `nome`, `tipo`, `descricao`, `localizacao`, `modelo`, `fabricante`, `data_instalacao`, `ativo`) VALUES
(1, 'Sensor Temperatura Água', 'Temperatura', 'Mede a temperatura da água do charco', 'Charco - Margem Norte', 'DS18B20', 'Dallas', '2026-02-10', 1),
(2, 'Sensor Humidade Ambiente', 'Humidade', 'Mede a humidade relativa do ar junto ao charco', 'Charco - Estação Ambiental', 'DHT22', 'Adafruit', '2026-02-10', 1),
(3, 'Sensor Luminosidade', 'Luminosidade', 'Mede a intensidade de luz solar no charco', 'Charco - Poste Solar', 'BH1750', 'Generic', '2026-02-10', 1),
(4, 'Sensor Partículas Ar', 'Qualidade do Ar', 'Mede partículas em suspensão no ar', 'Charco - Estação Ambiental', 'SDS011', 'Nova Fitness', '2026-02-12', 1),
(5, 'Sensor Nível da Água', 'Nível da Água', 'Mede o nível da água do charco', 'Charco - Centro', 'HC-SR04', 'Generic', '2026-02-15', 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizadores`
--

DROP TABLE IF EXISTS `utilizadores`;
CREATE TABLE IF NOT EXISTS `utilizadores` (
  `cod_utilizador` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_completo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ADMIN` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`cod_utilizador`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `utilizadores`
--

INSERT INTO `utilizadores` (`cod_utilizador`, `username`, `password`, `email`, `nome_completo`, `ADMIN`) VALUES
(1, 'BetuelSouza', '300108', 'aluno@escola.pt', 'Aluno Teste', 1),
(2, 'professor_carlos', '123456', 'carlos@escola.pt', 'Carlos Mendes', 1),
(3, 'aluna_maria', '123456', 'maria@escola.pt', 'Maria Fernandes', 0),
(4, 'aluno_joao', '123456', 'joao@escola.pt', 'João Silva', 0),
(5, 'BetuelRS', 'Betuel.300108', 'betuel801@gmail.com', 'Betuel Rocha de Souza', 0),
(6, 'SHA', 'SHASHA', 'Shalala@gmail.com', 'SHA', 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
