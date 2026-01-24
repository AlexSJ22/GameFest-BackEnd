<?php
require_once '../functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    enviarJSON(['success' => false, 'message' => 'Método no permitido'], 405);
}

if (!estaAutenticado()) {
    enviarJSON(['success' => false, 'message' => 'Debes iniciar sesión'], 401);
}

$eventId = isset($_GET['id']) ? (int) $_GET['id'] : null;

if (!$eventId) {
    enviarJSON(['success' => false, 'message' => 'ID de evento requerido'], 400);
}

$resultado = desinscribirse($eventId);
enviarJSON($resultado, $resultado['success'] ? 200 : 400);
?>