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

function enviarJSON($data, $codigo = 200)
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

/*======================================================================================
 * Gestion sesiones - registro, inicio de sesion, etc
 ====================================================================================== */

function iniciarSesion()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

function logout()
{
    iniciarSesion();
    session_destroy();
    return ["success" => true, "message" => "Sesión cerrada"];
}

function estaAutenticado()
{
    iniciarSesion();
    return isset($_SESSION['user_id']);
}

function esAdmin()
{
    iniciarSesion();
    return isset($_SESSION["rol"]) && strtoupper($_SESSION['rol']) === 'ADMIN';
}

function obtenerUsuarioActual()
{
    iniciarSesion();
    return $_SESSION['user_id'] ?? null;
}

/*======================================================================================
 * Gestion usuario - registro, inicio de sesion, etc
 ====================================================================================== */

function emailExiste($mysqli, $email)
{
    $stmt = $mysqli->prepare("SELECT id from users WHERE email=?;");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function usernameExiste($mysqli, $username)
{
    $stmt = $mysqli->prepare("SELECT id from users WHERE username=?;");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

/**
 * Metodo para registrar un usuario en la base de datos
 * @param mixed $username se proporciona el usuario 
 * @param mixed $email el email
 * @param mixed $password y la contrasena
 */
function registrarUsuario($username, $email, $password)
{
    $mysqli = conectarBD();
    try {
        if (strlen($username) < 3) {
            return ["success" => false, "message" => "El nombre de usuario debe tener al menos 3 caracteres"];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ["success" => false, "message" => "El email no es válido"];
        }

        if (strlen($password) < 6) {
            return ["success" => false, "message" => "La contraseña debe tener al menos 6 caracteres"];
        }

        if (emailExiste($mysqli, $email)) {
            return ["success" => false, "message" => "El email ya esta registrado"];
        }

        if (usernameExiste($mysqli, $username)) {
            return ["success" => false, "message" => "El nombre de usuario ya esta registrado"];
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'USER')");
        $stmt->bind_param("sss", $username, $email, $hash);

        if ($stmt->execute()) {
            return ["success" => true, "message" => "Usuario registrado correctamente"];
        } else {
            return ["success" => false, "message" => "No se pudo registrar el usuario"];
        }
    } catch (Exception $e) {
        return ["success" => false, "message" => "Error: " . $e->getMessage()];
    } finally {
        $mysqli->close();
    }
}

function loginUsuario($email, $pass)
{
    $mysqli = conectarBD();
    try {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ["success" => false, "message" => "Email no válido"];
        }

        $stmt = $mysqli->prepare("SELECT id,username,email,password_hash,role FROM `users` WHERE email=?;");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ["success" => false, "message" => "Credenciales incorrectas"];
        }

        $user = $result->fetch_assoc();
        if (password_verify($pass, $user['password_hash'])) {
            iniciarSesion();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['rol'] = $user['role'];

            return [
                "success" => true,
                "message" => "Inicio de sesión exitoso",
                "user" => [
                    "id" => $user['id'],
                    "username" => $user['username'],
                    "email" => $user['email'],
                    "role" => $user['role']
                ]
            ];
        } else {
            return ["success" => false, "message" => "Credenciales incorrectas"];
        }

    } catch (Exception $e) {
        return ["success" => false, "message" => "Error: " . $e->getMessage()];
    } finally {
        $mysqli->close();
    }
}

/*======================================================================================
 * Gestion eventos 
 ====================================================================================== */
function crearEvento($title, $type, $date, $hour, $slots, $image, $desc)
{
    if (!esAdmin()) {
        return ["success" => false, "message" => "No eres admin, no puedes crear un evento"];
    }

    // Validaciones
    if (empty($title) || empty($type) || empty($date) || empty($hour) || empty($desc)) {
        return ["success" => false, "message" => "Todos los campos son requeridos"];
    }

    if ($slots < 0) {
        return ["success" => false, "message" => "Las plazas no pueden ser negativas"];
    }

    $mysqli = conectarBD();
    try {
        $stmt = $mysqli->prepare("INSERT INTO events (titulo, tipo, fecha, hora, plazasLibres, imagen, descripcion) VALUES (?, ?, ?, ?, ?, ?, ?);");
        $slots = (int) $slots;

        $stmt->bind_param("ssssiss", $title, $type, $date, $hour, $slots, $image, $desc);

        if ($stmt->execute()) {
            return ["success" => true, "message" => "Evento creado correctamente", "id" => $mysqli->insert_id];
        } else {
            return ["success" => false, "message" => "No se pudo crear el evento"];
        }
    } catch (Exception $e) {
        return ["success" => false, "message" => "Error: " . $e->getMessage()];
    } finally {
        $mysqli->close();
    }
}

function verificarInscrito($eventId, $userId = null)
{
    if ($userId === null) {
        $userId = obtenerUsuarioActual();
    }

    if (!$userId) {
        return false;
    }

    $mysqli = conectarBD();

    try {
        $stmt = $mysqli->prepare("SELECT user_id FROM user_events WHERE user_id = ? AND event_id = ?");
        $stmt->bind_param("ii", $userId, $eventId);
        $stmt->execute();
        $result = $stmt->get_result()->num_rows > 0;
        return $result;
    } catch (Exception $e) {
        return false;
    } finally {
        $mysqli->close();
    }
}

function inscribirse($eventId)
{
    if (!estaAutenticado()) {
        return ["success" => false, "message" => "Debes iniciar sesión"];
    }

    $userId = obtenerUsuarioActual();
    $mysqli = conectarBD();

    try {
        if (verificarInscrito($eventId, $userId)) {
            return ["success" => false, "message" => "Ya estás inscrito en este evento"];
        }

        $stmt = $mysqli->prepare("SELECT plazasLibres FROM events WHERE id = ?");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $evento = $stmt->get_result()->fetch_assoc();

        if (!$evento) {
            return ["success" => false, "message" => "Evento no encontrado"];
        }

        if ($evento['plazasLibres'] <= 0) {
            return ["success" => false, "message" => "No hay plazas disponibles"];
        }

        $mysqli->begin_transaction();

        $stmt = $mysqli->prepare("INSERT INTO user_events (user_id, event_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userId, $eventId);
        $stmt->execute();

        $stmt = $mysqli->prepare("UPDATE events SET plazasLibres = plazasLibres - 1 WHERE id = ?");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();

        $mysqli->commit();

        return ["success" => true, "message" => "Te has inscrito correctamente"];

    } catch (Exception $e) {
        $mysqli->rollback();
        return ["success" => false, "message" => "Error: " . $e->getMessage()];
    } finally {
        $mysqli->close();
    }
}

function desinscribirse($eventId)
{
    if (!estaAutenticado()) {
        return ["success" => false, "message" => "Debes iniciar sesión"];
    }

    $userId = obtenerUsuarioActual();
    $mysqli = conectarBD();

    try {
        if (!verificarInscrito($eventId, $userId)) {
            return ["success" => false, "message" => "No estás inscrito en este evento"];
        }

        $mysqli->begin_transaction();

        $stmt = $mysqli->prepare("DELETE FROM user_events WHERE user_id = ? AND event_id = ?");
        $stmt->bind_param("ii", $userId, $eventId);
        $stmt->execute();

        $stmt = $mysqli->prepare("UPDATE events SET plazasLibres = plazasLibres + 1 WHERE id = ?");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();

        $mysqli->commit();

        return ["success" => true, "message" => "Te has desinscrito correctamente"];

    } catch (Exception $e) {
        $mysqli->rollback();
        return ["success" => false, "message" => "Error: " . $e->getMessage()];
    } finally {
        $mysqli->close();
    }
}

/*======================================================================================
 * Obtencion de datos - eventos, juegos, etc
 ======================================================================================
 */
function obtenerMisEventos($userId = null)
{
    if (!estaAutenticado()) {
        return ["success" => false, "message" => "Debes iniciar sesión"];
    }

    if ($userId === null) {
        $userId = obtenerUsuarioActual();
    }

    $mysqli = conectarBD();
    try {
        $stmt = $mysqli->prepare("SELECT e.id, e.titulo, e.fecha, e.hora, e.imagen, e.descripcion FROM events e JOIN user_events ue ON ue.event_id = e.id WHERE ue.user_id = ?;");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        return ["success" => true, "eventos" => $events];
    } catch (Exception $e) {
        return ["success" => false, "message" => "Error: " . $e->getMessage()];
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

        foreach ($juegos as &$juego) {
            if (isset($juego['plataformas'])) {
                $juego['plataformas'] = json_decode($juego['plataformas'], true);
            }
        }

        if ($id && count($juegos) > 0) {
            return $juegos[0];
        }

        return $juegos;

    } catch (Exception $e) {
        return ["error" => "Error en obtener juegos: " . $e->getMessage()];
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

            if (estaAutenticado() && $eventos) {
                $eventos['inscrito'] = verificarInscrito($id);
            }
            return $eventos;
        } else {
            $limit = 9;
            $offset = ($page - 1) * $limit;

            $stmt = $mysqli->prepare("SELECT * FROM events ORDER BY fecha ASC LIMIT ? OFFSET ?");
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $eventos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            if (estaAutenticado()) {
                foreach ($eventos as &$evento) {
                    $evento['inscrito'] = verificarInscrito($evento['id']);
                }
            }
            return $eventos;
        }
    } catch (Exception $e) {
        return ["error" => "Error al obtener eventos: " . $e->getMessage()];
    } finally {
        $mysqli->close();
    }
}

/*======================================================================================
 * Funciones de filtrado
 ======================================================================================*/
function mostrarEventosConPlazasLibres($page = 1)
{
    $mysqli = conectarBD();

    try {
        $limit = 9;
        $offset = ($page - 1) * $limit;

        $stmt = $mysqli->prepare("
            SELECT id, titulo, tipo, fecha, hora, plazasLibres, imagen, descripcion 
            FROM events 
            WHERE plazasLibres > 0 
            ORDER BY fecha ASC 
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $eventos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (estaAutenticado()) {
            foreach ($eventos as &$evento) {
                $evento['inscrito'] = verificarInscrito($evento['id']);
            }
        }

        return $eventos;

    } catch (Exception $e) {
        return ["error" => "Error al obtener eventos: " . $e->getMessage()];
    } finally {
        $mysqli->close();
    }
}

function mostrarEventosPorFecha($fecha, $page = 1)
{
    $mysqli = conectarBD();

    try {
        $limit = 9;
        $offset = ($page - 1) * $limit;

        $stmt = $mysqli->prepare("SELECT id, titulo, tipo, fecha, hora, plazasLibres, imagen, descripcion FROM events WHERE fecha = ? ORDER BY hora ASC LIMIT ? OFFSET ?;");
        $stmt->bind_param("sii", $fecha, $limit, $offset);
        $stmt->execute();
        $eventos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (estaAutenticado()) {
            foreach ($eventos as &$evento) {
                $evento['inscrito'] = verificarInscrito($evento['id']);
            }
        }

        return $eventos;

    } catch (Exception $e) {
        return ["error" => "Error al obtener eventos: " . $e->getMessage()];
    } finally {
        $mysqli->close();
    }
}

function mostrarEventosPorTipo($tipo, $page = 1)
{
    $mysqli = conectarBD();

    try {
        $limit = 9;
        $offset = ($page - 1) * $limit;

        $stmt = $mysqli->prepare("SELECT id, titulo, tipo, fecha, hora, plazasLibres, imagen, descripcion FROM events WHERE tipo = ? ORDER BY fecha ASC, hora ASC LIMIT ? OFFSET ?;");
        $stmt->bind_param("sii", $tipo, $limit, $offset);
        $stmt->execute();
        $eventos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (estaAutenticado()) {
            foreach ($eventos as &$evento) {
                $evento['inscrito'] = verificarInscrito($evento['id']);
            }
        }

        return $eventos;

    } catch (Exception $e) {
        return ["error" => "Error al obtener eventos: " . $e->getMessage()];
    } finally {
        $mysqli->close();
    }
}

function mostrarJuegosPorGenero($genero)
{
    $mysqli = conectarBD();
    try {
        $stmt = $mysqli->prepare("SELECT id, titulo, genero, plataformas, imagen, descripcion FROM games WHERE genero = ?;");
        $stmt->bind_param("s", $genero);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $juegos = $resultado->fetch_all(MYSQLI_ASSOC);

        foreach ($juegos as &$juego) {
            if (isset($juego['plataformas'])) {
                $juego['plataformas'] = json_decode($juego['plataformas'], true);
            }
        }
        return $juegos;
    } catch (Exception $e) {
        return ["error" => "Error al obtener juegos: " . $e->getMessage()];
    } finally {
        $mysqli->close();
    }
}
?>