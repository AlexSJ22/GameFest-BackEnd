<?php
require_once '../functions.php';

// 1. Recogemos el ID del evento que nos manda Vue
$eventId = isset($_GET['id']) ? (int) $_GET['id'] : null;

// 2. Comprobamos si está logueado (sin dar error 401, solo true/false)
$estaLogueado = estaAutenticado();
$estaInscrito = false;

// 3. Solo si está logueado y hay evento, miramos si está inscrito
if ($estaLogueado && $eventId) {
    $estaInscrito = verificarInscrito($eventId);
}

// 4. Enviamos la info limpia al frontend
enviarJSON([
    'success' => true,
    'logged_in' => $estaLogueado, // Vue usará esto para pedir login si hace falta
    'subscribed' => $estaInscrito // Vue usará esto para mostrar "Cancelar" o "Inscribirse"
]);
?>