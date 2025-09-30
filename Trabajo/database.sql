-- Crear base de datos
CREATE DATABASE IF NOT EXISTS tienda_online;
USE tienda_online;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de productos
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    imagen VARCHAR(255),
    stock INT DEFAULT 0,
    usuario_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Insertar productos de ejemplo
INSERT INTO productos (nombre, descripcion, precio, stock, usuario_id) VALUES
('Laptop HP', 'Laptop HP 15.6 pulgadas, 8GB RAM, 256GB SSD', 899.99, 10, 1),
('Mouse Inalámbrico', 'Mouse óptico inalámbrico USB', 25.50, 50, 1),
('Teclado Mecánico', 'Teclado mecánico RGB switches blue', 89.99, 15, 1);