-- ==============================================================================
-- BASE DE DATOS COMPLETA: SISTEMA DE INFORMACIÓN Y GESTIÓN COMERCIAL
-- Empresa: Hidromáticos y Repuestos Alexis Navas 2016 C.A.
-- RIF: J-40797625-7
-- Motor: MySQL / MariaDB (InnoDB con integridad referencial)
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP DATABASE IF EXISTS hidromaticos_db;
CREATE DATABASE hidromaticos_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hidromaticos_db;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------------------------
-- 1. TABLA: CLIENTE
-- ------------------------------------------------------------------------------
CREATE TABLE cliente (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL,
    apellido VARCHAR(60) NOT NULL,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    telefono VARCHAR(25) NULL,
    direccion VARCHAR(255) NULL,
    correo VARCHAR(100) NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2. TABLA: PROVEEDOR
-- ------------------------------------------------------------------------------
CREATE TABLE proveedor (
    id_proveedor INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    rif VARCHAR(20) NOT NULL UNIQUE,
    telefono VARCHAR(25) NULL,
    direccion VARCHAR(255) NULL,
    correo VARCHAR(100) NULL,
    contacto VARCHAR(100) NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 3. TABLA: REPUESTO (Catálogo de piezas e inventario)
-- ------------------------------------------------------------------------------
CREATE TABLE repuesto (
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

-- ------------------------------------------------------------------------------
-- 4. TABLA: VENTA (Cabecera)
-- ------------------------------------------------------------------------------
CREATE TABLE venta (
    id_venta INT AUTO_INCREMENT PRIMARY KEY,
    fecha_venta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo_comprobante VARCHAR(50) NOT NULL,
    metodo_pago VARCHAR(50) NOT NULL,
    total_venta DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    id_cliente INT NOT NULL,
    CONSTRAINT fk_venta_cliente
        FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 5. TABLA: DETALLE_VENTA (Renglones vendidos)
-- ------------------------------------------------------------------------------
CREATE TABLE detalle_venta (
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

-- ------------------------------------------------------------------------------
-- 6. TABLA: COMPRA (Cabecera)
-- ------------------------------------------------------------------------------
CREATE TABLE compra (
    id_compra INT AUTO_INCREMENT PRIMARY KEY,
    fecha_compra DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo_comprobante VARCHAR(50) NOT NULL,
    numero_comprobante VARCHAR(50) NULL,
    metodo_pago VARCHAR(50) NOT NULL,
    total_compra DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    id_proveedor INT NOT NULL,
    CONSTRAINT fk_compra_proveedor
        FOREIGN KEY (id_proveedor) REFERENCES proveedor(id_proveedor)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 7. TABLA: DETALLE_COMPRA (Renglones comprados)
-- ------------------------------------------------------------------------------
CREATE TABLE detalle_compra (
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

-- Índices de alto rendimiento
CREATE INDEX idx_repuesto_codigo ON repuesto(codigo);
CREATE INDEX idx_cliente_cedula ON cliente(cedula);
CREATE INDEX idx_proveedor_rif ON proveedor(rif);

-- ==============================================================================
-- DATOS INICIALES DE PRUEBA (CATÁLOGO REAL PARA TRANSMISIONES AUTOMÁTICAS)
-- ==============================================================================

-- Clientes
INSERT INTO cliente (id_cliente, nombre, apellido, cedula, telefono, direccion, correo) VALUES
(1, 'Carlos', 'Mendoza', 'V-18456123', '0414-1234567', 'Av. Intercomunal, Maracay', 'carlos.m@gmail.com'),
(2, 'María', 'Fernández', 'V-15789456', '0424-9876543', 'Urb. La Soledad, Maracay', 'maria.f@gmail.com'),
(3, 'Taller Mecánico', 'Los Samanes C.A.', 'J-31245678-0', '0243-2345678', 'Zona Industrial San Vicente', 'contacto@tallerlossamanes.com');

-- Proveedores
INSERT INTO proveedor (id_proveedor, nombre, rif, telefono, direccion, correo, contacto) VALUES
(1, 'Transmisiones y Partes Venezuela C.A.', 'J-29876543-1', '0241-8765432', 'Valencia, Carabobo', 'ventas@transmisionesve.com', 'Ing. Roberto Silva'),
(2, 'Importadora Hidromática Oriental', 'J-40123987-5', '0281-2891122', 'Barcelona, Anzoátegui', 'pedidos@hidro-oriental.com', 'Lic. Alejandro Gómez');

-- Repuestos
INSERT INTO repuesto (id_repuesto, codigo, nombre, marca, condicion, compatibilidad, ubicacion, stock_actual, precio_venta, precio_costo) VALUES
(1, 'KIT-4L60E', 'Master Kit de Reconstrucción de Transmisión 4L60E', 'Transtec', 'Nuevo', 'Chevrolet Silverado, Tahoe, Trailblazer', 'Estante A-1', 12, 145.00, 95.00),
(2, 'SOL-A340E', 'Kit de Solenoides de Cambio Transmisión A340E/F', 'Rostra', 'Nuevo', 'Toyota 4Runner, Hilux, Tacoma', 'Estante B-3', 8, 85.00, 52.00),
(3, 'TURB-TH400', 'Convertidor de Torque (Turbina) Reforzada TH400', 'Dacco', 'Reconstruido', 'GM Heavy Duty, C10, C30', 'Piso Central', 4, 180.00, 110.00),
(4, 'FLUID-DEX6', 'Aceite Hidráulico / Fluido Transmisión Dexron VI (Cuarto)', 'ACDelco', 'Nuevo', 'Universal GM / Especificación Dexron VI', 'Estante D-2', 48, 12.50, 7.80),
(5, 'FILT-6F35', 'Filtro Interno para Caja 6F35', 'Motorcraft', 'Nuevo', 'Ford Fusion, Escape, Explorer 2011+', 'Estante C-1', 15, 28.00, 16.50),
(6, 'BOMB-4R70W', 'Bomba de Aceite de Transmisión Automática 4R70W', 'Ford OEM', 'Usado', 'Ford F-150, Grand Marquis', 'Estante E-4', 3, 95.00, 50.00);
