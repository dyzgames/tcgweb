<?php
/**
 * db.php - Conexión a SQLite y funciones comunes de base de datos
 *
 * Snippet base: docs/backend/FASE1_BACKEND.md (PASO 3 y PASO 4)
 *
 * Funciones:
 *   getDB()        - Retorna conexión PDO a SQLite (singleton)
 *   closeDB()      - Cierra la conexión
 *   createTables() - Crea las tablas automáticamente si no existen
 *   sanitize()     - Valida / limpia inputs
 */

require_once __DIR__ . '/config.php';

/**
 * Retorna una conexión PDO a la base de datos SQLite.
 * La conexión se reutiliza durante toda la petición (singleton).
 */
function getDB()
{
    global $__db_connection;

    if ($__db_connection instanceof PDO) {
        return $__db_connection;
    }

    // Asegurar que exista el directorio /db/
    $dir = dirname(DB_PATH);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    try {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'data'    => null,
            'error'   => 'No se pudo abrir la base de datos: ' . $e->getMessage(),
        ]);
        exit;
    }

    $__db_connection = $pdo;
    return $__db_connection;
}

/**
 * Cierra la conexión a la base de datos.
 */
function closeDB()
{
    global $__db_connection;
    $__db_connection = null;
}

/**
 * Crea todas las tablas si no existen.
 * SQL tomado literalmente de docs/backend/FASE1_BACKEND.md (PASO 4).
 */
function createTables()
{
    $pdo = getDB();

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username TEXT UNIQUE NOT NULL,
          password TEXT NOT NULL,
          is_admin BOOLEAN DEFAULT 0,
          created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS rooms (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          code TEXT UNIQUE NOT NULL,
          player1_id INTEGER,
          player2_id INTEGER,
          created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY(player1_id) REFERENCES users(id),
          FOREIGN KEY(player2_id) REFERENCES users(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sessions (
          token TEXT PRIMARY KEY,
          user_id INTEGER NOT NULL,
          created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
          expires_at DATETIME,
          FOREIGN KEY(user_id) REFERENCES users(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS signals (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          room_id INTEGER NOT NULL,
          from_player_id INTEGER,
          signal_type TEXT NOT NULL,
          data TEXT,
          created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY(room_id) REFERENCES rooms(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS alerts (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          room_id INTEGER NOT NULL,
          user_id INTEGER NOT NULL,
          created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY(room_id) REFERENCES rooms(id),
          FOREIGN KEY(user_id) REFERENCES users(id)
        )
    ");
}

/**
 * Limpia y valida un valor de entrada.
 * - Recorta espacios
 * - Elimina etiquetas HTML
 * - Funciona recursivamente sobre arrays
 */
function sanitize($value)
{
    if (is_array($value)) {
        return array_map('sanitize', $value);
    }
    if ($value === null) {
        return null;
    }
    return trim(strip_tags((string) $value));
}
