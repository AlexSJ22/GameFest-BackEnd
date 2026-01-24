# 🎮 GameFest - Backend API

API REST para el festival de videojuegos GameFest. Desarrollada con PHP, MySQL y sesiones.

---

## 📖 ¿Qué hace?

- Ver catálogo de videojuegos
- Ver programación de eventos
- Registrarse e iniciar sesión
- Inscribirse a eventos
- Crear eventos (solo administradores)

---

## 🛠️ Tecnologías

- PHP 7.4+
- MySQL 8.0+
- Apache con mod_rewrite

---

## 📁 Estructura

```
gamefest-backend/
├── auth/           # Login, logout, registro
├── events/         # Eventos (crear, listar, inscribirse)
├── games/          # Videojuegos (listar, detalle)
├── users/          # Datos del usuario
├── .htaccess       # Configuración de rutas
└── functions.php   # Funciones compartidas
```

---

## 📡 Endpoints

### 🔓 Públicos (sin login)

```
GET  /games              # Todos los juegos
GET  /games/{id}         # Detalle de un juego
GET  /events?page=1      # Eventos (9 por página)
GET  /events/{id}        # Detalle de un evento
```

### 🔐 Privados (requieren login)

```
POST   /auth/register          # Registrar usuario
POST   /auth/login             # Iniciar sesión
POST   /auth/logout            # Cerrar sesión

GET    /users/me               # Datos del usuario actual
GET    /users/me/events        # Mis eventos

POST   /events/{id}/signup     # Inscribirse
DELETE /events/{id}/signup     # Desinscribirse

POST   /events                 # Crear evento (solo ADMIN)
```

---

## 🔒 Autenticación

La API usa **sesiones PHP**:

1. Haces login → Se crea una sesión
2. Las siguientes peticiones incluyen la cookie de sesión automáticamente
3. El backend verifica si estás logueado

---

## 👥 Roles

| Acción        | USER | ADMIN |
| ------------- | ---- | ----- |
| Ver juegos    | ✅   | ✅    |
| Ver eventos   | ✅   | ✅    |
| Inscribirse   | ✅   | ✅    |
| Crear eventos | ❌   | ✅    |

---

## 🐛 Problemas comunes

**Error 404 en todas las rutas**

- Verifica que `.htaccess` esté en la raíz
- Comprueba que `mod_rewrite` esté habilitado

**"No autenticado"**

- Usa `credentials: 'include'` en fetch
- Verifica que hiciste login primero

**Conexión a BD falla**

- Revisa las credenciales en `functions.php`
- Comprueba que MySQL esté corriendo

---

## ✨ Autores

Alexis Guaño, Aingeru Lazaro, Levan Sabashvili, Endika Ordiano  
**Reto 2 GameFest - Elorrieta 2026**
