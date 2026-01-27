<?php
require_once '../functions.php';
header('Content-Type: application/json; charset=utf-8');

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

if ($page < 1) {
    http_response_code(400);
    echo json_encode(['error' => 'Página inválida'], JSON_UNESCAPED_UNICODE);
    exit;
}

$eventos = mostrarEventosConPlazasLibres($page);
echo json_encode($eventos, JSON_UNESCAPED_UNICODE);
exit;
?>