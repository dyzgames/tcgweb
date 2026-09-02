const API_URL = import.meta.env.VITE_API_URL || '/api'

async function request(endpoint, method = 'GET', data = null) {
  const options = {
    method,
    headers: { 'Content-Type': 'application/json' }
  }

  if (data) {
    options.body = JSON.stringify(data)
  }

  const response = await fetch(`${API_URL}/${endpoint}`, options)
  return response.json()
}

// ===== AUTENTICACIÓN =====

export async function login(usuario, contrasena) {
  return request('auth.php', 'POST', {
    action: 'login',
    usuario,
    contrasena
  })
}

export async function logout(sessionToken) {
  return request('auth.php', 'POST', {
    action: 'logout',
    session_token: sessionToken
  })
}

export async function register(username, password) {
  return request('auth.php', 'POST', {
    action: 'register',
    username,
    password
  })
}

// ===== SALAS =====

export async function createRoom(sessionToken) {
  return request('rooms.php', 'POST', {
    action: 'create_room',
    session_token: sessionToken
  })
}

export async function joinRoom(code, sessionToken) {
  return request('rooms.php', 'POST', {
    action: 'join_room',
    code,
    session_token: sessionToken
  })
}

export async function spectateRoom(code, sessionToken) {
  return request('rooms.php', 'POST', {
    action: 'spectate',
    code,
    session_token: sessionToken
  })
}

export async function leaveRoom(roomId, sessionToken) {
  return request('rooms.php', 'POST', {
    action: 'leave',
    room_id: roomId,
    session_token: sessionToken
  })
}

export async function roomInfo(roomId) {
  return request(`rooms.php?action=info&room_id=${roomId}`)
}

// ===== SEÑALIZACIÓN WEBRTC =====

export async function sendSignal(roomId, signalType, data, sessionToken) {
  return request('signal.php', 'POST', {
    action: 'send_signal',
    room_id: roomId,
    signal_type: signalType,
    data,
    session_token: sessionToken
  })
}

export async function pollSignals(roomId, sessionToken, sinceId = 0) {
  return request(
    `signal.php?action=poll&room_id=${roomId}&session_token=${sessionToken}&since_id=${sinceId}`
  )
}

// Alias con el nombre usado en FASE4_WEBRTC.md
export async function getSignals(roomId, sinceId = 0, sessionToken = null) {
  const token = sessionToken || localStorage.getItem('session_token') || ''
  return pollSignals(roomId, token, sinceId)
}

export async function markSignalsConsumed(signalIds, sessionToken) {
  return request('signal.php', 'POST', {
    action: 'mark_consumed',
    signal_ids: signalIds,
    session_token: sessionToken
  })
}

// ===== ALERTAS =====

export async function sendAlert(roomId, sessionToken) {
  return request('alerts.php', 'POST', {
    action: 'send_alert',
    room_id: roomId,
    session_token: sessionToken
  })
}

export async function pollAlerts(roomId, sessionToken, sinceId = 0) {
  return request(
    `alerts.php?action=poll&room_id=${roomId}&session_token=${sessionToken}&since_id=${sinceId}`
  )
}

export async function markAlertsConsumed(alertIds, sessionToken) {
  return request('alerts.php', 'POST', {
    action: 'mark_consumed',
    alert_ids: alertIds,
    session_token: sessionToken
  })
}

// ===== ADMIN =====

export async function createUser(sessionToken, username, password) {
  return request('admin.php', 'POST', {
    action: 'create_user',
    session_token: sessionToken,
    username,
    password
  })
}

export async function deleteUser(sessionToken, userId) {
  return request('admin.php', 'POST', {
    action: 'delete_user',
    session_token: sessionToken,
    user_id: userId
  })
}

export async function listUsers(sessionToken) {
  return request(`admin.php?action=list_users&session_token=${sessionToken}`)
}
