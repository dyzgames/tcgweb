<?php
/**
 * api/auth.php - Autenticación
 *
 * Endpoints (via ?action= o campo "action"):
 *   POST  login     { username|usuario, password|contrasena }
 *   POST  register  { username, password, is_admin? }
 *   POST  logout    { session_token }
 *
 * Respuesta: { success, data:{...}, error, ...campos promovidos }
 */

require_once __DIR__ . '/../config/utils.php';

$data   = getRequestData();
$action = getAction();
$pdo    = getDB();

switch ($action) {

    // -------------------------------------------------------------------
    case 'login':
        $username = sanitize($data['username'] ?? ($data['usuario'] ?? ''));
        $password = (string) ($data['password'] ?? ($data['contrasena'] ?? ''));

        if ($username === '' || $password === '') {
            respondJSON(false, [], 'Usuario y contraseña requeridos');
        }

        $stmt = $pdo->prepare("SELECT id, username, password, is_admin FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            http_response_code(401);
            respondJSON(false, [], 'Credenciales incorrectas');
        }

        // Crear sesión
        $token     = generateToken();
        $expiresAt = date('Y-m-d H:i:s', time() + SESSION_TIMEOUT);

        $ins = $pdo->prepare("INSERT INTO sessions (token, user_id, expires_at) VALUES (?, ?, ?)");
        $ins->execute([$token, $user['id'], $expiresAt]);

        respondJSON(true, [
            'session_token' => $token,
            'user_id'       => (int) $user['id'],
            'username'      => $user['username'],
            'is_admin'      => (int) $user['is_admin'] === 1,
            'expires_at'    => $expiresAt,
        ]);
        break;

    // -------------------------------------------------------------------
    case 'register':
        $username = sanitize($data['username'] ?? ($data['usuario'] ?? ''));
        $password = (string) ($data['password'] ?? ($data['contrasena'] ?? ''));
        $isAdmin  = !empty($data['is_admin']) ? 1 : 0;

        if ($username === '' || $password === '') {
            respondJSON(false, [], 'Usuario y contraseña requeridos');
        }
        if (strlen($username) < 3) {
            respondJSON(false, [], 'El usuario debe tener al menos 3 caracteres');
        }
        if (strlen($password) < 4) {
            respondJSON(false, [], 'La contraseña debe tener al menos 4 caracteres');
        }

        $stmt = $pdo->prepare("SELECT 1 FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn()) {
            respondJSON(false, [], 'El usuario ya existe');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $ins  = $pdo->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)");
        $ins->execute([$username, $hash, $isAdmin]);

        respondJSON(true, [
            'user_id'  => (int) $pdo->lastInsertId(),
            'username' => $username,
            'is_admin' => $isAdmin === 1,
        ]);
        break;

    // -------------------------------------------------------------------
    case 'logout':
        $token = trim((string) ($data['session_token'] ?? ($data['token'] ?? '')));
        if ($token !== '') {
            $del = $pdo->prepare("DELETE FROM sessions WHERE token = ?");
            $del->execute([$token]);
        }
        respondJSON(true, ['message' => 'Sesión cerrada']);
        break;

    // -------------------------------------------------------------------
    default:
        http_response_code(404);
        respondJSON(false, [], 'Acción no reconocida: usa login, register o logout');
}
