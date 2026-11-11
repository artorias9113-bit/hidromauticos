<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Repuestos - Hidromáticos Alexis Navas</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- BARRA SUPERIOR DE NAVEGACIÓN -->
    <header class="navbar">
        <div class="brand">
            <img src="assets/img/logo.png" alt="Logotipo Alexis Navas" class="brand-logo">
            <div>
                <div class="brand-title">Hidromáticos y Repuestos Alexis Navas 2016 C.A.</div>
                <div class="brand-subtitle">Control de Inventario & Gestión Comercial | RIF: J-40797625-7</div>
            </div>
        </div>
        <nav class="nav-links">
            <a href="index.php" class="nav-link">🛒 Punto de Venta</a>
            <a href="inventario.php" class="nav-link active">📦 Catálogo de Repuestos</a>
        </nav>
    </header>

    <main class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📦 Catálogo y Control de Inventario</h2>
                <button class="btn btn-primary" onclick="abrirModalNuevoRepuesto()">
                    ➕ Registrar Nueva Pieza
                </button>
            </div>

            <!-- FILTRO DE BÚSQUEDA -->
            <div style="margin-bottom: 1.5rem;">
                <input type="text" 
                       id="inputBuscarRepuesto" 
                       class="form-control" 
                       placeholder="Filtrar por código, nombre o marca en tiempo real...">
            </div>

            <!-- TABLA DEL CATÁLOGO -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Descripción / Compatibilidad</th>
                            <th>Marca</th>
                            <th>Condición</th>
                            <th>Ubicación</th>
                            <th>Stock Actual</th>
                            <th>Precio Venta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaRepuestos">
                        <tr>
                            <td colspan="8" style="text-align: center;">Cargando repuestos...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- MODAL: REGISTRAR / EDITAR REPUESTO -->
    <div id="modalRepuesto" class="modal-overlay">
        <div class="modal-content">
            <div class="card-header">
                <h3 id="modalRepuestoTitulo" class="card-title">Registrar Nuevo Repuesto</h3>
                <button class="btn btn-secondary btn-sm" onclick="cerrarModal('modalRepuesto')">✕</button>
            </div>
            
            <form id="formRepuesto">
                <input type="hidden" name="id_repuesto" id="id_repuesto">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div class="form-group">
                        <label>Código de Pieza (*):</label>
                        <input type="text" name="codigo" class="form-control" required placeholder="Ej: KIT-4L60E">
                    </div>
                    <div class="form-group">
                        <label>Marca:</label>
                        <input type="text" name="marca" class="form-control" placeholder="Ej: Transtec, ACDelco">
                    </div>
                </div>

                <div class="form-group">
                    <label>Nombre o Descripción (*):</label>
                    <input type="text" name="nombre" class="form-control" required placeholder="Nombre de la pieza">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div class="form-group">
                        <label>Condición:</label>
                        <select name="condicion" class="form-control">
                            <option value="Nuevo">Nuevo</option>
                            <option value="Usado">Usado</option>
                            <option value="Reconstruido">Reconstruido</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ubicación en Almacén:</label>
                        <input type="text" name="ubicacion" class="form-control" placeholder="Ej: Estante B-2">
                    </div>
                </div>

                <div class="form-group">
                    <label>Compatibilidad de Transmisión / Vehículos:</label>
                    <textarea name="compatibilidad" class="form-control" rows="2" placeholder="Modelos, años o cajas compatibles..."></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                    <div class="form-group">
                        <label>Stock Actual (*):</label>
                        <input type="number" name="stock_actual" class="form-control" min="0" required value="0">
                    </div>
                    <div class="form-group">
                        <label>Precio Costo ($):</label>
                        <input type="number" step="0.01" name="precio_costo" class="form-control" min="0" value="0.00">
                    </div>
                    <div class="form-group">
                        <label>Precio Venta ($) (*):</label>
                        <input type="number" step="0.01" name="precio_venta" class="form-control" min="0" required value="0.00">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal('modalRepuesto')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Repuesto</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/inventario.js"></script>
</body>
</html>
