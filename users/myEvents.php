<?php
require_once '../functions.php';

if (!estaAutenticado()) {
    enviarJSON(['success' => false, 'message' => 'Debes iniciar sesión'], 401);
}

$resultado = obtenerMisEventos();

if ($resultado['success']) {
    enviarJSON($resultado['eventos'], 200);
} else {
    enviarJSON($resultado, 400);
}
?>