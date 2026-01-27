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

/**
 * Metodo para devolver un json 
 * @param mixed $data con los datos pasados por parametro
 * @param mixed $codigo --> codigo http para respuesta de la pagina
 */
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

/**
 * SMetodo para iniciar sesion en la pagina
 * Con session
 */
function iniciarSesion()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Metodo para cerrar sesion en la pagina 
 * @return array{message: string, success: bool} Devuelve un mensaje de confirmacion
 * Destruye la session
 */
function logout()
{
    iniciarSesion();
    session_destroy();
    return ["success" => true, "message" => "Sesión cerrada"];
}

/**
 * Metodo para verificar si el usuario esta autenticado en la sesion actual
 * @return bool Devuelve true si hay un usuario autenticado, false si no
 */
function estaAutenticado()
{
    iniciarSesion();
    return isset($_SESSION['user_id']);
}

/**
 * Comprueba si el usuario actual es admin
 * @return bool Devuelve true|false si el usuario es admin
 */
function esAdmin()
{
    iniciarSesion();
    return isset($_SESSION["rol"]) && strtoupper($_SESSION['rol']) === 'ADMIN';
}

/**
 * Obtiene el id del usuario logeado
 * @return int|null Devuelve el ID del usuario o null si no hay sesion
 */
function obtenerUsuarioActual()
{
    iniciarSesion();
    return $_SESSION['user_id'] ?? null;
}

/*======================================================================================
 * Gestion usuario - registro, inicio de sesion, etc
 ====================================================================================== */

/**
 * Comprueba si un email ya esta vinculado en una base de datos
 * @param mixed $mysqli Se proporciona la conexion a la base de datos
 * @param mixed $email Email a comprobar
 * @return bool Devuelve true si el email existe
 */
function emailExiste($mysqli, $email)
{
    $stmt = $mysqli->prepare("SELECT id from users WHERE email=?;");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

/**
 * Comprueba si un nombre de usuario existe en la base datos
 * @param mixed $mysqli Se proporciona la conexion a la base de datos
 * @param mixed $username Nombre de usuario a comprobar
 * @return bool Devuelve true si el usuario existe
 */
function usernameExiste($mysqli, $username)
{
    $stmt = $mysqli->prepare("SELECT id from users WHERE username=?;");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

/**
 * Metodo para registrar un usuario en la base de datos
 * @param mixed $username Se proporciona la conexion a la base de datos
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

/**
 * Metodo para autenticar el usuario mediante el email y la contrasena
 * @param mixed $email Email del usuario 
 * @param mixed $pass Contrasena del usuario
 * @return array{message: string, success: bool, user: array{email: mixed, id: mixed, role: mixed, username: mixed}|array{message: string, success: bool}}
 * Devuelve el resultado del inicio de sesion
 */
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
/**
 * Metodo para crear un nuevo evento en la base de datos,
 * que solo puede ser creado por un administrador
 * @param mixed $title Se proporciona un titulo
 * @param mixed $type Se proporciona un tipo
 * @param mixed $date Se proporciona una fecha
 * @param mixed $hour Se proporciona una hora
 * @param mixed $slots Se proporciona las plazas
 * @param mixed $image Se proporciona una imagen
 * @param mixed $desc Y se proporciona una descripcion 
 * @return array{id: int|string, message: string, success: bool|array{message: string, success: bool}}
 * Devuelve el resultado de la creacion del evento
 */
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

/**
 * Metodo que verifica si un usuario esta incrito en un evento 
 * @param mixed $eventId Se proporciona un id del evento 
 * @param mixed $userId Y el id del usuario
 * @return bool Devuelve true|false si el usuario esta incrito o no
 */
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

/**
 * Metodo para incribir al usuario a un evento
 * @param mixed $eventId Se proporciona el id del evento
 * @return array{message: string, success: bool} Devuelve el resultado de la inscripcion
 */
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

/**
 * Metodo para desincribir al usuario actual de un evento
 * @param mixed $eventId Se proporciona el id del evento
 * @return array{message: string, success: bool} Devuelve el resultado de la inscripcion
 */
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

/**
 * Metodo para obtener todos los eventos del usuario
 * @param mixed $userId (Opcional) Se proporciona el id del usuario
 * @return array{eventos: array, success: bool|array{message: string, success: bool}}
 * Devuelve los eventos del usuario
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
 * Metodo para obtener uno o varios juegos
 * @param mixed $id (Opcional) Se proporciona la id del juego
 * Retorna todos los juegos o un solo juego
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
 * Metodo para obtener uno o varios eventos de forma paginada o por ID
 * @param mixed $page (Opcional) Se proporciona el numero de la pagina
 * @param mixed $id (Opcional) Se proporciona el id del evento
 * @return array|array{error: string|bool|null}
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

            $totalResult = $mysqli->query("SELECT COUNT(*) as total FROM events");
            $total = $totalResult->fetch_assoc()['total'];

            $stmt = $mysqli->prepare("SELECT * FROM events ORDER BY fecha ASC LIMIT ? OFFSET ?");
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $eventos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            if (estaAutenticado()) {
                foreach ($eventos as &$evento) {
                    $evento['inscrito'] = verificarInscrito($evento['id']);
                }
            }
            return ["total" => $total, "eventos" => $eventos];
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

/**
 * Metodo para obtener los eventos con plazas libres de forma paginada
 * @param mixed $page (Opcional) Se proporciona el numero de la pagina
 * @return array|array{error: string} Devuelve un array con plazas libres
 */
function mostrarEventosConPlazasLibres($page = 1)
{
    $mysqli = conectarBD();

    try {
        $limit = 9;
        $offset = ($page - 1) * $limit;

        $totalResult = $mysqli->query(
            "SELECT COUNT(*) as total FROM events WHERE plazasLibres > 0"
        );
        $total = $totalResult->fetch_assoc()['total'];

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

        return ["total" => $total, "eventos" => $eventos];

    } catch (Exception $e) {
        return ["error" => "Error al obtener eventos: " . $e->getMessage()];
    } finally {
        $mysqli->close();
    }
}

/**
 * Obtiene los eventos filtrados por fecha
 * @param mixed $fecha Se proporciona la fecha del evento
 * @param mixed $page (Opcional) Se proporciona el numero de la pagina
 * @return array|array{error: string} Devuelve un array de los eventos filtrados
 */
function mostrarEventosPorFecha($fecha, $page = 1)
{
    $mysqli = conectarBD();

    try {
        $limit = 9;
        $offset = ($page - 1) * $limit;

        $totalResult = $mysqli->query(
            "SELECT COUNT(*) as total FROM events WHERE plazasLibres > 0"
        );
        $total = $totalResult->fetch_assoc()['total'];

        $stmt = $mysqli->prepare("SELECT id, titulo, tipo, fecha, hora, plazasLibres, imagen, descripcion FROM events WHERE fecha = ? ORDER BY hora ASC LIMIT ? OFFSET ?;");
        $stmt->bind_param("sii", $fecha, $limit, $offset);
        $stmt->execute();
        $eventos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (estaAutenticado()) {
            foreach ($eventos as &$evento) {
                $evento['inscrito'] = verificarInscrito($evento['id']);
            }
        }

        return ["total" => $total, "eventos" => $eventos];

    } catch (Exception $e) {
        return ["error" => "Error al obtener eventos: " . $e->getMessage()];
    } finally {
        $mysqli->close();
    }
}

/**
 * Metodo para mostrar los eventos filtrados por tipo
 * @param mixed $tipo Se proporciona el tipo del evento
 * @param mixed $page (Opcional) Se proporciona el numero de la pagina
 * @return array|array{error: string} Devuelve un array con los eventos filtrados
 */
function mostrarEventosPorTipo($tipo, $page = 1)
{
    $mysqli = conectarBD();

    try {
        $limit = 9;
        $offset = ($page - 1) * $limit;

        $totalResult = $mysqli->query(
            "SELECT COUNT(*) as total FROM events WHERE plazasLibres > 0"
        );
        $total = $totalResult->fetch_assoc()['total'];

        $stmt = $mysqli->prepare("SELECT id, titulo, tipo, fecha, hora, plazasLibres, imagen, descripcion FROM events WHERE tipo = ? ORDER BY fecha ASC, hora ASC LIMIT ? OFFSET ?;");
        $stmt->bind_param("sii", $tipo, $limit, $offset);
        $stmt->execute();
        $eventos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (estaAutenticado()) {
            foreach ($eventos as &$evento) {
                $evento['inscrito'] = verificarInscrito($evento['id']);
            }
        }

        return ["total" => $total, "eventos" => $eventos];

    } catch (Exception $e) {
        return ["error" => "Error al obtener eventos: " . $e->getMessage()];
    } finally {
        $mysqli->close();
    }
}

/**
 * Metodo para mostrar los juegos por nombre
 * @param mixed $genero Se proporciona el nombre
 * @return array|array{error: string} Devuelve un array con los juegos filtrados por el nombre
 */
function mostrarJuegosPorTitulo($titulo)
{
    $mysqli = conectarBD();
    try {
        $stmt = $mysqli->prepare("SELECT id, titulo, genero, plataformas, imagen, descripcion FROM games WHERE titulo = ?;");
        $stmt->bind_param("s", $titulo);
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

/**
 * Obtiene juegos filtrados por plataforma
 * @param mixed $plataforma Nombre de la plataforma a buscar
 * @return array|array{error: string} Devuelve un array con los juegos filtrados
 */
function mostrarJuegosPorPlataforma($plataforma)
{
    $mysqli = conectarBD();
    try {
        $stmt = $mysqli->prepare("SELECT id, titulo, genero, plataformas, imagen, descripcion FROM games WHERE JSON_CONTAINS(plataformas, ?);");
        $jsonValue = json_encode($plataforma);
        $stmt->bind_param("s", $jsonValue);
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

/**
 * Metodo para buscar juegos en titulo, genero y plataformas desde un único input
 * @param mixed $busqueda Texto de búsqueda que se aplicará a título, género y plataformas
 * @return array|array{error: string} Devuelve un array con los juegos encontrados
 */
function buscarJuegosGlobal($busqueda)
{
    $mysqli = conectarBD();

    try {
        if (empty($busqueda)) {
            return ["success" => false, "message" => "Debe proporcionar un término de búsqueda"];
        }

        // Preparar el término de búsqueda para LIKE
        $searchTerm = "%" . $busqueda . "%";
        $searchJson = json_encode($busqueda);

        // Consulta que busca en título, género Y plataformas
        $sql = "SELECT id, titulo, genero, plataformas, imagen, descripcion 
                FROM games 
                WHERE titulo LIKE ? 
                   OR genero LIKE ? 
                   OR JSON_SEARCH(plataformas, 'one', ?) IS NOT NULL
                ORDER BY titulo ASC";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $busqueda);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $juegos = $resultado->fetch_all(MYSQLI_ASSOC);

        // Decodificar plataformas JSON
        foreach ($juegos as &$juego) {
            if (isset($juego['plataformas'])) {
                $juego['plataformas'] = json_decode($juego['plataformas'], true);
            }
        }

        return ["success" => true, "total" => count($juegos), "juegos" => $juegos];

    } catch (Exception $e) {
        return ["success" => false, "error" => "Error al buscar juegos: " . $e->getMessage()];
    } finally {
        $mysqli->close();
    }
}
?>