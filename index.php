<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punto de Venta - Hidromáticos Alexis Navas</title>
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
            <a href="index.php" class="nav-link active">🛒 Punto de Venta</a>
            <a href="inventario.php" class="nav-link">📦 Catálogo de Repuestos</a>
        </nav>
    </header>

    <main class="container">
        <div class="pos-grid">
            
            <!-- COLUMNA IZQUIERDA: BÚSQUEDA Y DETALLE DE LA VENTA -->
            <section>
                <!-- Búsqueda rápida por código -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">🔍 Búsqueda de Repuestos por Código</h2>
                        <span id="codigoFeedback" style="display:none;"></span>
                    </div>
                    <div class="barcode-box">
                        <input type="text" 
                               id="inputCodigo" 
                               class="form-control" 
                               placeholder="Ingrese o escanee el código del repuesto (Ej: KIT-4L60E, SOL-A340E)..." 
                               autofocus 
                               autocomplete="off">
                        <button id="btnBuscarCodigo" class="btn btn-primary">
                            <span>➕</span> Agregar a la Orden
                        </button>
                    </div>
                </div>

                <!-- Tabla interactiva de detalle de la venta -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">📋 Detalle de la Venta en Curso</h2>
                        <small style="color: var(--text-muted)">Las cantidades actualizan el total automáticamente</small>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Descripción / Repuesto</th>
                                    <th style="text-align: center;">Cantidad</th>
                                    <th>P. Unitario</th>
                                    <th>Subtotal</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="tablaDetalleVenta">
                                <!-- Filas dinámicas generadas por JS -->
                            </tbody>
                        </table>

                        <div id="emptyStateVenta" class="empty-state">
                            <span>🛒</span>
                            <p>No hay repuestos agregados a esta venta todavía.<br>Escriba un código de pieza arriba para comenzar.</p>
                        </div>
                    </div>
                </div>

                <!-- Historial reciente -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">🕒 Últimas Ventas Procesadas</h2>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>N° Venta</th>
                                    <th>Fecha y Hora</th>
                                    <th>Cliente</th>
                                    <th>Método Pago</th>
                                    <th>Total Cobrado</th>
                                </tr>
                            </thead>
                            <tbody id="tablaUltimasVentas">
                                <tr>
                                    <td colspan="5" style="text-align:center;">Cargando historial...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- COLUMNA DERECHA: CONFIGURACIÓN Y COBRO -->
            <aside>
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">👤 Datos del Cliente</h2>
                        <button id="btnNuevoCliente" class="btn btn-secondary btn-sm">+ Nuevo</button>
                    </div>

                    <div class="form-group">
                        <label for="selectCliente">Cliente Titular:</label>
                        <select id="selectCliente" class="form-control">
                            <option value="">Cargando clientes...</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="selectTipoComprobante">Tipo de Comprobante:</label>
                        <select id="selectTipoComprobante" class="form-control">
                            <option value="Factura">Factura</option>
                            <option value="Nota de Entrega" selected>Nota de Entrega</option>
                            <option value="Comprobante de Caja">Comprobante de Caja</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="selectMetodoPago">Método de Pago:</label>
                        <select id="selectMetodoPago" class="form-control">
                            <option value="Divisa (Efectivo USD)">Divisa (Efectivo USD)</option>
                            <option value="Pago Móvil">Pago Móvil</option>
                            <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                            <option value="Punto de Venta / Débito">Punto de Venta / Débito</option>
                        </select>
                    </div>

                    <!-- Resumen del Total -->
                    <div class="order-summary">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span id="totalSubtotal">$0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Impuesto / Retención:</span>
                            <span>$0.00</span>
                        </div>
                        <div class="summary-row summary-total">
                            <span>Total a Pagar:</span>
                            <span id="totalVenta">$0.00</span>
                        </div>
                    </div>

                    <div style="margin-top: 1.5rem;">
                        <button id="btnProcesarVenta" class="btn btn-success btn-block" style="padding: 1rem; font-size: 1.05rem;" disabled>
                            💳 Registrar y Cobrar Venta
                        </button>
                    </div>
                </div>
            </aside>

        </div>
    </main>

    <!-- MODAL: REGISTRAR NUEVO CLIENTE RÁPIDO -->
    <div id="modalCliente" class="modal-overlay">
        <div class="modal-content">
            <div class="card-header">
                <h3 class="card-title">Registrar Nuevo Cliente</h3>
                <button class="btn btn-secondary btn-sm" onclick="cerrarModal('modalCliente')">✕</button>
            </div>
            <form id="formNuevoCliente">
                <div class="form-group">
                    <label>Cédula o RIF:</label>
                    <input type="text" name="cedula" class="form-control" required placeholder="Ej: V-12345678 o J-12345678-0">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div class="form-group">
                        <label>Nombre:</label>
                        <input type="text" name="nombre" class="form-control" required placeholder="Nombre">
                    </div>
                    <div class="form-group">
                        <label>Apellido:</label>
                        <input type="text" name="apellido" class="form-control" required placeholder="Apellido">
                    </div>
                </div>
                <div class="form-group">
                    <label>Teléfono:</label>
                    <input type="text" name="telefono" class="form-control" placeholder="0414-0000000">
                </div>
                <div class="form-group">
                    <label>Dirección:</label>
                    <input type="text" name="direccion" class="form-control" placeholder="Dirección">
                </div>
                <div class="form-group">
                    <label>Correo Electrónico:</label>
                    <input type="email" name="correo" class="form-control" placeholder="cliente@correo.com">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal('modalCliente')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: RECIBO / COMPROBANTE DE VENTA -->
    <div id="modalRecibo" class="modal-overlay">
        <div class="modal-content">
            <div id="contenidoRecibo"></div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 1.5rem;">
                <button class="btn btn-secondary" onclick="window.print()">🖨️ Imprimir Comprobante</button>
                <button class="btn btn-primary" onclick="cerrarModal('modalRecibo')">Aceptar y Continuar</button>
            </div>
        </div>
    </div>

    <script src="assets/js/pos.js"></script>
</body>
</html>
