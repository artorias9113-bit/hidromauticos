-- Datos iniciales de prueba para Hidromáticos Alexis Navas
USE hidromaticos_db;

-- Clientes de ejemplo
INSERT INTO cliente (nombre, apellido, cedula, telefono, direccion, correo) VALUES
('Carlos', 'Mendoza', 'V-18456123', '0414-1234567', 'Av. Intercomunal, Maracay', 'carlos.m@gmail.com'),
('María', 'Fernández', 'V-15789456', '0424-9876543', 'Urb. La Soledad, Maracay', 'maria.f@gmail.com'),
('Taller Mecánico', 'Los Samanes C.A.', 'J-31245678-0', '0243-2345678', 'Zona Industrial San Vicente', 'contacto@tallerlossamanes.com')
ON DUPLICATE KEY UPDATE cedula=cedula;

-- Proveedores de ejemplo
INSERT INTO proveedor (nombre, rif, telefono, direccion, correo, contacto) VALUES
('Transmisiones y Partes Venezuela C.A.', 'J-29876543-1', '0241-8765432', 'Valencia, Carabobo', 'ventas@transmisionesve.com', 'Ing. Roberto Silva'),
('Importadora Hidromática Oriental', 'J-40123987-5', '0281-2891122', 'Barcelona, Anzoátegui', 'pedidos@hidro-oriental.com', 'Lic. Alejandro Gómez')
ON DUPLICATE KEY UPDATE rif=rif;

-- Repuestos de transmisiones automáticas e hidráulicas
INSERT INTO repuesto (codigo, nombre, marca, condicion, compatibilidad, ubicacion, stock_actual, precio_venta, precio_costo) VALUES
('KIT-4L60E', 'Master Kit de Reconstrucción de Transmisión 4L60E', 'Transtec', 'Nuevo', 'Chevrolet Silverado, Tahoe, Trailblazer', 'Estante A-1', 12, 145.00, 95.00),
('SOL-A340E', 'Kit de Solenoides de Cambio Transmisión A340E/F', 'Rostra', 'Nuevo', 'Toyota 4Runner, Hilux, Tacoma', 'Estante B-3', 8, 85.00, 52.00),
('TURB-TH400', 'Convertidor de Torque (Turbina) Reforzada TH400', 'Dacco', 'Reconstruido', 'GM Heavy Duty, C10, C30', 'Piso Central', 4, 180.00, 110.00),
('FLUID-DEX6', 'Aceite Hidráulico / Fluido Transmisión Dexron VI (Cuarto)', 'ACDelco', 'Nuevo', 'Universal GM / Especificación Dexron VI', 'Estante D-2', 48, 12.50, 7.80),
('FILT-6F35', 'Filtro Interno para Caja 6F35', 'Motorcraft', 'Nuevo', 'Ford Fusion, Escape, Explorer 2011+', 'Estante C-1', 15, 28.00, 16.50),
('BOMB-4R70W', 'Bomba de Aceite de Transmisión Automática 4R70W', 'Ford OEM', 'Usado', 'Ford F-150, Grand Marquis', 'Estante E-4', 3, 95.00, 50.00)
ON DUPLICATE KEY UPDATE codigo=codigo;
