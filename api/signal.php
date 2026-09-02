<?php
/**
 * api/signal.php - Señalización WebRTC (OFFER / ANSWER / ICE candidate)
 *
 * Endpoints (via ?action= o campo "action"):
 *   POST  send           { session_token, room_id, to_user_id?, signal_type, signal_data }
 *   GET   poll           ?session_token=&room_id=&since_id=0
 *   POST  mark_consumed  { session_token, signal_ids: [] }
 *
 * La sala es de 2 jugadores: cada cliente recibe todas las señales de la
 * sala que NO haya emitido él mismo (from_player_id != usuario actual).
 *
 * Respuesta: { success, data:{...}, error, ...campos promovidos }
 */

require_once __DIR__ . '/../config/utils.php';

$data   = getRequestData();
$action = getAction();
$pdo    = getDB();

/**
 * Comprueba que el usuario pertenece a la sala (jugador o espectador la ve).
 */
function userInRoom(PDO $pdo, int $roomId, int $userId)
{
    $stmt = $pdo->prepare("SELECT 1 FROM rooms WHERE id = ?");
    $stmt->execute([$roomId]);
    return (bool) $stmt->fetchColumn();
}

switch ($action) {

    // -------------------------------------------------------------------
    case 'send':
    case 'send_signal':
        $user       = requireAuth();
        $roomId     = (int) ($data['room_id'] ?? 0);
        $signalType = sanitize($data['signal_type'] ?? '');
        $signalData = $data['signal_data'] ?? ($data['data'] ?? null);

        if ($roomId <= 0 || $signalType === '') {
            respondJSON(false, [], 'room_id y signal_type son obligatorios');
        }
        if (!userInRoom($pdo, $roomId, $user['id'])) {
            http_response_code(404);
            respondJSON(false, [], 'Sala no encontrada');
        }

        // signal_data se guarda serializado como JSON en la columna "data"
        $payload = is_string($signalData) ? $signalData : json_encode($signalData);

        $ins = $pdo->prepare("
            INSERT INTO signals (room_id, from_player_id, signal_type, data)
            VALUES (?, ?, ?, ?)
        ");
        $ins->execute([$roomId, $user['id'], $signalType, $payload]);

        respondJSON(true, ['signal_id' => (int) $pdo->lastInsertId()]);
        break;

    // -------------------------------------------------------------------
    case 'poll':
    case 'get_signals':
        $user     = requireAuth();
        $roomId   = (int) ($data['room_id'] ?? 0);
        $sinceId  = (int) ($data['since_id'] ?? 0);

        if ($roomId <= 0) {
            respondJSON(false, [], 'room_id requerido');
        }

        $stmt = $pdo->prepare("
            SELECT s.id, s.from_player_id, s.signal_type, s.data, s.created_at,
                   u.username AS from_username
            FROM signals s
            LEFT JOIN users u ON u.id = s.from_player_id
            WHERE s.room_id = ?
              AND s.id > ?
              AND (s.from_player_id IS NULL OR s.from_player_id != ?)
            ORDER BY s.id ASC
        ");
        $stmt->execute([$roomId, $sinceId, $user['id']]);

        $signals = [];
        foreach ($stmt->fetchAll() as $row) {
            $decoded = json_decode($row['data'], true);
            $signals[] = [
                'signal_id'    => (int) $row['id'],
                'from_user_id' => $row['from_player_id'] !== null ? (int) $row['from_player_id'] : null,
                'from_username' => $row['from_username'],
                'signal_type'  => $row['signal_type'],
                'signal_data'  => $decoded !== null ? $decoded : $row['data'],
                'created_at'   => $row['created_at'],
            ];
        }

        respondJSON(true, ['signals' => $signals]);
        break;

    // -------------------------------------------------------------------
    case 'mark_consumed':
        requireAuth();
        $ids = $data['signal_ids'] ?? [];
        if (!is_array($ids)) {
            $ids = [];
        }
        $ids = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));

        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $del = $pdo->prepare("DELETE FROM signals WHERE id IN ($placeholders)");
            $del->execute($ids);
        }

        respondJSON(true, ['consumed' => count($ids)]);
        break;

    // -------------------------------------------------------------------
    default:
        http_response_code(404);
        respondJSON(false, [], 'Acción no reconocida: usa send, poll o mark_consumed');
}
