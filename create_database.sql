-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS mi_base CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE mi_base;

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255) DEFAULT 'default-product.jpg',
    precio_unitario DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create clientes table
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ci VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    celular VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create cotizaciones table
CREATE TABLE IF NOT EXISTS cotizaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_cotizacion VARCHAR(20) UNIQUE NOT NULL,
    cliente_id INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    validez INT NOT NULL COMMENT 'Días de validez de la cotización',
    observaciones TEXT,
    total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    moneda ENUM('BOB', 'USD') DEFAULT 'BOB',
    tipo_cambio DECIMAL(8, 4) DEFAULT 6.9600,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

-- Create cotizacion_detalles table
CREATE TABLE IF NOT EXISTS cotizacion_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cotizacion_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insert sample products for testing
INSERT INTO products (nombre, descripcion, imagen, precio_unitario) VALUES
('Dimax Pro 500ml', 'Limpiador multiusos concentrado para superficies', 'dimax-pro.jpg', 25.50),
('Dimax Glass 750ml', 'Limpiador especializado para vidrios y cristales', 'dimax-glass.jpg', 18.75),
('Dimax Floor 1L', 'Limpiador para pisos de cerámica y porcelanato', 'dimax-floor.jpg', 32.00),
('Detergente Líquido 2L', 'Detergente concentrado para ropa', 'detergente.jpg', 45.80),
('Desinfectante Multiusos 500ml', 'Desinfectante con acción bactericida', 'desinfectante.jpg', 22.30),
('Jabón Líquido Antibacterial 1L', 'Jabón líquido para manos con acción antibacterial', 'jabon-liquido.jpg', 28.90),
('Limpiador de Baños 750ml', 'Limpiador especializado para sanitarios', 'limpiador-banos.jpg', 19.50),
('Cera para Pisos 1L', 'Cera líquida autobrillante para pisos', 'cera-pisos.jpg', 38.75);

-- Insert sample clients for testing
INSERT INTO clientes (ci, nombre, apellido, celular) VALUES
('12345678', 'Juan Carlos', 'Pérez López', '70123456'),
('87654321', 'María Elena', 'García Morales', '71234567'),
('11223344', 'Roberto', 'Mamani Quispe', '72345678');

-- Create index for better performance
CREATE INDEX idx_products_nombre ON products(nombre);
CREATE INDEX idx_clientes_ci ON clientes(ci);
CREATE INDEX idx_cotizaciones_numero ON cotizaciones(numero_cotizacion);
CREATE INDEX idx_cotizaciones_fecha ON cotizaciones(fecha);
