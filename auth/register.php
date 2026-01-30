<?php
require_once '../functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    enviarJSON(['success' => true], 200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    enviarJSON(['success' => false, 'message' => 'Método no permitido'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['username']) || !isset($input['email']) || !isset($input['password'])) {
    enviarJSON(['success' => false, 'message' => 'Faltan datos requeridos'], 400);
}

$username = trim($input['username']);
$email = trim($input['email']);
$password = $input['password'];

if (empty($username) || empty($email) || empty($password)) {
    enviarJSON(['success' => false, 'message' => 'Todos los campos son requeridos'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    enviarJSON(['success' => false, 'message' => 'Email inválido'], 400);
}

if (strlen($password) < 6) {
    enviarJSON(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'], 400);
}

$resultado = registrarUsuario($username, $email, $password);

if ($resultado['success']) {
    enviarJSON($resultado, 201);
} else {
    enviarJSON($resultado, 200);
}
?>