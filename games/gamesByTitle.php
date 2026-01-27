<?php
require_once '../functions.php';
header('Content-Type: application/json; charset=utf-8');

$title = isset($_GET['tipo']) ? $_GET['tipo'] : null;

if (!$type) {
    http_response_code(400);
    echo json_encode(['error' => 'Titulo es requerido'], JSON_UNESCAPED_UNICODE);
    exit;
}

$juegos = mostrarJuegosPorTitulo($type);
echo json_encode($juegos, JSON_UNESCAPED_UNICODE);
exit;
?>