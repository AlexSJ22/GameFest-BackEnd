<?php
require_once '../functions.php';

iniciarSesion();

if (estaAutenticado()) {
    $userId = obtenerUsuarioActual();

    // Obtener datos completos del usuario
    $mysqli = conectarBD();
    $stmt = $mysqli->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $mysqli->close();

    enviarJSON([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ], 200);
} else {
    enviarJSON(['success' => false, 'message' => 'No hay sesión activa'], 200);
}
?>