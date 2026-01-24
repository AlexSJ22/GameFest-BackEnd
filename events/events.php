<?php
require_once '../functions.php';

header('Content-Type: application/json; charset=utf-8');

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

$eventos = obtenerEventos($page);
echo json_encode($eventos, JSON_UNESCAPED_UNICODE);
exit;
?>