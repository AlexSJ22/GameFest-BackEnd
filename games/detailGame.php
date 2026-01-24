<?php
require_once '../functions.php';

header('Content-Type: application/json; charset=utf-8');

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido'], JSON_UNESCAPED_UNICODE);
    exit;
}

$juego = obtenerJuegos($id);

if (empty($juego)) {
    http_response_code(404);
    echo json_encode(['error' => 'Juego no encontrado'], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode($juego, JSON_UNESCAPED_UNICODE);
}
exit;
?>