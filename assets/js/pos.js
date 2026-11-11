/**
 * ==============================================================================
 * Sistema de Información para Hidromáticos Alexis Navas
 * Módulo: Lógica Frontend del Punto de Venta (POS)
 * ==============================================================================
 */

// Estado de la venta actual (items agregados)
let ventaItems = [];

document.addEventListener('DOMContentLoaded', () => {
    cargarClientes();
    cargarUltimasVentas();

    // Eventos de búsqueda por código
    const btnBuscar = document.getElementById('btnBuscarCodigo');
    const inputCodigo = document.getElementById('inputCodigo');

    if (btnBuscar && inputCodigo) {
        btnBuscar.addEventListener('click', () => buscarPorCodigo(inputCodigo.value));
        inputCodigo.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarPorCodigo(inputCodigo.value);
            }
        });
    }

    // Evento de registrar venta
    const btnProcesar = document.getElementById('btnProcesarVenta');
    if (btnProcesar) {
        btnProcesar.addEventListener('click', procesarVenta);
    }

    // Modal nuevo cliente
    const btnNuevoCliente = document.getElementById('btnNuevoCliente');
    if (btnNuevoCliente) {
        btnNuevoCliente.addEventListener('click', () => {
            document.getElementById('modalCliente').classList.add('active');
        });
    }

    const formCliente = document.getElementById('formNuevoCliente');
    if (formCliente) {
        formCliente.addEventListener('submit', guardarNuevoCliente);
    }
});

/**
 * Carga la lista de clientes desde la API
 */
async function cargarClientes() {
    try {
        const res = await fetch('clientes/api_clientes.php?accion=listar');
        const json = await res.json();
        const select = document.getElementById('selectCliente');
        
        if (json.success && select) {
            select.innerHTML = '<option value="">-- Seleccione Cliente --</option>';
            json.data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id_cliente;
                opt.textContent = `${c.nombre} ${c.apellido} (${c.cedula})`;
                select.appendChild(opt);
            });
        }
    } catch (err) {
        console.error('Error al cargar clientes:', err);
    }
}

/**
 * Busca un repuesto por código y lo añade directamente al detalle de la venta
 */
async function buscarPorCodigo(codigo) {
    const inputCodigo = document.getElementById('inputCodigo');
    const feedback = document.getElementById('codigoFeedback');

    if (!codigo || codigo.trim() === '') {
        mostrarFeedback('Por favor ingrese un código de repuesto.', 'danger');
        return;
    }

    mostrarFeedback('Consultando catálogo...', 'info');

    try {
        const res = await fetch(`inventario/api_repuestos.php?accion=buscar_codigo&codigo=${encodeURIComponent(codigo.trim())}`);
        const data = await res.json();

        if (!data.success) {
            mostrarFeedback(data.mensaje, 'danger');
            return;
        }

        const repuesto = data.data;

        if (parseInt(repuesto.stock_actual) <= 0) {
            mostrarFeedback(`El repuesto "${repuesto.nombre}" no tiene stock disponible (0 unidades).`, 'warning');
            return;
        }

        agregarRepuestoAVenta(repuesto);
        mostrarFeedback(`"${repuesto.nombre}" agregado a la orden.`, 'success');

        // Limpiar input y reenfocar
        inputCodigo.value = '';
        inputCodigo.focus();

    } catch (err) {
        console.error('Error en búsqueda:', err);
        mostrarFeedback('Error de comunicación con el servidor.', 'danger');
    }
}

/**
 * Agrega un repuesto al listado o incrementa su cantidad si ya fue agregado
 */
function agregarRepuestoAVenta(repuesto) {
    const id = parseInt(repuesto.id_repuesto);
    const existente = ventaItems.find(item => item.id_repuesto === id);

    if (existente) {
        if (existente.cantidad + 1 > parseInt(repuesto.stock_actual)) {
            alert(`No puedes agregar más unidades. Stock máximo disponible: ${repuesto.stock_actual}`);
            return;
        }
        existente.cantidad += 1;
        existente.subtotal = +(existente.cantidad * existente.precio_unitario).toFixed(2);
    } else {
        const precio = parseFloat(repuesto.precio_venta);
        ventaItems.push({
            id_repuesto: id,
            codigo: repuesto.codigo,
            nombre: repuesto.nombre,
            marca: repuesto.marca || 'N/A',
            stock_max: parseInt(repuesto.stock_actual),
            precio_unitario: precio,
            cantidad: 1,
            subtotal: precio
        });
    }

    renderizarTablaVenta();
}

/**
 * Renderiza la tabla HTML de los renglones de venta y calcula el total
 */
function renderizarTablaVenta() {
    const tbody = document.getElementById('tablaDetalleVenta');
    const emptyState = document.getElementById('emptyStateVenta');
    const btnProcesar = document.getElementById('btnProcesarVenta');

    if (ventaItems.length === 0) {
        tbody.innerHTML = '';
        if (emptyState) emptyState.style.display = 'block';
        if (btnProcesar) btnProcesar.disabled = true;
        actualizarTotales(0);
        return;
    }

    if (emptyState) emptyState.style.display = 'none';
    if (btnProcesar) btnProcesar.disabled = false;

    let html = '';
    let totalGeneral = 0;

    ventaItems.forEach((item, index) => {
        totalGeneral += item.subtotal;
        html += `
            <tr>
                <td><strong>${item.codigo}</strong></td>
                <td>
                    <div>${item.nombre}</div>
                    <small style="color: var(--text-muted)">Marca: ${item.marca} | Disp: ${item.stock_max}</small>
                </td>
                <td style="width: 110px;">
                    <input type="number" 
                           class="form-control" 
                           style="padding: 4px 8px; text-align: center;" 
                           min="1" 
                           max="${item.stock_max}" 
                           value="${item.cantidad}" 
                           onchange="cambiarCantidad(${index}, this.value)">
                </td>
                <td>$${item.precio_unitario.toFixed(2)}</td>
                <td><strong>$${item.subtotal.toFixed(2)}</strong></td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="eliminarRenglon(${index})" title="Eliminar renglón">
                        ✕
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    actualizarTotales(totalGeneral);
}

/**
 * Modifica la cantidad de un producto en la tabla y recalcula subtotales
 */
window.cambiarCantidad = function(index, nuevaCantidad) {
    let cant = parseInt(nuevaCantidad);
    const item = ventaItems[index];

    if (isNaN(cant) || cant <= 0) {
        cant = 1;
    }

    if (cant > item.stock_max) {
        alert(`Stock insuficiente. La existencia máxima es de ${item.stock_max} unidades.`);
        cant = item.stock_max;
    }

    item.cantidad = cant;
    item.subtotal = +(item.cantidad * item.precio_unitario).toFixed(2);
    renderizarTablaVenta();
};

/**
 * Elimina un renglón del detalle de venta
 */
window.eliminarRenglon = function(index) {
    ventaItems.splice(index, 1);
    renderizarTablaVenta();
};

/**
 * Actualiza los elementos visuales del total
 */
function actualizarTotales(total) {
    const elSubtotal = document.getElementById('totalSubtotal');
    const elTotal = document.getElementById('totalVenta');

    if (elSubtotal) elSubtotal.textContent = `$${total.toFixed(2)}`;
    if (elTotal) elTotal.textContent = `$${total.toFixed(2)}`;
}

/**
 * Envía la venta a la API para persistir en BD y restar el stock
 */
async function procesarVenta() {
    const idCliente = document.getElementById('selectCliente').value;
    const tipoComprobante = document.getElementById('selectTipoComprobante').value;
    const metodoPago = document.getElementById('selectMetodoPago').value;
    const btnProcesar = document.getElementById('btnProcesarVenta');

    if (!idCliente) {
        alert('Por favor seleccione un cliente antes de procesar la venta.');
        document.getElementById('selectCliente').focus();
        return;
    }

    if (ventaItems.length === 0) {
        alert('Debe agregar al menos un repuesto a la venta.');
        return;
    }

    const payload = {
        accion: 'registrar',
        id_cliente: parseInt(idCliente),
        tipo_comprobante: tipoComprobante,
        metodo_pago: metodoPago,
        detalles: ventaItems.map(i => ({
            id_repuesto: i.id_repuesto,
            cantidad: i.cantidad,
            precio_unitario: i.precio_unitario
        }))
    };

    btnProcesar.disabled = true;
    btnProcesar.innerHTML = 'Procesando venta...';

    try {
        const res = await fetch('ventas/api_ventas.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const respuesta = await res.json();

        if (respuesta.success) {
            // Mostrar resumen / modal de éxito
            mostrarModalRecibo(respuesta.id_venta);
            // Limpiar venta actual
            ventaItems = [];
            renderizarTablaVenta();
            cargarUltimasVentas();
        } else {
            alert('Error al registrar venta: ' + respuesta.mensaje);
        }

    } catch (err) {
        console.error('Error al procesar venta:', err);
        alert('Ocurrió un error inesperado al procesar la venta.');
    } finally {
        btnProcesar.disabled = false;
        btnProcesar.innerHTML = '💳 Registrar y Cobrar Venta';
    }
}

/**
 * Muestra el recibo con los detalles generados
 */
async function mostrarModalRecibo(idVenta) {
    try {
        const res = await fetch(`ventas/api_ventas.php?accion=obtener&id_venta=${idVenta}`);
        const data = await res.json();

        if (data.success) {
            const v = data.data;
            const contenedor = document.getElementById('contenidoRecibo');
            
            let renglonesHtml = v.detalles.map(d => `
                <tr>
                    <td>${d.codigo} - ${d.repuesto_nombre}</td>
                    <td style="text-align: center;">${d.cantidad}</td>
                    <td style="text-align: right;">$${parseFloat(d.precio_unitario).toFixed(2)}</td>
                    <td style="text-align: right;">$${parseFloat(d.subtotal).toFixed(2)}</td>
                </tr>
            `).join('');

            contenedor.innerHTML = `
                <div style="text-align: center; margin-bottom: 15px; border-bottom: 1px dashed #ccc; padding-bottom: 12px;">
                    <img src="assets/img/logo.png" alt="Logo Alexis Navas" style="height: 65px; margin-bottom: 8px; object-fit: contain;">
                    <h3 style="color: var(--primary); margin-bottom: 2px;">Hidromáticos y Repuestos Alexis Navas 2016 C.A.</h3>
                    <p style="font-size: 0.82rem; font-weight: bold; color: var(--text-muted); margin-bottom: 2px;">RIF: J-40797625-7</p>
                    <p style="font-size: 0.8rem; color: var(--text-muted);">Especialistas en Cajas y Transmisiones Automáticas</p>
                    <p style="font-size: 0.85rem; margin-top: 6px;"><strong>${v.tipo_comprobante} N°:</strong> ${String(v.id_venta).padStart(6, '0')}</p>
                    <p style="font-size: 0.82rem; color: var(--text-muted);">${v.fecha_venta}</p>
                </div>
                <div style="font-size: 0.9rem; margin-bottom: 15px;">
                    <p><strong>Cliente:</strong> ${v.cliente_nombre} ${v.cliente_apellido}</p>
                    <p><strong>Cédula/RIF:</strong> ${v.cedula} | <strong>Pago:</strong> ${v.metodo_pago}</p>
                </div>
                <table class="table" style="font-size: 0.85rem; margin-bottom: 15px;">
                    <thead>
                        <tr>
                            <th>Repuesto</th>
                            <th style="text-align: center;">Cant.</th>
                            <th style="text-align: right;">P. Unit</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${renglonesHtml}
                    </tbody>
                </table>
                <div style="text-align: right; font-size: 1.2rem; font-weight: bold; color: var(--primary);">
                    TOTAL PAGADO: $${parseFloat(v.total_venta).toFixed(2)}
                </div>
            `;

            document.getElementById('modalRecibo').classList.add('active');
        }
    } catch (e) {
        console.error(e);
    }
}

/**
 * Carga las últimas ventas para mostrarlas en el panel inferior
 */
async function cargarUltimasVentas() {
    try {
        const res = await fetch('ventas/api_ventas.php?accion=ultimas&limite=5');
        const json = await res.json();
        const tbody = document.getElementById('tablaUltimasVentas');
        
        if (json.success && tbody) {
            if (json.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color: var(--text-muted);">No hay ventas registradas hoy.</td></tr>';
                return;
            }

            tbody.innerHTML = json.data.map(v => `
                <tr>
                    <td><strong>#${v.id_venta}</strong></td>
                    <td>${v.fecha_venta}</td>
                    <td>${v.cliente} (${v.cedula})</td>
                    <td><span class="badge badge-info">${v.metodo_pago}</span></td>
                    <td><strong>$${parseFloat(v.total_venta).toFixed(2)}</strong></td>
                </tr>
            `).join('');
        }
    } catch (e) {
        console.error(e);
    }
}

/**
 * Registra un nuevo cliente desde el modal
 */
async function guardarNuevoCliente(e) {
    e.preventDefault();
    const form = e.target;
    const datos = {
        nombre: form.nombre.value,
        apellido: form.apellido.value,
        cedula: form.cedula.value,
        telefono: form.telefono.value,
        direccion: form.direccion.value,
        correo: form.correo.value
    };

    try {
        const res = await fetch('clientes/api_clientes.php?accion=registrar', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(datos)
        });
        const resp = await res.json();

        if (resp.success) {
            alert('Cliente registrado con éxito.');
            form.reset();
            cerrarModal('modalCliente');
            await cargarClientes();
            document.getElementById('selectCliente').value = resp.id;
        } else {
            alert(resp.mensaje);
        }
    } catch (err) {
        console.error(err);
        alert('Error al guardar cliente.');
    }
}

function mostrarFeedback(msg, tipo) {
    const el = document.getElementById('codigoFeedback');
    if (!el) return;
    el.textContent = msg;
    el.className = `badge badge-${tipo}`;
    el.style.display = 'inline-block';
}

window.cerrarModal = function(idModal) {
    const modal = document.getElementById(idModal);
    if (modal) modal.classList.remove('active');
};
