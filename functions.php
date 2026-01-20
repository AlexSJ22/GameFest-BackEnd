<?php
define("SERVIDOR", "localhost");
define("USUARIO", "root");
define("CLAVE", "");
define("BBDD", "gamefest");

function obtenerJuegos()
{
    $mysqli = new mysqli(SERVIDOR, USUARIO, CLAVE, BBDD);
    $mysqli->set_charset('utf8');
    $sql = "SELECT * FROM games";
    $resultado = $mysqli->query($sql);

    $eventos = [];
    while ($fila = $resultado->fetch_assoc()) {
        $eventos[] = $fila;
    }
    echo json_encode($eventos);
    $resultado->free();
    $mysqli->close();
}
function obtenerEventos()
{
    $mysqli = new mysqli(SERVIDOR, USUARIO, CLAVE, BBDD);
    $mysqli->set_charset('utf8');
    $sql = "SELECT * FROM eventos";
    $resultado = $mysqli->query($sql);
    $eventos = [];
    while ($fila = $resultado->fetch_assoc()) {
        $eventos[] = $fila;
    }
    echo json_encode($eventos);
    $resultado->free();
    $mysqli->close();
}
?>