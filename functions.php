<?php
define("SERVIDOR", "localhost");
define("USUARIO", "root");
define("CLAVE", "");
define("BBDD", "gamefest");

/**
 * Metodo para realizar la conexion a la base de datos
 * @return mysqli
 */
function conectarBD()
{
    $mysqli = new mysqli(SERVIDOR, USUARIO, CLAVE, BBDD);
    $mysqli->set_charset('utf8');
    if ($mysqli->connect_errno) {
        print ("Error de conexion " . $mysqli->connect_error);
    }
    return $mysqli;
}


/*======================================================================================
 * Gestion usuario - registro, inicio de sesion, etc
 ======================================================================================
 */

/**
 *Metodo para registrar un usuario en la base de datos
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
            return "Usuario registrado correctamente, filas devueltas " . $stmt->affected_rows;
        } else {
            return "No se pudo registrar el usuario";
        }


    } catch (Exception $e) {
        return "Error en registrar usuario: " . $e->getMessage();
    } finally {
        $mysqli->close();
    }
}
function registrarUsuarioAdmin($username, $email, $password)
{
    $mysqli = conectarBD();
    try {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $mysqli->prepare("INSERT INTO users (username,email,password_hash,role) VALUES (?,?,?,'admin');");
        $stmt->bind_param("sss", $username, $email, $hash);

        if ($stmt->execute()) {
            return "Administrador registrado correctamente, filas devueltas " . $stmt->affected_rows;
        } else {
            return "No se pudo registrar el usuario";
        }


    } catch (Exception $e) {
        return "Error en registrar admin: " . $e->getMessage();
    } finally {
        $mysqli->close();
    }
}

function crearEvento($title, $type, $date, $hour, $slots, $image, $desc)
{
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
        return "No tienes permisos para crear eventos";
    }

    if ($slots < 0) {
        return false;
    }

    $mysqli = conectarBD();
    try {
        $stmt = $mysqli->prepare("INSERT INTO events (titulo, tipo_evento, fecha, hora, plazasLibres, imagen, descripcion) VALUES (?, ?, ?, ?, ?, ?, ?);");
        $slots = (int) $slots;

        $stmt->bind_param(
            "ssssiss",
            $title,
            $type,
            $date,
            $hour,
            $slots,
            $image,
            $desc
        );

        if ($stmt->execute()) {
            return "Evento creado correctamente, filas devueltas " . $stmt->affected_rows;
        } else {
            return "No se pudo crear el evento";
        }
    } finally {
        $mysqli->close();
    }
}

function loginUsuario($email, $pass)
{
    $mysqli = conectarBD();
    try {
        $stmt = $mysqli->prepare("SELECT id,username,email,password_hash,role FROM `users` WHERE email=?;");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if ($stmt && password_verify($pass, $result['password_hash'])) {
            return $stmt;
        }
        return false;
    } finally {
        $mysqli->close();
    }
}

function inscribirse($eventId)
{
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    $userId = $_SESSION["user_id"];
    $mysqli = conectarBD();
    try {
        if (verificarInscrito($mysqli, $userId, $eventId) == true) {
            //TODO Bloquear evento
        } else {
            //todo Inscribirse y restar plaza
        }

    } finally {

    }
}

function verificarInscrito($mysqli, $userId, $eventId)
{
    try {
        $stmt = $mysqli->prepare("SELECT user_id FROM user_events WHERE user_id = ? AND event_id = ?;");
        $stmt->bind_param(
            "ii",
            $userId,
            $eventId
        );
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo "Esta registrado";
            return true;
        } else {
            echo "No está registrado";
            return false;
        }
    } finally {
        $mysqli->close();
    }
}

function verificarPlazas($eventId)
{

}

function restarPlaza($eventId)
{

}

function bloquearEvento($eventId)
{

}
/*======================================================================================
 * Obtencion de datos - eventos, juegos, etc
 ======================================================================================
 */
function obtenerMisEventos($userId)
{
    $mysqli = conectarBD();
    try {
        $stmt = $mysqli->prepare("SELECT e.titulo,e.fecha,e.hora,e.imagen,e.descripcion FROM events e JOIN user_events u on u.user_id= e.id WHERE user_id =?;");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } finally {
        $mysqli->close();
    }
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
 * Retorna array con todos los eventos y son paginados o un solo evento 
 */
function obtenerEventos($page = 1, $id = null)
{
    $mysqli = conectarBD();
    try {
        if ($id) {
            $stmt = $mysqli->prepare("SELECT * FROM events WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $eventos = $stmt->get_result()->fetch_assoc();
            return $eventos;
        } else {
            $limit = 9;
            $offset = ($page - 1) * $limit;

            $stmt = $mysqli->prepare("SELECT * FROM events ORDER BY fecha ASC LIMIT ? OFFSET ?");
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
    } finally {
        $mysqli->close();
    }
}
?>