<?php
/**
 * ==============================================================================
 * Sistema de Información para el Control de Inventario y Gestión Comercial
 * Empresa: Hidromáticos Alexis Navas
 * Módulo: API Endpoint para Ventas (JSON)
 * ==============================================================================
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/VentaController.php';

$controller = new VentaController();

// Permitir recepción de datos tanto en JSON crudo como en POST normal
$rawInput = file_get_contents('php://input');
$jsonBody = json_decode($rawInput, true) ?? [];

$accion = $_GET['accion'] ?? ($jsonBody['accion'] ?? ($_POST['accion'] ?? ''));

switch ($accion) {
    case 'registrar':
        $data = !empty($jsonBody) ? $jsonBody : $_POST;
        
        $idCliente       = (int)($data['id_cliente'] ?? 0);
        $tipoComprobante = trim($data['tipo_comprobante'] ?? 'Factura');
        $metodoPago      = trim($data['metodo_pago'] ?? 'Efectivo');
        $detalles        = $data['detalles'] ?? [];

        $resultado = $controller->registrarVenta($idCliente, $tipoComprobante, $metodoPago, $detalles);
        echo json_encode($resultado);
        break;

    case 'obtener':
        $idVenta = (int)($_GET['id_venta'] ?? ($jsonBody['id_venta'] ?? 0));
        if ($idVenta <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de venta inválido.']);
            exit;
        }
        $venta = $controller->obtenerVenta($idVenta);
        if ($venta) {
            echo json_encode(['success' => true, 'data' => $venta]);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Venta no encontrada.']);
        }
        break;

    case 'ultimas':
        $limite = (int)($_GET['limite'] ?? 10);
        $ventas = $controller->listarUltimasVentas($limite);
        echo json_encode(['success' => true, 'data' => $ventas]);
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción de venta no reconocida.']);
        break;
}
