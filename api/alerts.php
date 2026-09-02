<?php
/**
 * api/alerts.php - Alertas genéricas dentro de una sala
 *
 * Endpoints (via ?action= o campo "action"):
 *   POST  trigger        { session_token, room_id }
 *   GET   poll           ?session_token=&room_id=&since_id=0
 *   POST  mark_consumed  { session_token, alert_ids: [] }
 *
 * Respuesta: { success, data:{...}, error, ...campos promovidos }
 */

require_once __DIR__ . '/../config/utils.php';

$data   = getRequestData();
$action = getAction();
$pdo    = getDB();

switch ($action) {

    // -------------------------------------------------------------------
    case 'trigger':
    case 'send':
    case 'send_alert':
        $user   = requireAuth();
        $roomId = (int) ($data['room_id'] ?? 0);

        if ($roomId <= 0) {
            respondJSON(false, [], 'room_id requerido');
        }

        $stmt = $pdo->prepare("SELECT 1 FROM rooms WHERE id = ?");
        $stmt->execute([$roomId]);
        if (!$stmt->fetchColumn()) {
            http_response_code(404);
            respondJSON(false, [], 'Sala no encontrada');
        }

        $ins = $pdo->prepare("INSERT INTO alerts (room_id, user_id) VALUES (?, ?)");
        $ins->execute([$roomId, $user['id']]);

        respondJSON(true, [
            'alert_id'               => (int) $pdo->lastInsertId(),
            'triggered_by_username'  => $user['username'],
        ]);
        break;

    // -------------------------------------------------------------------
    case 'poll':
        $user    = requireAuth();
        $roomId  = (int) ($data['room_id'] ?? 0);
        $sinceId = (int) ($data['since_id'] ?? 0);

        if ($roomId <= 0) {
            respondJSON(false, [], 'room_id requerido');
        }

        $stmt = $pdo->prepare("
            SELECT a.id, a.user_id, a.created_at, u.username
            FROM alerts a
            JOIN users u ON u.id = a.user_id
            WHERE a.room_id = ?
              AND a.id > ?
              AND a.user_id != ?
            ORDER BY a.id ASC
        ");
        $stmt->execute([$roomId, $sinceId, $user['id']]);

        $alerts = [];
        foreach ($stmt->fetchAll() as $row) {
            $alerts[] = [
                'alert_id'              => (int) $row['id'],
                'triggered_by_user_id'  => (int) $row['user_id'],
                'triggered_by_username' => $row['username'],
                'created_at'            => $row['created_at'],
            ];
        }

        respondJSON(true, ['alerts' => $alerts]);
        break;

    // -------------------------------------------------------------------
    case 'mark_consumed':
        requireAuth();
        $ids = $data['alert_ids'] ?? [];
        if (!is_array($ids)) {
            $ids = [];
        }
        $ids = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));

        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $del = $pdo->prepare("DELETE FROM alerts WHERE id IN ($placeholders)");
            $del->execute($ids);
        }

        respondJSON(true, ['consumed' => count($ids)]);
        break;

    // -------------------------------------------------------------------
    default:
        http_response_code(404);
        respondJSON(false, [], 'Acción no reconocida: usa trigger, poll o mark_consumed');
}
