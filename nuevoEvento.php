<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    enviarJSON(['success' => false, 'message' => 'Método no permitido'], 405);
}

if (!esAdmin()) {
    enviarJSON(['success' => false, 'message' => 'No tienes permisos para crear eventos'], 403);
}

$input = json_decode(file_get_contents('php://input'), true);

$camposRequeridos = ['titulo', 'tipo', 'fecha', 'hora', 'plazasLibres', 'descripcion'];
foreach ($camposRequeridos as $campo) {
    if (!isset($input[$campo]) || empty($input[$campo])) {
        enviarJSON(['success' => false, 'message' => "El campo $campo es requerido"], 400);
    }
}

$titulo = trim($input['titulo']);
$tipo = trim($input['tipo']);
$fecha = trim($input['fecha']);
$hora = trim($input['hora']);
$plazas = (int) $input['plazasLibres'];
$imagen = isset($input['imagen']) ? trim($input['imagen']) : '';
$descripcion = trim($input['descripcion']);

if ($plazas < 0) {
    enviarJSON(['success' => false, 'message' => 'Las plazas no pueden ser negativas'], 400);
}

$resultado = crearEvento($titulo, $tipo, $fecha, $hora, $plazas, $imagen, $descripcion);

if ($resultado['success']) {
    enviarJSON($resultado, 201);
} else {
    enviarJSON($resultado, 400);
}
?>