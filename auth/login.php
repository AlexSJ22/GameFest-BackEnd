<?php
require_once '../functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    enviarJSON(['success' => false, 'message' => 'Método no permitido'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['email']) || !isset($input['password'])) {
    enviarJSON(['success' => false, 'message' => 'Email y contraseña son requeridos'], 400);
}

$email = trim($input['email']);
$password = $input['password'];

if (empty($email) || empty($password)) {
    enviarJSON(['success' => false, 'message' => 'Email y contraseña son requeridos'], 400);
}

$resultado = loginUsuario($email, $password);

if ($resultado['success']) {
    enviarJSON($resultado, 200);
} else {
    enviarJSON($resultado, 200);
}
?>