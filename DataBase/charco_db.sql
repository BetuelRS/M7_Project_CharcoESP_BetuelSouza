SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

INSERT INTO `sensores` (`cod_sensor`, `nome`, `tipo`, `descricao`, `localizacao`, `modelo`, `fabricante`, `data_instalacao`, `ativo`) VALUES
(1, 'Sensor Temperatura Água', 'Temperatura', 'Mede a temperatura da água do charco', 'Charco - Margem Norte', 'DS18B20', 'Dallas', '2026-02-10', 1),
(2, 'Sensor Humidade Ambiente', 'Humidade', 'Mede a humidade relativa do ar junto ao charco', 'Charco - Estação Ambiental', 'DHT22', 'Adafruit', '2026-02-10', 1),
(3, 'Sensor Luminosidade', 'Luminosidade', 'Mede a intensidade de luz solar no charco', 'Charco - Poste Solar', 'BH1750', 'Generic', '2026-02-10', 1),
(4, 'Sensor Partículas Ar', 'Qualidade do Ar', 'Mede partículas em suspensão no ar', 'Charco - Estação Ambiental', 'SDS011', 'Nova Fitness', '2026-02-12', 1),
(5, 'Sensor Nível da Água', 'Nível da Água', 'Mede o nível da água do charco', 'Charco - Centro', 'HC-SR04', 'Generic', '2026-02-15', 1);

DROP TABLE IF EXISTS `utilizadores`;
CREATE TABLE IF NOT EXISTS `utilizadores` (
  `cod_utilizador` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_completo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ADMIN` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`cod_utilizador`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `utilizadores` (`username`, `password`, `email`, `nome_completo`, `ADMIN`) VALUES
('admin1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin1@charco.pt', 'Administrador Principal', 1),
('admin2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin2@charco.pt', 'Administrador Secundário', 1),
('tecnico1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tecnico1@charco.pt', 'Técnico João Silva', 0),
('tecnico2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tecnico2@charco.pt', 'Técnica Maria Santos', 0),
('utilizador', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'utilizador@charco.pt', 'Utilizador Normal', 0);

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;