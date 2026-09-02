<?php
/**
 * api/admin.php - Gestión de usuarios (solo administradores)
 *
 * Endpoints (via ?action= o campo "action"):
 *   POST  create_user   { session_token, username, password }
 *   POST  delete_user   { session_token, user_id }
 *   GET   list_users    ?session_token=
 *
 * Respuesta: { success, data:{...}, error, ...campos promovidos }
 */

require_once __DIR__ . '/../config/utils.php';

$data   = getRequestData();
$action = getAction();
$pdo    = getDB();

switch ($action) {

    // -------------------------------------------------------------------
    case 'create_user':
        requireAdmin();

        $username = sanitize($data['username'] ?? ($data['usuario'] ?? ''));
        $password = (string) ($data['password'] ?? ($data['contrasena'] ?? ''));

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
        $ins  = $pdo->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, 0)");
        $ins->execute([$username, $hash]);

        respondJSON(true, [
            'user_id'  => (int) $pdo->lastInsertId(),
            'username' => $username,
        ]);
        break;

    // -------------------------------------------------------------------
    case 'delete_user':
        $admin  = requireAdmin();
        $userId = (int) ($data['user_id'] ?? 0);

        if ($userId <= 0) {
            respondJSON(false, [], 'user_id requerido');
        }
        if ($userId === $admin['id']) {
            respondJSON(false, [], 'No puedes eliminar tu propio usuario');
        }

        $stmt = $pdo->prepare("SELECT id, is_admin FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $target = $stmt->fetch();

        if (!$target) {
            http_response_code(404);
            respondJSON(false, [], 'Usuario no encontrado');
        }
        if ((int) $target['is_admin'] === 1) {
            respondJSON(false, [], 'No se puede eliminar a otro administrador');
        }

        $pdo->prepare("DELETE FROM sessions WHERE user_id = ?")->execute([$userId]);
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);

        respondJSON(true, ['message' => 'Usuario eliminado', 'user_id' => $userId]);
        break;

    // -------------------------------------------------------------------
    case 'list_users':
        requireAdmin();

        $rows = $pdo->query("
            SELECT id, username, is_admin, created_at
            FROM users
            ORDER BY created_at ASC, id ASC
        ")->fetchAll();

        $users = array_map(fn($r) => [
            'user_id'    => (int) $r['id'],
            'username'   => $r['username'],
            'is_admin'   => (int) $r['is_admin'] === 1,
            'created_at' => $r['created_at'],
        ], $rows);

        respondJSON(true, ['users' => $users]);
        break;

    // -------------------------------------------------------------------
    default:
        http_response_code(404);
        respondJSON(false, [], 'Acción no reconocida: usa create_user, delete_user o list_users');
}
