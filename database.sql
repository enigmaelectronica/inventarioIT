-- Crear base de datos
CREATE DATABASE IF NOT EXISTS enigmatool;
USE enigmatool;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL
);

-- Insertar usuario admin (contraseña: admin)
INSERT INTO usuarios (usuario, contrasena) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- Contraseña: "admin"

-- Tabla de equipos
CREATE TABLE equipos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('computadora', 'celular', 'impresora') NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    numero_serie VARCHAR(50) UNIQUE NOT NULL,
    numero_express VARCHAR(50),
    numero_telefono VARCHAR(20),
    imei VARCHAR(20),
    costo DECIMAL(10,2) NOT NULL,
    fotos TEXT,
    receptor_id INT,
    fecha_entrega DATE
);

-- Tabla de receptores
CREATE TABLE receptores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(100) NOT NULL,
    departamento VARCHAR(50) NOT NULL,
    cargo VARCHAR(50) NOT NULL,
    foto VARCHAR(255),
    pdf_aceptacion VARCHAR(255)
);

-- Datos de ejemplo
INSERT INTO receptores (nombre_completo, departamento, cargo) VALUES
('Juan Pérez', 'TI', 'Desarrollador'),
('María García', 'Recursos Humanos', 'Gerente');

INSERT INTO equipos (tipo, marca, modelo, numero_serie, costo, receptor_id) VALUES
('computadora', 'Dell', 'XPS 15', 'ABC123XYZ', 1500.00, 1),
('celular', 'Samsung', 'Galaxy S23', 'SAM456GXY', 800.00, 2);
