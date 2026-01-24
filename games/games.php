<?php
require_once '../functions.php';

header('Content-Type: application/json; charset=utf-8');

$juegos = obtenerJuegos();
echo json_encode($juegos, JSON_UNESCAPED_UNICODE);
exit;
?>