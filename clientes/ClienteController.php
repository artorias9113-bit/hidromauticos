<?php
/**
 * ==============================================================================
 * Sistema de Información para el Control de Inventario y Gestión Comercial
 * Empresa: Hidromáticos Alexis Navas
 * Módulo: Controlador de Clientes
 * ==============================================================================
 */

require_once __DIR__ . '/../conexion.php';

class ClienteController {
    private PDO $db;

    public function __construct() {
        $this->db = getConexion();
    }

    public function listar(): array {
        try {
            $stmt = $this->db->query("SELECT id_cliente, nombre, apellido, cedula, telefono FROM cliente ORDER BY nombre ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("[ClienteController::listar] " . $e->getMessage());
            return [];
        }
    }

    public function registrar(array $datos): array {
        try {
            $sql = "INSERT INTO cliente (nombre, apellido, cedula, telefono, direccion, correo) 
                    VALUES (:nombre, :apellido, :cedula, :telefono, :direccion, :correo)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nombre'    => trim($datos['nombre']),
                ':apellido'  => trim($datos['apellido']),
                ':cedula'    => trim($datos['cedula']),
                ':telefono'  => trim($datos['telefono'] ?? ''),
                ':direccion' => trim($datos['direccion'] ?? ''),
                ':correo'    => trim($datos['correo'] ?? '')
            ]);
            return [
                'success' => true,
                'id'      => (int)$this->db->lastInsertId(),
                'mensaje' => 'Cliente registrado exitosamente.'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'mensaje' => 'Error al registrar cliente: ' . $e->getMessage()
            ];
        }
    }
}
