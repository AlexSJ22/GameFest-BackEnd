<?php
require_once '../functions.php';

if (!estaAutenticado()) {
    enviarJSON(['success' => false, 'message' => 'No autenticado'], 401);
}

iniciarSesion();

$usuario = [
    'id' => $_SESSION['user_id'],
    'username' => $_SESSION['username'],
    'email' => $_SESSION['email'],
    'role' => $_SESSION['rol']
];

enviarJSON(['success' => true, 'user' => $usuario], 200);
?>