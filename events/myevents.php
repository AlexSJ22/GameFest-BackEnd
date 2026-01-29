<?php
require_once '../functions.php';

// 1. Check method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    enviarJSON(['success' => false, 'message' => 'Método no permitido'], 405);
}

// 2. Check auth
if (!estaAutenticado()) {
    enviarJSON(['success' => false, 'message' => 'No autenticado'], 401);
}

// 3. Get events using your existing function in functions.php
$resultado = obtenerMisEventos();

// 4. Return data
enviarJSON($resultado);
?>