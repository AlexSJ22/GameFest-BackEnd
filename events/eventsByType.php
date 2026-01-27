<?php
require_once '../functions.php';
header('Content-Type: application/json; charset=utf-8');

$type = isset($_GET['tipo']) ? $_GET['tipo'] : null;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

if (!$type) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo es requerido'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($page < 1) {
    http_response_code(400);
    echo json_encode(['error' => 'Página inválida'], JSON_UNESCAPED_UNICODE);
    exit;
}

$eventos = mostrarEventosPorTipo($type, $page);
echo json_encode($eventos, JSON_UNESCAPED_UNICODE);
exit;
?>