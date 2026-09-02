<?php
/**
 * config.php - Constantes y configuración global
 *
 * Snippet base: docs/backend/FASE1_BACKEND.md (PASO 3)
 */

// ==== Rutas ====
// La raíz del proyecto es el directorio padre de /config/
define('BASE_PATH', dirname(__DIR__));

// Ruta al fichero SQLite (se crea automáticamente en setup / primera petición)
define('DB_PATH', BASE_PATH . '/db/game.sqlite3');

// ==== Credenciales admin por defecto (las usa setup.php) ====
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'admin123');

// ==== Sesiones ====
// Duración de una sesión en segundos (86400 = 24 h)
define('SESSION_TIMEOUT', 86400);

// ==== Longitud del código de sala ====
define('ROOM_CODE_LENGTH', 6);

// ==== Servidores STUN públicos (Google) ====
define('STUN_SERVERS', [
    ['urls' => 'stun:stun.l.google.com:19302'],
    ['urls' => 'stun:stun1.l.google.com:19302'],
    ['urls' => 'stun:stun2.l.google.com:19302'],
    ['urls' => 'stun:stun3.l.google.com:19302'],
    ['urls' => 'stun:stun4.l.google.com:19302'],
]);

// ==== CORS ====
// En producción cámbialo por tu dominio: 'https://tudominio.com'
define('CORS_ALLOW_ORIGIN', '*');

// Zona horaria (para expiración de sesiones coherente)
date_default_timezone_set('UTC');
