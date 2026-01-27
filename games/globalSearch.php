<?php
require_once '../functions.php';
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
// Solo permitir método GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    enviarJSON(["success" => false, "message" => "Método no permitido"], 405);
}
// Obtener parámetro de búsqueda
$query = isset($_GET['q']) ? trim($_GET['q']) : null;
// Validar que haya un término de búsqueda
if (empty($query)) {
    enviarJSON([
        "success" => false,
        "message" => "Debe proporcionar un término de búsqueda usando el parámetro 'q'"
    ], 400);
}
// Llamar a la función de búsqueda global
$resultado = buscarJuegosGlobal($query);
// Enviar respuesta
if (isset($resultado['success']) && $resultado['success']) {
    enviarJSON($resultado, 200);
} else {
    enviarJSON($resultado, 500);
}
?>