<?php
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Directorio donde se guardan las imágenes
$dir = __DIR__ . '/../gamefest_resources/events';

// Verificar que la carpeta existe
if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
}

// Verificar permisos de escritura
if (!is_writable($dir)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'La carpeta de destino no tiene permisos de escritura'
    ]);
    exit;
}

// Verificar que se envió un archivo
if (!isset($_FILES['imagen'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No se envió ningún archivo']);
    exit;
}

$file = $_FILES['imagen'];

// Verificar errores en la subida
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la subida: ' . $file['error']
    ]);
    exit;
}

// Verificar que sea una imagen real (no solo por extensión)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$permitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/jpg'];

if (!in_array($mime, $permitidos)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => "El archivo no es una imagen válida. Tipo: $mime"
    ]);
    exit;
}

// Generar nombre aleatorio seguro
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$nuevoNombre = bin2hex(random_bytes(8)) . '.' . $ext;
$destino = $dir . '/' . $nuevoNombre;

// Mover archivo
if (move_uploaded_file($file['tmp_name'], $destino)) {
    echo json_encode([
        'success' => true,
        'message' => 'Imagen subida correctamente',
        'filename' => $nuevoNombre,
        'path' => 'gamefest_resources/events/' . $nuevoNombre
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'No se pudo mover el archivo a la carpeta de destino'
    ]);
}
?>