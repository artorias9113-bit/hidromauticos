/**
 * ==============================================================================
 * Sistema de Información para Hidromáticos Alexis Navas
 * Módulo: Lógica Frontend para Gestión de Inventario (CRUD de Repuestos)
 * ==============================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    cargarRepuestos();

    const inputBuscar = document.getElementById('inputBuscarRepuesto');
    if (inputBuscar) {
        inputBuscar.addEventListener('input', (e) => {
            cargarRepuestos(e.target.value);
        });
    }

    const formRepuesto = document.getElementById('formRepuesto');
    if (formRepuesto) {
        formRepuesto.addEventListener('submit', guardarRepuesto);
    }
});

/**
 * Carga el catálogo de repuestos
 */
async function cargarRepuestos(buscar = '') {
    const tbody = document.getElementById('tablaRepuestos');
    if (!tbody) return;

    try {
        const res = await fetch(`inventario/api_repuestos.php?accion=listar&buscar=${encodeURIComponent(buscar)}`);
        const json = await res.json();

        if (json.success) {
            if (json.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" class="empty-state">No se encontraron piezas registradas.</td></tr>`;
                return;
            }

            tbody.innerHTML = json.data.map(r => {
                let badgeStock = 'badge-success';
                if (parseInt(r.stock_actual) <= 0) badgeStock = 'badge-danger';
                else if (parseInt(r.stock_actual) <= 5) badgeStock = 'badge-warning';

                return `
                    <tr>
                        <td><strong>${r.codigo}</strong></td>
                        <td>
                            <strong>${r.nombre}</strong><br>
                            <small style="color: var(--text-muted);">${r.compatibilidad || 'Sin especificación'}</small>
                        </td>
                        <td>${r.marca || 'Genérico'}</td>
                        <td><span class="badge badge-info">${r.condicion}</span></td>
                        <td>${r.ubicacion || 'Sin asignar'}</td>
                        <td><span class="badge ${badgeStock}">${r.stock_actual} unid.</span></td>
                        <td><strong>$${parseFloat(r.precio_venta).toFixed(2)}</strong></td>
                        <td>
                            <div style="display:flex; gap: 6px;">
                                <button class="btn btn-secondary btn-sm" onclick="editarRepuesto(${r.id_repuesto})">✏️</button>
                                <button class="btn btn-danger btn-sm" onclick="eliminarRepuesto(${r.id_repuesto}, '${r.nombre}')">🗑️</button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }
    } catch (err) {
        console.error('Error al cargar repuestos:', err);
    }
}

/**
 * Abre modal para nuevo repuesto
 */
window.abrirModalNuevoRepuesto = function() {
    const form = document.getElementById('formRepuesto');
    if (form) form.reset();
    document.getElementById('id_repuesto').value = '';
    document.getElementById('modalRepuestoTitulo').textContent = 'Registrar Nuevo Repuesto';
    document.getElementById('modalRepuesto').classList.add('active');
};

/**
 * Carga los datos de un repuesto para editarlo
 */
window.editarRepuesto = async function(id) {
    try {
        const res = await fetch(`inventario/api_repuestos.php?accion=obtener&id=${id}`);
        const json = await res.json();

        if (json.success) {
            const r = json.data;
            const form = document.getElementById('formRepuesto');
            
            form.id_repuesto.value     = r.id_repuesto;
            form.codigo.value          = r.codigo;
            form.nombre.value          = r.nombre;
            form.marca.value           = r.marca || '';
            form.condicion.value       = r.condicion;
            form.ubicacion.value       = r.ubicacion || '';
            form.compatibilidad.value  = r.compatibilidad || '';
            form.stock_actual.value    = r.stock_actual;
            form.precio_costo.value    = r.precio_costo;
            form.precio_venta.value    = r.precio_venta;

            document.getElementById('modalRepuestoTitulo').textContent = 'Editar Repuesto: ' + r.codigo;
            document.getElementById('modalRepuesto').classList.add('active');
        } else {
            alert(json.mensaje);
        }
    } catch (err) {
        console.error(err);
        alert('Error al obtener datos del repuesto.');
    }
};

/**
 * Guarda o actualiza repuesto mediante la API
 */
async function guardarRepuesto(e) {
    e.preventDefault();
    const form = e.target;
    const datos = {
        accion: 'guardar',
        id_repuesto: form.id_repuesto.value,
        codigo: form.codigo.value,
        nombre: form.nombre.value,
        marca: form.marca.value,
        condicion: form.condicion.value,
        ubicacion: form.ubicacion.value,
        compatibilidad: form.compatibilidad.value,
        stock_actual: form.stock_actual.value,
        precio_costo: form.precio_costo.value,
        precio_venta: form.precio_venta.value
    };

    try {
        const res = await fetch('inventario/api_repuestos.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(datos)
        });
        const resp = await res.json();

        if (resp.success) {
            alert(resp.mensaje);
            cerrarModal('modalRepuesto');
            cargarRepuestos();
        } else {
            alert(resp.mensaje);
        }
    } catch (err) {
        console.error(err);
        alert('Error de red al guardar repuesto.');
    }
}

/**
 * Elimina un repuesto si no tiene restricciones de clave foránea
 */
window.eliminarRepuesto = async function(id, nombre) {
    if (!confirm(`¿Está seguro de eliminar el repuesto "${nombre}"? Esta acción no se puede deshacer.`)) {
        return;
    }

    try {
        const res = await fetch(`inventario/api_repuestos.php?accion=eliminar&id=${id}`);
        const resp = await res.json();

        alert(resp.mensaje);
        if (resp.success) {
            cargarRepuestos();
        }
    } catch (err) {
        console.error(err);
        alert('Error al intentar eliminar repuesto.');
    }
};

window.cerrarModal = function(idModal) {
    const modal = document.getElementById(idModal);
    if (modal) modal.classList.remove('active');
};
