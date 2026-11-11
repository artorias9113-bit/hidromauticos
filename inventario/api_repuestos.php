<?php
/**
 * ==============================================================================
 * Sistema de Información para el Control de Inventario y Gestión Comercial
 * Empresa: Hidromáticos Alexis Navas
 * Módulo: API Endpoint para Repuestos (JSON)
 * ==============================================================================
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/RepuestoController.php';

$controller = new RepuestoController();
$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? ($_POST['accion'] ?? '');

// Si los datos vienen en el cuerpo en formato JSON (como fetch POST)
$rawInput = file_get_contents('php://input');
$jsonBody = json_decode($rawInput, true) ?? [];

if (!empty($jsonBody['accion'])) {
    $accion = $jsonBody['accion'];
}

switch ($accion) {
    case 'listar':
        $filtro = $_GET['buscar'] ?? '';
        $repuestos = $controller->listar($filtro);
        echo json_encode(['success' => true, 'data' => $repuestos]);
        break;

    case 'buscar_codigo':
        $codigo = $_GET['codigo'] ?? ($jsonBody['codigo'] ?? '');
        if (empty($codigo)) {
            echo json_encode(['success' => false, 'mensaje' => 'Debe ingresar un código.']);
            exit;
        }
        $repuesto = $controller->obtenerPorCodigo($codigo);
        if ($repuesto) {
            echo json_encode(['success' => true, 'data' => $repuesto]);
        } else {
            echo json_encode(['success' => false, 'mensaje' => "No se encontró ningún repuesto con el código '{$codigo}'."]);
        }
        break;

    case 'obtener':
        $id = (int)($_GET['id'] ?? 0);
        $repuesto = $controller->obtenerPorId($id);
        if ($repuesto) {
            echo json_encode(['success' => true, 'data' => $repuesto]);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Repuesto no encontrado.']);
        }
        break;

    case 'guardar':
        $datos = !empty($jsonBody) ? $jsonBody : $_POST;
        $id = !empty($datos['id_repuesto']) ? (int)$datos['id_repuesto'] : null;

        if ($id) {
            $resultado = $controller->actualizar($id, $datos);
        } else {
            $resultado = $controller->registrar($datos);
        }
        echo json_encode($resultado);
        break;

    case 'eliminar':
        $datos = !empty($jsonBody) ? $jsonBody : $_POST;
        $id = (int)($datos['id_repuesto'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'ID inválido.']);
            exit;
        }
        $resultado = $controller->eliminar($id);
        echo json_encode($resultado);
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida o no especificada.']);
        break;
}
