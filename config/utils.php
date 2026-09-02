<?php
/**
 * utils.php - Funciones helper compartidas por todos los endpoints
 *
 * Snippet base: docs/backend/FASE1_BACKEND.md (PASO 3)
 *
 * Funciones:
 *   respondJSON($success, $data, $error) - Devuelve respuesta JSON y termina
 *   validateSession($token)              - Valida un token de sesión
 *   getRequestData()                     - Parsea POST JSON + query string
 *   getAction()                          - Devuelve la acción solicitada
 *   requireAuth()                        - Exige sesión válida (o corta)
 *   requireAdmin()                       - Exige sesión de admin (o corta)
 *   generateToken()                      - Token aleatorio seguro
 *   generateRoomCode()                   - Código de sala único
 */

require_once __DIR__ . '/db.php';

// ---------------------------------------------------------------------------
// Cabeceras comunes + CORS (se ejecuta al incluir el fichero)
// ---------------------------------------------------------------------------
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . CORS_ALLOW_ORIGIN);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Crear las tablas si es la primera vez que se llama a la API.
createTables();

/**
 * Devuelve una respuesta JSON estandarizada y termina la ejecución.
 *
 * Formato:
 * {
 *   "success": true|false,
 *   "data": { ... },
 *   "error": "mensaje si hay error" | null,
 *   ...campos de data promovidos al nivel superior (compat. con src/lib/api.js)
 * }
 */
function respondJSON($success, $data = [], $error = null)
{
    if (!is_array($data)) {
        $data = ['value' => $data];
    }

    $payload = array_merge(
        ['success' => (bool) $success],
        $data,
        ['data' => $data, 'error' => $error]
    );

    if (!$success && http_response_code() === 200) {
        http_response_code(400);
    }

    echo json_encode($payload);
    exit;
}

/**
 * Lee y combina los datos de la petición:
 *   query string  ($_GET)
 *   form-encoded  ($_POST)
 *   cuerpo JSON   (php://input)
 * El cuerpo JSON tiene prioridad.
 */
function getRequestData()
{
    $raw  = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (!is_array($json)) {
        $json = [];
    }
    return array_merge($_GET, $_POST, $json);
}

/**
 * Devuelve la acción solicitada (query string ?action= o campo "action").
 */
function getAction()
{
    $data = getRequestData();
    return isset($data['action']) ? (string) $data['action'] : '';
}

/**
 * Valida un token de sesión.
 *
 * @return array|false  Fila del usuario (id, username, is_admin) o false.
 */
function validateSession($token)
{
    $token = trim((string) $token);
    if ($token === '') {
        return false;
    }

    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.is_admin, s.expires_at
        FROM sessions s
        JOIN users u ON u.id = s.user_id
        WHERE s.token = ?
    ");
    $stmt->execute([$token]);
    $row = $stmt->fetch();

    if (!$row) {
        return false;
    }

    // Comprobar expiración
    if (!empty($row['expires_at']) && strtotime($row['expires_at']) < time()) {
        $del = $pdo->prepare("DELETE FROM sessions WHERE token = ?");
        $del->execute([$token]);
        return false;
    }

    return [
        'id'       => (int) $row['id'],
        'username' => $row['username'],
        'is_admin' => (int) $row['is_admin'] === 1,
    ];
}

/**
 * Exige una sesión válida. Corta con error si no la hay.
 * @return array Usuario autenticado.
 */
function requireAuth()
{
    $data  = getRequestData();
    $token = $data['session_token'] ?? ($data['token'] ?? '');
    $user  = validateSession($token);

    if (!$user) {
        http_response_code(401);
        respondJSON(false, [], 'Sesión no válida o expirada');
    }
    return $user;
}

/**
 * Exige una sesión de administrador. Corta con error si no lo es.
 * @return array Usuario admin autenticado.
 */
function requireAdmin()
{
    $user = requireAuth();
    if (empty($user['is_admin'])) {
        http_response_code(403);
        respondJSON(false, [], 'Se requieren permisos de administrador');
    }
    return $user;
}

/**
 * Genera un token aleatorio criptográficamente seguro (64 hex chars).
 */
function generateToken()
{
    return bin2hex(random_bytes(32));
}

/**
 * Genera un código de sala único (mayúsculas + dígitos, sin caracteres ambiguos).
 */
function generateRoomCode()
{
    $pdo      = getDB();
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // sin I, O, 0, 1
    do {
        $code = '';
        for ($i = 0; $i < ROOM_CODE_LENGTH; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        $stmt = $pdo->prepare("SELECT 1 FROM rooms WHERE code = ?");
        $stmt->execute([$code]);
    } while ($stmt->fetchColumn());

    return $code;
}
