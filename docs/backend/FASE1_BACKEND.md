# 🔧 FASE 1: BACKEND PHP + SETUP INICIAL

**Objetivo:** Crear endpoints PHP, BD SQLite, y estructura base de proyecto  
**Duración:** 2-3 sesiones en Claude Code  
**Resultado:** Backend funcional que puede testearse con Postman/cURL

---

## 📝 TAREAS DE FASE 1

### Tarea 1.1: Crear `package.json` con todas las dependencias
**Para:** Frontend (Svelte, Vite, Tailwind, Testing)

```json
{
  "name": "game-webrtc",
  "version": "1.0.0",
  "type": "module",
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview",
    "test": "vitest",
    "test:ui": "vitest --ui",
    "test:run": "vitest run"
  },
  "devDependencies": {
    "@sveltejs/vite-plugin-svelte": "^3.0.0",
    "@testing-library/svelte": "^4.0.0",
    "@vitest/ui": "^1.0.0",
    "autoprefixer": "^10.4.0",
    "happy-dom": "^12.0.0",
    "postcss": "^8.4.0",
    "tailwindcss": "^3.4.0",
    "vite": "^5.0.0",
    "vitest": "^1.0.0"
  },
  "dependencies": {
    "simple-peer": "^9.11.0"
  }
}
```

**📌 Instrucción para Claude Code:**
```
Crea el archivo package.json en la raíz del proyecto con el contenido anterior.
```

---

### Tarea 1.2: Crear estructura `/api/` con 5 endpoints PHP

**Archivos a crear:**
```
api/
├── auth.php         # Login, registro, logout
├── rooms.php        # Crear partida, unirse, listar
├── signal.php       # Señalización WebRTC (OFFER/ANSWER/ICE)
├── alerts.php       # Enviar alertas
└── admin.php        # Crear usuarios (solo admin)
```

**Más abajo están los snippets de cada archivo.**

---

### Tarea 1.3: Crear configuración compartida

**Archivos:**
```
config.php          # Constantes y configuración
db.php              # Conexión a SQLite y funciones comunes
utils.php           # Funciones helper
setup.php           # EJECUTAR UNA SOLA VEZ al hacer deploy
```

---

### Tarea 1.4: Crear `/public/` con archivos estáticos

```
public/
├── alert-sound.mp3    # Sonido de alerta (puedes crear uno dummy)
└── favicon.ico        # Favicon
```

---

### Tarea 1.5: Crear `.env.local` y `.env.production`

Esto es para que Vite sepa a dónde apuntar los endpoints.

---

## 🎯 PASOS PRÁCTICOS (Abre Claude Code y sigue esto)

### PASO 1: Prepara el directorio
En la terminal de VS Code:
```bash
cd game-webrtc
npm init -y
```

Esto crea un `package.json` básico. **Luego le pides a Claude que lo reemplace** con el de arriba.

---

### PASO 2: Pídele a Claude Code que cree los 5 endpoints PHP

**Dile esto en Claude Code:**

```
Consulta la sección "PSEUDO-CÓDIGO BACKEND (PHP)" del DOCUMENTO_TECNICO_v2.md

Crea estos 5 archivos en /api/:

1. api/auth.php - Endpoints:
   - POST /login (usuario, contraseña)
   - POST /register (usuario, contraseña, es_admin=0)
   - POST /logout

2. api/rooms.php - Endpoints:
   - POST /create_room (session_token)
   - POST /join_room (code, session_token)
   - GET /room_info (room_id)
   - POST /leave_room (room_id, session_token)

3. api/signal.php - Endpoints:
   - POST /send_signal (room_id, from_player, signal_type, data)
   - GET /get_signals (room_id, since_id)

4. api/alerts.php - Endpoints:
   - POST /send_alert (room_id, session_token)

5. api/admin.php - Endpoints:
   - POST /create_user (session_token, usuario, password)
   - GET /list_users (session_token)

Todos retornan JSON con formato:
{
  "success": true/false,
  "data": {},
  "error": "mensaje si hay error"
}

Incluye validación básica y manejo de errores.
```

Claude Code creará los archivos automáticamente.

---

### PASO 3: Crea la configuración compartida

**Dile a Claude:**

```
Crea estos 3 archivos de configuración:

1. config.php - Constantes:
   - DB_PATH = 'db/game.sqlite3'
   - ADMIN_USER = 'admin'
   - ADMIN_PASS = 'admin123'
   - STUN_SERVERS = array de servidores públicos
   - SESSION_TIMEOUT = 86400 segundos

2. db.php - Funciones:
   - getDB() - Retorna conexión PDO a SQLite
   - closeDB() - Cierra conexión
   - createTables() - Crea tablas automáticamente si no existen
   - sanitize() - Valida inputs

3. utils.php - Funciones helper:
   - respondJSON($success, $data, $error)
   - validateSession($token)
   - getRequestData() - Parsea POST JSON
```

---

### PASO 4: Crea las tablas de BD

**Dile a Claude:**

```
En config.php, crea una función createTables() que ejecute este SQL:

CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT UNIQUE NOT NULL,
  password TEXT NOT NULL,
  is_admin BOOLEAN DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rooms (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  code TEXT UNIQUE NOT NULL,
  player1_id INTEGER,
  player2_id INTEGER,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(player1_id) REFERENCES users(id),
  FOREIGN KEY(player2_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS sessions (
  token TEXT PRIMARY KEY,
  user_id INTEGER NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  expires_at DATETIME,
  FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS signals (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  room_id INTEGER NOT NULL,
  from_player_id INTEGER,
  signal_type TEXT NOT NULL,
  data TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(room_id) REFERENCES rooms(id)
);

CREATE TABLE IF NOT EXISTS alerts (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  room_id INTEGER NOT NULL,
  user_id INTEGER NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(room_id) REFERENCES rooms(id),
  FOREIGN KEY(user_id) REFERENCES users(id)
);
```

---

### PASO 5: Crea setup.php

**Este archivo se ejecuta UNA SOLA VEZ en producción.**

```php
<?php
// setup.php - EJECUTAR UNA SOLA VEZ

require 'config.php';
require 'db.php';

// Crear tablas
createTables();

// Crear usuario admin
$pdo = getDB();
$username = 'admin';
$password = password_hash('admin123', PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, 1)");
    $stmt->execute([$username, $password]);
    echo "✓ Tablas creadas y usuario admin/admin123 creado\n";
} catch (PDOException $e) {
    echo "✓ Admin ya existe o error: " . $e->getMessage() . "\n";
}

closeDB();

// Ahora borra este archivo manualmente en el servidor
?>
```

**⚠️ IMPORTANTE:** Después de ejecutar en producción, **BORRA este archivo.**

---

### PASO 6: Crea .env files

**`.env.local` (Desarrollo):**
```
VITE_API_URL=http://localhost:8000/api
```

**`.env.production` (Producción - Servidor OVH):**
```
VITE_API_URL=/api
```

---

## ✅ CÓMO TESTEAR FASE 1

### Opción A: Con PHP built-in server

```bash
cd game-webrtc
php -S localhost:8000
```

Luego en otra terminal, prueba los endpoints:

```bash
# Test login
curl -X POST http://localhost:8000/api/auth.php \
  -H "Content-Type: application/json" \
  -d '{"action":"login","usuario":"admin","contrasena":"admin123"}'

# Debe retornar algo como:
# {"success":true,"data":{"session_token":"xxx","user_id":1},"error":null}
```

### Opción B: Con Postman (si lo tienes)

1. Abre Postman
2. New Request
3. POST a `http://localhost:8000/api/auth.php`
4. Body > JSON:
```json
{
  "action": "login",
  "usuario": "admin",
  "contrasena": "admin123"
}
```

---

## 🔍 Verificación de Fase 1

**Checklist antes de pasar a Fase 2:**

- [ ] `npm install` completó sin errores
- [ ] `/api/` tiene 5 archivos PHP
- [ ] `/config/` tiene config.php, db.php, utils.php
- [ ] PHP server corre en `localhost:8000`
- [ ] Login endpoint retorna JSON válido
- [ ] BD SQLite se crea automáticamente (`db/game.sqlite3`)
- [ ] Usuario admin/admin123 funciona
- [ ] Endpoints POST retornan `{"success":true/false, ...}`

---

## 📌 PRÓXIMO PASO

Cuando termines Fase 1:

1. Confirma que todos los tests del checklist pasan
2. Di a Claude: **"Fase 1 completada. Voy con Fase 2: Frontend Setup"**
3. Abre: `docs/frontend/FASE2_SETUP.md`

---

## 🆘 Troubleshooting

**Error: "Cannot find module 'simple-peer'"**
```bash
npm install
```

**Error: "SQLITE_CANTOPEN"**
- Asegúrate que existe `/db/` directory
- Dale permisos: `chmod 755 db/`

**Error: "Headers already sent"**
- Revisa que no haya espacios en blanco antes de `<?php` en los archivos

