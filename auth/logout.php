<?PHP
require_once '../functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    enviarJSON(['success' => false, 'message' => 'Método no permitido'], 405);
}
$resultado = logout();
enviarJSON($resultado, 200);
?>