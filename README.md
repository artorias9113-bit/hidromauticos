# Sistema de Inventario y Gestión Comercial - Hidromáticos Alexis Navas 2016 C.A.

Sistema web modular para el control de inventario, catálogo de piezas de transmisión automática y punto de venta comercial con actualización dinámica de stock.

**Empresa:** Hidromáticos y Repuestos Alexis Navas 2016 C.A.  
**RIF:** J-40797625-7  
**Tecnologías:** PHP 8+ (PDO), MySQL / MariaDB (InnoDB), JavaScript, CSS3, HTML5.

---

## 🚀 Características Principales

1. **Estructura de Base de Datos Relacional (MySQL / InnoDB):**
   - Tablas: `cliente`, `proveedor`, `repuesto`, `venta`, `detalle_venta`, `compra`, `detalle_compra`.
   - Soporte transaccional ACID e integridad referencial (`FOREIGN KEY`).
2. **Conexión Segura (PDO):**
   - Configuración mediante variables de entorno (`getenv`) y manejo seguro de excepciones con `try/catch`.
   - Prepared statements nativos (`PDO::ATTR_EMULATE_PREPARES => false`) para mitigar inyecciones SQL.
3. **Módulo de Inventario (CRUD):**
   - Registro, edición, eliminación y consulta en tiempo real de repuestos.
   - Indicadores visuales de existencia (semáforo de stock).
4. **Punto de Venta (POS) y Stock Dinámico:**
   - Búsqueda inmediata de piezas por código de repuesto.
   - Cálculo automático de subtotales y total en tiempo real.
   - Transacciones atómicas: al registrar una venta se insertan cabecera y detalles, y se descuenta automáticamente el stock mediante `UPDATE`.
   - Generación de comprobante con logotipo oficial listo para imprimir.

---

## 🛠️ Instalación y Puesta en Marcha

### Opción 1: Ejecución Automática (XAMPP en Windows)
1. Clona el repositorio:
   ```bash
   git clone https://github.com/artorias9113-bit/hidromauticos.git
   cd hidromauticos
   ```
2. Ejecuta el archivo de inicio automático:
   - Haz doble clic en `iniciar_servidor.bat`.
   - El script verificará MySQL, creará la base de datos si no existe e iniciará el servidor en `http://localhost:8000`.

### Opción 2: Con phpMyAdmin
1. Coloca la carpeta en `htdocs` de XAMPP (ejemplo: `C:\xampp\htdocs\hidromauticos`).
2. Entra a `http://localhost/phpmyadmin` e importa el archivo `hidromaticos_db_completo.sql`.
3. Accede a `http://localhost/hidromauticos/index.php`.

---

## 📂 Estructura del Proyecto

```text
├── assets/
│   ├── css/style.css            # Estilos del Punto de Venta y Catálogo
│   ├── img/logo.png             # Logotipo oficial de Alexis Navas 2016 C.A.
│   └── js/
│       ├── pos.js               # Lógica del Punto de Venta y cálculos en vivo
│       └── inventario.js        # Lógica CRUD de repuestos
├── clientes/                    # Controlador y API de clientes
├── inventario/                  # Controlador y API de inventario
├── ventas/                      # Controlador y API de ventas y transacciones
├── conexion.php                 # Conexión PDO segura
├── database.sql                 # Script DDL de la base de datos
├── datos_prueba.sql             # Datos de prueba de piezas automotrices
├── hidromaticos_db_completo.sql # Script SQL unificado (DDL + DML)
├── index.php                    # Panel del vendedor / Punto de Venta
├── inventario.php               # Vista de gestión de catálogo
└── LEEME_INSTRUCCIONES.txt      # Instrucciones de uso
```
