-- --------------------------------------------------------
-- Base de datos: `enigmatool`
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `enigmatool`;
USE `enigmatool`;

-- --------------------------------------------------------
-- Tabla `usuarios`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `usuario` VARCHAR(50) NOT NULL,
  `contrasena` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `usuario_UNIQUE` (`usuario`)
) ENGINE = InnoDB;

-- Insertar usuario admin (contraseña: admin)
INSERT INTO `usuarios` (`usuario`, `contrasena`) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- --------------------------------------------------------
-- Tabla `receptores`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `receptores` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre_completo` VARCHAR(100) NOT NULL,
  `departamento` VARCHAR(50) NOT NULL,
  `cargo` VARCHAR(50) NOT NULL,
  `fecha_recibido` DATE NOT NULL,
  `foto` VARCHAR(255) NULL,
  `pdf_aceptacion` VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB;

-- --------------------------------------------------------
-- Tabla `equipos`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `equipos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `tipo` ENUM('computadora', 'celular', 'impresora') NOT NULL,
  `marca` VARCHAR(50) NOT NULL,
  `modelo` VARCHAR(50) NOT NULL,
  `numero_serie` VARCHAR(50) NOT NULL,
  `numero_express` VARCHAR(50) NULL COMMENT 'Solo para computadoras',
  `numero_telefono` VARCHAR(20) NULL COMMENT 'Solo para celulares',
  `imei` VARCHAR(20) NULL COMMENT 'Solo para celulares',
  `costo` DECIMAL(10,2) NOT NULL,
  `fotos` TEXT NULL COMMENT 'Rutas de imágenes (máx 4)',
  `receptor_id` INT NOT NULL,
  `fecha_entrega` DATE NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `numero_serie_UNIQUE` (`numero_serie`),
  INDEX `fk_equipos_receptores_idx` (`receptor_id`),
  CONSTRAINT `fk_equipos_receptores`
    FOREIGN KEY (`receptor_id`)
    REFERENCES `receptores` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE = InnoDB;
