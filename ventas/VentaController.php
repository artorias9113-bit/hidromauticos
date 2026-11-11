<?php
/**
 * ==============================================================================
 * Sistema de Información para el Control de Inventario y Gestión Comercial
 * Empresa: Hidromáticos Alexis Navas
 * Módulo: Controlador de Ventas y Actualización Dinámica de Stock
 * ==============================================================================
 * 
 * Implementa control de transacciones ACID (beginTransaction, commit, rollBack)
 * para garantizar que la venta, sus detalles y el descuento de stock se ejecuten
 * de forma atómica.
 */

require_once __DIR__ . '/../conexion.php';

class VentaController {
    private PDO $db;

    public function __construct() {
        $this->db = getConexion();
    }

    /**
     * Registra una venta completa con sus detalles y descuenta el stock de repuestos
     * de forma atómica y segura.
     * 
     * @param int    $idCliente       ID del cliente que realiza la compra
     * @param string $tipoComprobante Factura, Nota de Entrega, etc.
     * @param string $metodoPago      Efectivo, Pago Móvil, Divisas, etc.
     * @param array  $detalles        Lista de renglones: [['id_repuesto' => int, 'cantidad' => int, 'precio_unitario' => float]]
     * @return array Resultado de la operación
     */
    public function registrarVenta(int $idCliente, string $tipoComprobante, string $metodoPago, array $detalles): array {
        if ($idCliente <= 0) {
            return ['success' => false, 'mensaje' => 'Debe seleccionar un cliente válido.'];
        }

        if (empty($detalles)) {
            return ['success' => false, 'mensaje' => 'La venta debe contener al menos un repuesto.'];
        }

        try {
            // Iniciar Transacción ACID
            $this->db->beginTransaction();

            // 1. Validar existencia del cliente
            $stmtCliente = $this->db->prepare("SELECT id_cliente, nombre, apellido FROM cliente WHERE id_cliente = :id");
            $stmtCliente->execute([':id' => $idCliente]);
            $cliente = $stmtCliente->fetch();
            if (!$cliente) {
                throw new Exception("El cliente seleccionado no existe.");
            }

            // 2. Validar disponibilidad de stock y calcular el total de la venta
            $totalVenta = 0.0;
            $itemsValidados = [];

            // Prepared statement para consultar y bloquear temporalmente la fila (FOR UPDATE)
            $stmtStock = $this->db->prepare("SELECT id_repuesto, codigo, nombre, stock_actual, precio_venta 
                                             FROM repuesto 
                                             WHERE id_repuesto = :id FOR UPDATE");

            foreach ($detalles as $item) {
                $idRepuesto = (int)($item['id_repuesto'] ?? 0);
                $cantidad   = (int)($item['cantidad'] ?? 0);
                $precio     = (float)($item['precio_unitario'] ?? 0.0);

                if ($idRepuesto <= 0 || $cantidad <= 0) {
                    throw new Exception("Existen renglones con cantidad o producto inválido.");
                }

                $stmtStock->execute([':id' => $idRepuesto]);
                $repuesto = $stmtStock->fetch();

                if (!$repuesto) {
                    throw new Exception("El repuesto con ID {$idRepuesto} no existe en el catálogo.");
                }

                // Validación crítica de inventario
                if ($repuesto['stock_actual'] < $cantidad) {
                    throw new Exception("Stock insuficiente para el repuesto '{$repuesto['nombre']}' (Código: {$repuesto['codigo']}). Disponible: {$repuesto['stock_actual']}, Requerido: {$cantidad}.");
                }

                // Usar precio del formulario o fallback al precio de la BD
                $precioFinal = ($precio > 0) ? $precio : (float)$repuesto['precio_venta'];
                $subtotal = round($cantidad * $precioFinal, 2);
                $totalVenta += $subtotal;

                $itemsValidados[] = [
                    'id_repuesto'     => $idRepuesto,
                    'nombre'          => $repuesto['nombre'],
                    'codigo'          => $repuesto['codigo'],
                    'cantidad'        => $cantidad,
                    'precio_unitario' => $precioFinal,
                    'subtotal'        => $subtotal
                ];
            }

            // 3. Insertar la cabecera de la VENTA
            $sqlVenta = "INSERT INTO venta (tipo_comprobante, metodo_pago, total_venta, id_cliente) 
                         VALUES (:tipo, :metodo, :total, :id_cliente)";
            $stmtVenta = $this->db->prepare($sqlVenta);
            $stmtVenta->execute([
                ':tipo'       => $tipoComprobante,
                ':metodo'     => $metodoPago,
                ':total'      => $totalVenta,
                ':id_cliente' => $idCliente
            ]);

            $idVenta = (int)$this->db->lastInsertId();

            // 4. Insertar DETALLE_VENTA y descontar stock dinámicamente con UPDATE
            $sqlDetalle = "INSERT INTO detalle_venta (id_venta, id_repuesto, cantidad, precio_unitario, subtotal)
                           VALUES (:id_venta, :id_repuesto, :cantidad, :precio, :subtotal)";
            $stmtDetalle = $this->db->prepare($sqlDetalle);

            $sqlRestarStock = "UPDATE repuesto 
                               SET stock_actual = stock_actual - :cantidad 
                               WHERE id_repuesto = :id_repuesto";
            $stmtRestarStock = $this->db->prepare($sqlRestarStock);

            foreach ($itemsValidados as $item) {
                // Registrar renglón del detalle
                $stmtDetalle->execute([
                    ':id_venta'    => $idVenta,
                    ':id_repuesto' => $item['id_repuesto'],
                    ':cantidad'    => $item['cantidad'],
                    ':precio'      => $item['precio_unitario'],
                    ':subtotal'    => $item['subtotal']
                ]);

                // Actualizar (restar) stock de repuestos de inmediato
                $stmtRestarStock->execute([
                    ':cantidad'    => $item['cantidad'],
                    ':id_repuesto' => $item['id_repuesto']
                ]);
            }

            // Confirmar transacción
            $this->db->commit();

            return [
                'success'     => true,
                'mensaje'     => "¡Venta #{$idVenta} registrada con éxito y stock actualizado dinámicamente!",
                'id_venta'    => $idVenta,
                'total_venta' => $totalVenta
            ];

        } catch (Exception $e) {
            // Revertir cualquier cambio realizado si ocurre una excepción
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log("[VentaController::registrarVenta] " . $e->getMessage());

            return [
                'success' => false,
                'mensaje' => $e->getMessage()
            ];
        }
    }

    /**
     * Consulta una venta con sus detalles para comprobante o recibo
     * 
     * @param int $idVenta
     * @return array|null
     */
    public function obtenerVenta(int $idVenta): ?array {
        try {
            $sqlVenta = "SELECT v.*, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido, c.cedula, c.telefono 
                         FROM venta v 
                         INNER JOIN cliente c ON v.id_cliente = c.id_cliente 
                         WHERE v.id_venta = :id";
            $stmt = $this->db->prepare($sqlVenta);
            $stmt->execute([':id' => $idVenta]);
            $venta = $stmt->fetch();

            if (!$venta) {
                return null;
            }

            $sqlDetalles = "SELECT dv.*, r.codigo, r.nombre AS repuesto_nombre, r.marca 
                            FROM detalle_venta dv 
                            INNER JOIN repuesto r ON dv.id_repuesto = r.id_repuesto 
                            WHERE dv.id_venta = :id";
            $stmtDet = $this->db->prepare($sqlDetalles);
            $stmtDet->execute([':id' => $idVenta]);
            $venta['detalles'] = $stmtDet->fetchAll();

            return $venta;
        } catch (PDOException $e) {
            error_log("[VentaController::obtenerVenta] " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lista las últimas ventas registradas
     */
    public function listarUltimasVentas(int $limite = 10): array {
        try {
            $sql = "SELECT v.id_venta, v.fecha_venta, v.tipo_comprobante, v.metodo_pago, v.total_venta, 
                           CONCAT(c.nombre, ' ', c.apellido) AS cliente, c.cedula 
                    FROM venta v 
                    INNER JOIN cliente c ON v.id_cliente = c.id_cliente 
                    ORDER BY v.id_venta DESC 
                    LIMIT :limite";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("[VentaController::listarUltimasVentas] " . $e->getMessage());
            return [];
        }
    }
}
