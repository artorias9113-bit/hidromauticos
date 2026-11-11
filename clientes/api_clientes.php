<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/ClienteController.php';

$controller = new ClienteController();
$rawInput = file_get_contents('php://input');
$jsonBody = json_decode($rawInput, true) ?? [];
$accion = $_GET['accion'] ?? ($jsonBody['accion'] ?? ($_POST['accion'] ?? 'listar'));

if ($accion === 'listar') {
    echo json_encode(['success' => true, 'data' => $controller->listar()]);
} elseif ($accion === 'registrar') {
    $datos = !empty($jsonBody) ? $jsonBody : $_POST;
    echo json_encode($controller->registrar($datos));
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
}
