<?php
/**
 * api/rooms.php - Gestión de salas / partidas
 *
 * Endpoints (via ?action= o campo "action"):
 *   POST  create    { session_token }
 *   POST  join      { session_token, code }
 *   POST  spectate  { session_token, code }
 *   POST  leave     { session_token, room_id }
 *   GET   info      { room_id }            (alias: room_info)
 *
 * Respuesta: { success, data:{...}, error, ...campos promovidos }
 */

require_once __DIR__ . '/../config/utils.php';

$data   = getRequestData();
$action = getAction();
$pdo    = getDB();

/**
 * Devuelve la lista de jugadores de una sala (id + username).
 */
function roomPlayers(PDO $pdo, array $room)
{
    $players = [];
    foreach (['player1_id', 'player2_id'] as $slot) {
        if (!empty($room[$slot])) {
            $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
            $stmt->execute([$room[$slot]]);
            if ($u = $stmt->fetch()) {
                $players[] = ['user_id' => (int) $u['id'], 'username' => $u['username']];
            }
        }
    }
    return $players;
}

switch ($action) {

    // -------------------------------------------------------------------
    case 'create':
    case 'create_room':
        $user = requireAuth();

        $code = generateRoomCode();
        $ins  = $pdo->prepare("INSERT INTO rooms (code, player1_id) VALUES (?, ?)");
        $ins->execute([$code, $user['id']]);
        $roomId = (int) $pdo->lastInsertId();

        respondJSON(true, [
            'room_id'      => $roomId,
            'code'         => $code,
            'your_player_index' => 0,
            'stun_servers' => STUN_SERVERS,
        ]);
        break;

    // -------------------------------------------------------------------
    case 'join':
    case 'join_room':
        $user = requireAuth();
        $code = strtoupper(sanitize($data['code'] ?? ''));

        if ($code === '') {
            respondJSON(false, [], 'Código de sala requerido');
        }

        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE code = ?");
        $stmt->execute([$code]);
        $room = $stmt->fetch();

        if (!$room) {
            http_response_code(404);
            respondJSON(false, [], 'Sala no encontrada');
        }

        $playerIndex = null;

        // ¿Ya está dentro?
        if ((int) $room['player1_id'] === $user['id']) {
            $playerIndex = 0;
        } elseif ((int) $room['player2_id'] === $user['id']) {
            $playerIndex = 1;
        } elseif (empty($room['player1_id'])) {
            $upd = $pdo->prepare("UPDATE rooms SET player1_id = ? WHERE id = ?");
            $upd->execute([$user['id'], $room['id']]);
            $room['player1_id'] = $user['id'];
            $playerIndex = 0;
        } elseif (empty($room['player2_id'])) {
            $upd = $pdo->prepare("UPDATE rooms SET player2_id = ? WHERE id = ?");
            $upd->execute([$user['id'], $room['id']]);
            $room['player2_id'] = $user['id'];
            $playerIndex = 1;
        } else {
            respondJSON(false, [], 'La sala está llena');
        }

        $others = array_values(array_filter(
            roomPlayers($pdo, $room),
            fn($p) => $p['user_id'] !== $user['id']
        ));

        respondJSON(true, [
            'room_id'            => (int) $room['id'],
            'code'              => $room['code'],
            'your_player_index' => $playerIndex,
            'other_players'     => $others,
            'stun_servers'      => STUN_SERVERS,
        ]);
        break;

    // -------------------------------------------------------------------
    case 'spectate':
        $user = requireAuth();
        $code = strtoupper(sanitize($data['code'] ?? ''));

        if ($code === '') {
            respondJSON(false, [], 'Código de sala requerido');
        }

        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE code = ?");
        $stmt->execute([$code]);
        $room = $stmt->fetch();

        if (!$room) {
            http_response_code(404);
            respondJSON(false, [], 'Sala no encontrada');
        }

        respondJSON(true, [
            'room_id'      => (int) $room['id'],
            'code'         => $room['code'],
            'role'         => 'spectator',
            'players'      => roomPlayers($pdo, $room),
            'stun_servers' => STUN_SERVERS,
        ]);
        break;

    // -------------------------------------------------------------------
    case 'leave':
    case 'leave_room':
        $user   = requireAuth();
        $roomId = (int) ($data['room_id'] ?? 0);

        if ($roomId <= 0) {
            respondJSON(false, [], 'room_id requerido');
        }

        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$roomId]);
        $room = $stmt->fetch();

        if (!$room) {
            respondJSON(true, ['message' => 'La sala ya no existe']);
        }

        if ((int) $room['player1_id'] === $user['id']) {
            $pdo->prepare("UPDATE rooms SET player1_id = NULL WHERE id = ?")->execute([$roomId]);
        } elseif ((int) $room['player2_id'] === $user['id']) {
            $pdo->prepare("UPDATE rooms SET player2_id = NULL WHERE id = ?")->execute([$roomId]);
        }

        // Si la sala queda vacía, limpiarla junto con sus señales / alertas
        $stmt = $pdo->prepare("SELECT player1_id, player2_id FROM rooms WHERE id = ?");
        $stmt->execute([$roomId]);
        $room = $stmt->fetch();
        if ($room && empty($room['player1_id']) && empty($room['player2_id'])) {
            $pdo->prepare("DELETE FROM signals WHERE room_id = ?")->execute([$roomId]);
            $pdo->prepare("DELETE FROM alerts  WHERE room_id = ?")->execute([$roomId]);
            $pdo->prepare("DELETE FROM rooms   WHERE id = ?")->execute([$roomId]);
        }

        respondJSON(true, ['message' => 'Has salido de la sala']);
        break;

    // -------------------------------------------------------------------
    case 'info':
    case 'room_info':
        $roomId = (int) ($data['room_id'] ?? 0);
        if ($roomId <= 0) {
            respondJSON(false, [], 'room_id requerido');
        }

        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$roomId]);
        $room = $stmt->fetch();

        if (!$room) {
            http_response_code(404);
            respondJSON(false, [], 'Sala no encontrada');
        }

        respondJSON(true, [
            'room_id'    => (int) $room['id'],
            'code'       => $room['code'],
            'players'    => roomPlayers($pdo, $room),
            'created_at' => $room['created_at'],
        ]);
        break;

    // -------------------------------------------------------------------
    default:
        http_response_code(404);
        respondJSON(false, [], 'Acción no reconocida: usa create, join, spectate, leave o info');
}
