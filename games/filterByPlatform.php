<?php
require_once '../functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['plataforma']) || empty(trim($_GET['plataforma']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Plataforma requerida'], JSON_UNESCAPED_UNICODE);
    exit;
}

$plataforma = trim($_GET['plataforma']);

try {
    $juegos = mostrarJuegosPorPlataforma($plataforma);
    echo json_encode($juegos, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener juegos'], JSON_UNESCAPED_UNICODE);
}
exit;
?>