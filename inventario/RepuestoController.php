<?php
/**
 * ==============================================================================
 * Sistema de Información para el Control de Inventario y Gestión Comercial
 * Empresa: Hidromáticos Alexis Navas
 * Módulo: Controlador de Inventario (CRUD de Repuestos)
 * ==============================================================================
 */

require_once __DIR__ . '/../conexion.php';

class RepuestoController {
    private PDO $db;

    public function __construct() {
        $this->db = getConexion();
    }

    /**
     * Consulta y lista todos los repuestos, con opción de filtro por código o nombre
     * 
     * @param string $criterio Término de búsqueda opcional
     * @return array Lista de repuestos encontrados
     */
    public function listar(string $criterio = ''): array {
        try {
            if (!empty($criterio)) {
                $sql = "SELECT * FROM repuesto 
                        WHERE codigo LIKE :criterio1 
                           OR nombre LIKE :criterio2 
                           OR marca LIKE :criterio3 
                        ORDER BY id_repuesto DESC";
                $stmt = $this->db->prepare($sql);
                $param = "%{$criterio}%";
                $stmt->execute([
                    ':criterio1' => $param,
                    ':criterio2' => $param,
                    ':criterio3' => $param
                ]);
            } else {
                $sql = "SELECT * FROM repuesto ORDER BY id_repuesto DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
            }

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("[RepuestoController::listar] " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un repuesto por su identificador primario (ID)
     * 
     * @param int $id Identificador del repuesto
     * @return array|null Datos del repuesto o null si no existe
     */
    public function obtenerPorId(int $id): ?array {
        try {
            $sql = "SELECT * FROM repuesto WHERE id_repuesto = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $repuesto = $stmt->fetch();
            return $repuesto ?: null;
        } catch (PDOException $e) {
            error_log("[RepuestoController::obtenerPorId] " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene un repuesto por su código único de pieza
     * Fundamental para la búsqueda rápida en el punto de venta
     * 
     * @param string $codigo Código del repuesto
     * @return array|null Datos del repuesto o null
     */
    public function obtenerPorCodigo(string $codigo): ?array {
        try {
            $sql = "SELECT * FROM repuesto WHERE codigo = :codigo LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':codigo' => trim($codigo)]);
            $repuesto = $stmt->fetch();
            return $repuesto ?: null;
        } catch (PDOException $e) {
            error_log("[RepuestoController::obtenerPorCodigo] " . $e->getMessage());
            return null;
        }
    }

    /**
     * Registra un nuevo repuesto en el catálogo
     * 
     * @param array $datos Arreglo asociativo con los campos del repuesto
     * @return array ['success' => bool, 'mensaje' => string, 'id' => int|null]
     */
    public function registrar(array $datos): array {
        try {
            // Validación de existencia de código único
            if ($this->obtenerPorCodigo($datos['codigo'])) {
                return [
                    'success' => false,
                    'mensaje' => "El código '{$datos['codigo']}' ya se encuentra registrado."
                ];
            }

            $sql = "INSERT INTO repuesto (
                        codigo, nombre, marca, condicion, 
                        compatibilidad, ubicacion, stock_actual, 
                        precio_venta, precio_costo
                    ) VALUES (
                        :codigo, :nombre, :marca, :condicion, 
                        :compatibilidad, :ubicacion, :stock_actual, 
                        :precio_venta, :precio_costo
                    )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':codigo'         => trim($datos['codigo']),
                ':nombre'         => trim($datos['nombre']),
                ':marca'          => trim($datos['marca'] ?? ''),
                ':condicion'      => $datos['condicion'] ?? 'Nuevo',
                ':compatibilidad' => trim($datos['compatibilidad'] ?? ''),
                ':ubicacion'      => trim($datos['ubicacion'] ?? ''),
                ':stock_actual'   => (int)($datos['stock_actual'] ?? 0),
                ':precio_venta'   => (float)($datos['precio_venta'] ?? 0.0),
                ':precio_costo'   => (float)($datos['precio_costo'] ?? 0.0)
            ]);

            return [
                'success' => true,
                'mensaje' => 'Repuesto registrado exitosamente en el inventario.',
                'id'      => (int)$this->db->lastInsertId()
            ];
        } catch (PDOException $e) {
            error_log("[RepuestoController::registrar] " . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => 'Error al registrar el repuesto: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza la información de un repuesto existente
     * 
     * @param int $id Identificador del repuesto
     * @param array $datos Datos actualizados
     * @return array ['success' => bool, 'mensaje' => string]
     */
    public function actualizar(int $id, array $datos): array {
        try {
            // Verificar si el nuevo código entra en conflicto con otro repuesto
            $existente = $this->obtenerPorCodigo($datos['codigo']);
            if ($existente && (int)$existente['id_repuesto'] !== $id) {
                return [
                    'success' => false,
                    'mensaje' => "El código '{$datos['codigo']}' ya pertenece a otro repuesto."
                ];
            }

            $sql = "UPDATE repuesto SET 
                        codigo = :codigo,
                        nombre = :nombre,
                        marca = :marca,
                        condicion = :condicion,
                        compatibilidad = :compatibilidad,
                        ubicacion = :ubicacion,
                        stock_actual = :stock_actual,
                        precio_venta = :precio_venta,
                        precio_costo = :precio_costo
                    WHERE id_repuesto = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':codigo'         => trim($datos['codigo']),
                ':nombre'         => trim($datos['nombre']),
                ':marca'          => trim($datos['marca'] ?? ''),
                ':condicion'      => $datos['condicion'] ?? 'Nuevo',
                ':compatibilidad' => trim($datos['compatibilidad'] ?? ''),
                ':ubicacion'      => trim($datos['ubicacion'] ?? ''),
                ':stock_actual'   => (int)($datos['stock_actual'] ?? 0),
                ':precio_venta'   => (float)($datos['precio_venta'] ?? 0.0),
                ':precio_costo'   => (float)($datos['precio_costo'] ?? 0.0),
                ':id'             => $id
            ]);

            return [
                'success' => true,
                'mensaje' => 'Repuesto actualizado correctamente.'
            ];
        } catch (PDOException $e) {
            error_log("[RepuestoController::actualizar] " . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => 'Error al actualizar el repuesto: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Elimina un repuesto siempre y cuando no tenga historial de ventas o compras asociadas
     * 
     * @param int $id
     * @return array
     */
    public function eliminar(int $id): array {
        try {
            $sql = "DELETE FROM repuesto WHERE id_repuesto = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);

            return [
                'success' => true,
                'mensaje' => 'Repuesto eliminado con éxito.'
            ];
        } catch (PDOException $e) {
            // Captura de violación de llave foránea (Integrity constraint violation)
            if ($e->getCode() == '23000') {
                return [
                    'success' => false,
                    'mensaje' => 'No se puede eliminar el repuesto porque posee transacciones históricas asociadas (ventas o compras).'
                ];
            }
            return [
                'success' => false,
                'mensaje' => 'Error al eliminar el repuesto.'
            ];
        }
    }
}
