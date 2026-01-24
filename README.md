# 🎮 GameFest - Backend API

API REST para la plataforma del festival de videojuegos GameFest. Desarrollada con PHP, MySQL y arquitectura basada en sesiones.

## 📋 Tabla de contenidos

- [Descripción](#descripción)
- [Tecnologías](#tecnologías)
- [Estructura del proyecto](#estructura-del-proyecto)
- [Instalación](#instalación)
- [Endpoints de la API](#endpoints-de-la-api)
- [Autenticación](#autenticación)
- [Ejemplos de uso](#ejemplos-de-uso)

---

## 📖 Descripción

GameFest es una API REST que permite:

- Consultar catálogo de videojuegos
- Consultar programación de eventos del festival
- Sistema de autenticación con roles (USER/ADMIN)
- Inscripción y gestión de asistencia a eventos
- Creación de eventos (solo administradores)

---

## 🛠️ Tecnologías

- **PHP** 7.4+
- **MySQL** 8.0+
- **Apache** con mod_rewrite
- **Arquitectura REST** con sesiones PHP

---

## 📁 Estructura del proyecto

```
gamefest-backend/
├── auth/
│   ├── login.php           # POST - Inicio de sesión
│   ├── logout.php          # POST - Cerrar sesión
│   └── register.php        # POST - Registro de usuarios
├── events/
│   ├── createEvent.php     # POST - Crear evento (ADMIN)
│   ├── detailEvent.php     # GET - Detalle de evento
│   ├── events.php          # GET - Listado paginado
│   ├── signup.php          # POST - Inscribirse a evento
│   └── unsignup.php        # DELETE - Cancelar inscripción
├── games/
│   ├── datailGame.php      # GET - Detalle de juego
│   └── games.php           # GET - Listado de juegos
├── users/
│   ├── loggedUser.php      # GET - Datos usuario actual
│   └── myEvents.php        # GET - Eventos del usuario
├── .htaccess               # Configuración de rutas
├── functions.php           # Funciones compartidas
└── README.md
```

---

## 🚀 Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/gamefest-backend.git
cd gamefest-backend
```

### 2. Configurar base de datos

Edita `functions.php` con tus credenciales:

```php
define("SERVIDOR", "localhost");
define("USUARIO", "root");
define("CLAVE", "");
define("BBDD", "gamefest");
```

### 3. Importar base de datos

```bash
mysql -u root -p gamefest < database/gamefest.sql
```

### 4. Configurar Apache

Asegúrate de tener habilitado `mod_rewrite`:

```bash
sudo a2enmod rewrite
sudo service apache2 restart
```

### 5. Configurar `.htaccess`

Verifica que `RewriteBase` coincida con tu ruta:

```apache
RewriteBase /gamefest-backend/
```

---

## 📡 Endpoints de la API

### 🔓 Endpoints públicos (no requieren autenticación)

#### **Juegos**

| Método | Endpoint      | Descripción                    |
| ------ | ------------- | ------------------------------ |
| GET    | `/games`      | Listado de todos los juegos    |
| GET    | `/games/{id}` | Detalle de un juego específico |

**Ejemplo:**

```bash
GET /games
GET /games/5
```

**Respuesta:**

```json
{
  "id": 5,
  "titulo": "The Legend of Zelda",
  "genero": "Aventura",
  "plataformas": ["Nintendo Switch", "Wii U"],
  "imagen": "zelda.jpg",
  "descripcion": "Juego de aventuras..."
}
```

---

#### **Eventos**

| Método | Endpoint         | Descripción                             |
| ------ | ---------------- | --------------------------------------- |
| GET    | `/events?page=1` | Listado paginado (9 eventos por página) |
| GET    | `/events/{id}`   | Detalle de un evento específico         |

**Parámetros opcionales:**

- `page` - Número de página (default: 1)
- `tipo` - Filtrar por tipo de evento
- `fecha` - Filtrar por fecha (YYYY-MM-DD)
- `disponibles` - Solo eventos con plazas (true/false)

**Ejemplos:**

```bash
GET /events?page=1
GET /events?page=2&disponibles=true
GET /events?tipo=Taller
GET /events/10
```

**Respuesta:**

```json
[
  {
    "id": 10,
    "titulo": "Torneo de Smash Bros",
    "tipo": "Competición",
    "fecha": "2026-02-15",
    "hora": "16:00:00",
    "plazasLibres": 32,
    "imagen": "smash.jpg",
    "descripcion": "Torneo eliminatorio...",
    "inscrito": false
  }
]
```

---

### 🔐 Endpoints privados (requieren autenticación)

#### **Autenticación**

| Método | Endpoint         | Descripción             | Rol        |
| ------ | ---------------- | ----------------------- | ---------- |
| POST   | `/auth/register` | Registrar nuevo usuario | -          |
| POST   | `/auth/login`    | Iniciar sesión          | -          |
| POST   | `/auth/logout`   | Cerrar sesión           | USER/ADMIN |

**POST `/auth/register`**

```json
{
  "username": "johndoe",
  "email": "john@example.com",
  "password": "password123"
}
```

**Respuesta:**

```json
{
  "success": true,
  "message": "Usuario registrado correctamente"
}
```

**POST `/auth/login`**

```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Respuesta:**

```json
{
  "success": true,
  "message": "Inicio de sesión exitoso",
  "user": {
    "id": 1,
    "username": "johndoe",
    "email": "john@example.com",
    "role": "USER"
  }
}
```

---

#### **Usuario**

| Método | Endpoint           | Descripción                   | Rol        |
| ------ | ------------------ | ----------------------------- | ---------- |
| GET    | `/users/me`        | Datos del usuario actual      | USER/ADMIN |
| GET    | `/users/me/events` | Eventos inscritos del usuario | USER/ADMIN |

**GET `/users/me`**

**Respuesta:**

```json
{
  "success": true,
  "user": {
    "id": 1,
    "username": "johndoe",
    "email": "john@example.com",
    "role": "USER"
  }
}
```

**GET `/users/me/events`**

**Respuesta:**

```json
[
  {
    "id": 10,
    "titulo": "Torneo de Smash Bros",
    "fecha": "2026-02-15",
    "hora": "16:00:00",
    "imagen": "smash.jpg",
    "descripcion": "Torneo eliminatorio..."
  }
]
```

---

#### **Gestión de inscripciones**

| Método | Endpoint              | Descripción             | Rol        |
| ------ | --------------------- | ----------------------- | ---------- |
| POST   | `/events/{id}/signup` | Inscribirse a un evento | USER/ADMIN |
| DELETE | `/events/{id}/signup` | Cancelar inscripción    | USER/ADMIN |

**POST `/events/10/signup`**

**Respuesta:**

```json
{
  "success": true,
  "message": "Te has inscrito correctamente"
}
```

**Errores posibles:**

```json
{
  "success": false,
  "message": "Ya estás inscrito en este evento"
}
```

```json
{
  "success": false,
  "message": "No hay plazas disponibles"
}
```

---

#### **Gestión de eventos (ADMIN)**

| Método | Endpoint  | Descripción        | Rol   |
| ------ | --------- | ------------------ | ----- |
| POST   | `/events` | Crear nuevo evento | ADMIN |

**POST `/events`**

```json
{
  "titulo": "Workshop de Unity",
  "tipo": "Taller",
  "fecha": "2026-03-01",
  "hora": "10:00:00",
  "plazasLibres": 30,
  "imagen": "unity.jpg",
  "descripcion": "Aprende a crear videojuegos con Unity"
}
```

**Respuesta:**

```json
{
  "success": true,
  "message": "Evento creado correctamente",
  "id": 25
}
```

---

## 🔒 Autenticación

La API utiliza **sesiones PHP** para la autenticación. Las cookies de sesión se envían automáticamente con cada petición.

### Flujo de autenticación:

1. El usuario hace login → Se crea una sesión PHP
2. Las peticiones posteriores incluyen la cookie de sesión
3. El backend verifica la sesión con `estaAutenticado()` y `esAdmin()`

### Headers requeridos:

```
Content-Type: application/json
```

### Cookies (automáticas):

```
PHPSESSID=abc123...
```

---

## 💡 Ejemplos de uso

### JavaScript (Fetch API)

```javascript
// Login
const login = async (email, password) => {
  const response = await fetch("/auth/login", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "include", // Importante para cookies
    body: JSON.stringify({ email, password }),
  });
  return await response.json();
};

// Obtener eventos
const getEvents = async (page = 1) => {
  const response = await fetch(`/events?page=${page}`, {
    credentials: "include",
  });
  return await response.json();
};

// Inscribirse a evento
const signupEvent = async (eventId) => {
  const response = await fetch(`/events/${eventId}/signup`, {
    method: "POST",
    credentials: "include",
  });
  return await response.json();
};

// Crear evento (ADMIN)
const createEvent = async (eventData) => {
  const response = await fetch("/events", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "include",
    body: JSON.stringify(eventData),
  });
  return await response.json();
};
```

### Vue.js (Axios)

```javascript
import axios from 'axios';

axios.defaults.baseURL = 'http://localhost/gamefest-backend';
axios.defaults.withCredentials = true; // Para sesiones

// Login
async login(email, password) {
  const { data } = await axios.post('/auth/login', { email, password });
  return data;
}

// Obtener mis eventos
async getMyEvents() {
  const { data } = await axios.get('/users/me/events');
  return data;
}
```

---

## 🔧 Funciones principales (functions.php)

### Gestión de sesiones

- `iniciarSesion()` - Inicia la sesión PHP
- `logout()` - Destruye la sesión
- `estaAutenticado()` - Verifica si hay sesión activa
- `esAdmin()` - Verifica si el usuario es ADMIN
- `obtenerUsuarioActual()` - Obtiene ID del usuario logueado

### Gestión de usuarios

- `registrarUsuario($username, $email, $password)`
- `loginUsuario($email, $password)`

### Gestión de eventos

- `obtenerEventos($page, $id = null)`
- `crearEvento($title, $type, $date, $hour, $slots, $image, $desc)`
- `inscribirse($eventId)`
- `desinscribirse($eventId)`
- `obtenerMisEventos($userId = null)`
- `verificarInscrito($eventId, $userId = null)`

### Gestión de juegos

- `obtenerJuegos($id = null)`

### Filtros

- `mostrarEventosConPlazasLibres($page)`
- `mostrarEventosPorFecha($fecha, $page)`
- `mostrarEventosPorTipo($tipo, $page)`
- `mostrarJuegosPorGenero($genero)`

### Utilidades

- `conectarBD()` - Conexión a MySQL
- `enviarJSON($data, $codigo)` - Respuesta JSON estandarizada

---

## 📌 Códigos de respuesta HTTP

| Código | Significado                                 |
| ------ | ------------------------------------------- |
| 200    | OK - Operación exitosa                      |
| 201    | Created - Recurso creado                    |
| 400    | Bad Request - Datos inválidos               |
| 401    | Unauthorized - No autenticado               |
| 403    | Forbidden - Sin permisos                    |
| 404    | Not Found - Recurso no encontrado           |
| 405    | Method Not Allowed - Método HTTP incorrecto |

---

## 🐛 Troubleshooting

### Error: "Método no permitido"

✅ Verifica que usas el método HTTP correcto (GET/POST/DELETE)

### Error: "No autenticado"

✅ Asegúrate de:

- Haber hecho login primero
- Incluir `credentials: 'include'` en fetch
- Tener cookies habilitadas

### Error: "Evento no encontrado"

✅ Verifica que el ID existe en la base de datos

### Redirección 404

✅ Comprueba:

- Que `.htaccess` está en la raíz del proyecto
- Que `mod_rewrite` está habilitado
- Que `RewriteBase` es correcto

---

## 👥 Roles y permisos

| Acción                | USER | ADMIN |
| --------------------- | ---- | ----- |
| Ver juegos            | ✅   | ✅    |
| Ver eventos           | ✅   | ✅    |
| Inscribirse a eventos | ✅   | ✅    |
| Ver mis eventos       | ✅   | ✅    |
| Crear eventos         | ❌   | ✅    |

---

## 📝 Licencia

Este proyecto es parte del Reto 2 de GameFest - Elorrieta 2026

---

## ✨ Autores

Desarrollado por Alexis Guaño, Aingeru Lazaro, Levan Sabashvili , Endika Ordiano
Fecha: Enero 2026
