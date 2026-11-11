-- ==============================================================================
-- Sistema de Información para el Control de Inventario y Gestión Comercial
-- Empresa: Hidromáticos Alexis Navas
-- Base de Datos: MySQL / MariaDB (Motor InnoDB con soporte para transacciones y FK)
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS hidromaticos_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE hidromaticos_db;

-- 1. TABLA: CLIENTE
CREATE TABLE IF NOT EXISTS cliente (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL,
    apellido VARCHAR(60) NOT NULL,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    telefono VARCHAR(25) NULL,
    direccion VARCHAR(255) NULL,
    correo VARCHAR(100) NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABLA: PROVEEDOR
CREATE TABLE IF NOT EXISTS proveedor (
    id_proveedor INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    rif VARCHAR(20) NOT NULL UNIQUE,
    telefono VARCHAR(25) NULL,
    direccion VARCHAR(255) NULL,
    correo VARCHAR(100) NULL,
    contacto VARCHAR(100) NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABLA: REPUESTO
CREATE TABLE IF NOT EXISTS repuesto (
    id_repuesto INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    marca VARCHAR(60) NULL,
    condicion ENUM('Nuevo', 'Usado', 'Reconstruido') NOT NULL DEFAULT 'Nuevo',
    compatibilidad TEXT NULL,
    ubicacion VARCHAR(60) NULL,
    stock_actual INT NOT NULL DEFAULT 0,
    precio_venta DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    precio_costo DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABLA: VENTA
CREATE TABLE IF NOT EXISTS venta (
    id_venta INT AUTO_INCREMENT PRIMARY KEY,
    fecha_venta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo_comprobante VARCHAR(50) NOT NULL, -- e.g., 'Factura', 'Nota de Entrega'
    metodo_pago VARCHAR(50) NOT NULL,      -- e.g., 'Efectivo', 'Transferencia', 'Pago Móvil', 'Divisa'
    total_venta DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    id_cliente INT NOT NULL,
    CONSTRAINT fk_venta_cliente
        FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TABLA: DETALLE_VENTA
CREATE TABLE IF NOT EXISTS detalle_venta (
    id_detalle_venta INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_repuesto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(12, 2) NOT NULL,
    subtotal DECIMAL(12, 2) NOT NULL,
    CONSTRAINT fk_detalle_venta_venta
        FOREIGN KEY (id_venta) REFERENCES venta(id_venta)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_detalle_venta_repuesto
        FOREIGN KEY (id_repuesto) REFERENCES repuesto(id_repuesto)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. TABLA: COMPRA
CREATE TABLE IF NOT EXISTS compra (
    id_compra INT AUTO_INCREMENT PRIMARY KEY,
    fecha_compra DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo_comprobante VARCHAR(50) NOT NULL, -- e.g., 'Factura Proveedor', 'Nota de Recepción'
    numero_comprobante VARCHAR(50) NULL,
    metodo_pago VARCHAR(50) NOT NULL,
    total_compra DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    id_proveedor INT NOT NULL,
    CONSTRAINT fk_compra_proveedor
        FOREIGN KEY (id_proveedor) REFERENCES proveedor(id_proveedor)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. TABLA: DETALLE_COMPRA
CREATE TABLE IF NOT EXISTS detalle_compra (
    id_detalle_compra INT AUTO_INCREMENT PRIMARY KEY,
    id_compra INT NOT NULL,
    id_repuesto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(12, 2) NOT NULL,
    subtotal DECIMAL(12, 2) NOT NULL,
    CONSTRAINT fk_detalle_compra_compra
        FOREIGN KEY (id_compra) REFERENCES compra(id_compra)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_detalle_compra_repuesto
        FOREIGN KEY (id_repuesto) REFERENCES repuesto(id_repuesto)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices adicionales para optimizar búsquedas frecuentes
CREATE INDEX idx_repuesto_codigo ON repuesto(codigo);
CREATE INDEX idx_cliente_cedula ON cliente(cedula);
CREATE INDEX idx_proveedor_rif ON proveedor(rif);
