<?php
require_once '../functions.php';
header('Content-Type: application/json; charset=utf-8');

$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : null;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

if (!$fecha) {
    http_response_code(400);
    echo json_encode(['error' => 'Fecha es requerida'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($page < 1) {
    http_response_code(400);
    echo json_encode(['error' => 'Página inválida'], JSON_UNESCAPED_UNICODE);
    exit;
}

$eventos = mostrarEventosPorFecha($fecha, $page);
echo json_encode($eventos, JSON_UNESCAPED_UNICODE);
exit;
?>