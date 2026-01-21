<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header("Content-Type: application/json; charset=utf-8");
require_once "functions.php";
echo json_encode(obtenerJuegos(), JSON_UNESCAPED_UNICODE);
exit;
?>