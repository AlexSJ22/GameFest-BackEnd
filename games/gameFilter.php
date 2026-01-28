<?php
require_once '../functions.php';
header('Content-Type: application/json; charset=utf-8');

$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    if ($busqueda === '') {
        $juegos = obtenerJuegos(); // devuelve todos los juegos
    } else {
        $juegos = buscarJuegos($busqueda); // búsqueda filtrada
    }

    echo json_encode($juegos, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al buscar juegos'], JSON_UNESCAPED_UNICODE);
}
exit;
?>