<?php
define("SERVIDOR", "localhost");
define("USUARIO", "root");
define("CLAVE", "");
define("BBDD", "gamefest");

function conectarBD()
{
    $mysqli = new mysqli(SERVIDOR, USUARIO, CLAVE, BBDD);
    $mysqli->set_charset('utf8');
    if ($mysqli->connect_errno) {
        print ("Error de conexion " . $mysqli->connect_error);
    }
    return $mysqli;
}

/**
 * Metodo para obtener juego mediante el id o sin el id
 * @param mixed $id si obtiene el id lo usa si no usa la query por defecto  
 * Retorna el array con todos los juegos o un solo juego
 */
function obtenerJuegos($id = null)
{
    $mysqli = conectarBD();
    $juegos = [];

    try {
        if ($id) {
            $stmt = $mysqli->prepare("SELECT * FROM games WHERE id = ?;");
            $stmt->bind_param('i', $id);
        } else {
            $stmt = $mysqli->prepare("SELECT * from games");
        }
        $stmt->execute();
        $resultado = $stmt->get_result();
        $juegos = $resultado->fetch_all(MYSQLI_ASSOC);

        foreach ($juegos as $juego) {
            if (isset($juego['plataformas'])) {
                $juego['plataformas'] = json_decode($juego['plataformas']);
            }
        }

        if ($id && count($juegos) > 0) {
            return $juegos[0];
        }

        return $juegos;

    } catch (Exception $e) {
        return ["error en obtener juegos" => $e->getMessage()];
    } finally {
        $mysqli->close();
    }
}
/**
 * Metodo para obtener todos los eventos mediante el id o sin el id
 * @param mixed $id 
 * Retorna array con todos los eventos o un evento
 */
function obtenerEventos($id = null)
{
    $mysqli = conectarBD();
    $eventos = [];

    try {
        if ($id) {
            $stmt = $mysqli->prepare("SELECT * FROM events WHERE id = ?;");
            $stmt->bind_param('i', $id);
        } else {
            $stmt = $mysqli->prepare("SELECT * from events ORDER BY fecha ASC");
        }
        $stmt->execute();
        $resultado = $stmt->get_result();
        $eventos = $resultado->fetch_all(MYSQLI_ASSOC);

        if ($id && count($eventos) > 0) {
            return $eventos[0];
        }

        return $eventos;

    } catch (Exception $e) {
        return ["error obtener eventos" => $e->getMessage()];
    } finally {
        $mysqli->close();
    }
}
/**
 *etodo para registrar un usuario en la base de datos
 * @param mixed $username se proporciona el usuario 
 * @param mixed $email el email
 * @param mixed $password y la contrasena
 * @return string Devuelve un texto de confirmacion del registro del usuario
 */
function registrarUsuario($username, $email, $password)
{
    $mysqli = conectarBD();
    try {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $mysqli->prepare("INSERT INTO users (username,email,password_hash,role) VALUES (?,?,?,'user');");
        $stmt->bind_param("sss", $username, $email, $hash);

        if ($stmt->execute()) {
            return "Usuario registrado correctamente, filas devueltas " + $stmt->affected_rows;
        } else {
            return "No se pudo registrar el usuario";
        }


    } catch (Exception $e) {
        return "Error en registrar usuario: " . $e->getMessage();
    } finally {
        $mysqli->close();
    }
}
?>