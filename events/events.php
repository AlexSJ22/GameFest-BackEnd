<?php
require_once '../functions.php'; // Asegúrate de que la ruta sea correcta
header('Content-Type: application/json; charset=utf-8');

// Recoger parámetros (si no existen, serán null o valores por defecto)
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : null;
$plazas = isset($_GET['plazas']) ? $_GET['plazas'] : null;

if ($page < 1)
    $page = 1;

// Llamamos a la super función
$resultado = obtenerEventos($page, $id, $tipo, $fecha, $plazas);

echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
exit;
?>