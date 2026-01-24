<?php
require_once '../functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    enviarJSON(['success' => false, 'message' => 'Método no permitido'], 405);
}

if (!esAdmin()) {
    enviarJSON(['success' => false, 'message' => 'Solo administradores pueden crear eventos'], 403);
}

$input = json_decode(file_get_contents('php://input'), true);

$titulo = $input['titulo'] ?? '';
$tipo = $input['tipo'] ?? '';
$fecha = $input['fecha'] ?? '';
$hora = $input['hora'] ?? '';
$plazas = $input['plazasLibres'] ?? 0;
$imagen = $input['imagen'] ?? '';
$descripcion = $input['descripcion'] ?? '';

$resultado = crearEvento($titulo, $tipo, $fecha, $hora, $plazas, $imagen, $descripcion);
enviarJSON($resultado, $resultado['success'] ? 201 : 400);
?>