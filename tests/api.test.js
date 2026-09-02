import { describe, it, expect, beforeEach, vi } from 'vitest'
import * as api from '../src/lib/api.js'

/** Configura la siguiente respuesta de fetch como JSON */
function mockJsonOnce(payload) {
  global.fetch.mockResolvedValueOnce({
    ok: true,
    json: () => Promise.resolve(payload)
  })
}

/** Devuelve [url, options] de la última llamada a fetch */
function lastCall() {
  return global.fetch.mock.calls[global.fetch.mock.calls.length - 1]
}

describe('src/lib/api.js', () => {
  beforeEach(() => {
    global.fetch = vi.fn()
  })

  it('login() success — devuelve el token', async () => {
    mockJsonOnce({
      success: true,
      session_token: 'tok123',
      user_id: 1,
      data: { session_token: 'tok123', user_id: 1 },
      error: null
    })

    const res = await api.login('admin', 'admin123')

    expect(res.success).toBe(true)
    expect(res.session_token).toBe('tok123')

    const [url, opts] = lastCall()
    expect(url).toContain('auth.php')
    expect(opts.method).toBe('POST')
    expect(JSON.parse(opts.body)).toMatchObject({
      action: 'login',
      usuario: 'admin',
      contrasena: 'admin123'
    })
  })

  it('login() error — propaga el mensaje de error', async () => {
    mockJsonOnce({ success: false, data: [], error: 'Credenciales incorrectas' })

    const res = await api.login('admin', 'mal')

    expect(res.success).toBe(false)
    expect(res.error).toMatch(/credenciales/i)
  })

  it('createRoom() — POST a rooms.php con action create_room', async () => {
    mockJsonOnce({ success: true, room_id: 7, code: 'ABC123', stun_servers: [] })

    const res = await api.createRoom('tok123')

    expect(res.success).toBe(true)
    expect(res.code).toBe('ABC123')

    const [url, opts] = lastCall()
    expect(url).toContain('rooms.php')
    expect(JSON.parse(opts.body)).toMatchObject({
      action: 'create_room',
      session_token: 'tok123'
    })
  })

  it('joinRoom() — envía código y token', async () => {
    mockJsonOnce({ success: true, room_id: 7, your_player_index: 1, other_players: [] })

    const res = await api.joinRoom('ABC123', 'tok123')

    expect(res.success).toBe(true)
    expect(JSON.parse(lastCall()[1].body)).toMatchObject({
      action: 'join_room',
      code: 'ABC123',
      session_token: 'tok123'
    })
  })

  it('sendSignal() — serializa la señal WebRTC', async () => {
    mockJsonOnce({ success: true, signal_id: 1 })

    const signal = { type: 'offer', sdp: 'v=0...' }
    const res = await api.sendSignal(7, 'offer', signal, 'tok123')

    expect(res.success).toBe(true)
    // api.js manda la señal en el campo `data` (el backend acepta `data` y `signal_data`)
    expect(JSON.parse(lastCall()[1].body)).toMatchObject({
      action: 'send_signal',
      room_id: 7,
      signal_type: 'offer',
      data: signal,
      session_token: 'tok123'
    })
  })

  it('sendAlert() — POST a alerts.php', async () => {
    mockJsonOnce({ success: true, alert_id: 1 })

    const res = await api.sendAlert(7, 'tok123')

    expect(res.success).toBe(true)
    const [url, opts] = lastCall()
    expect(url).toContain('alerts.php')
    expect(JSON.parse(opts.body)).toMatchObject({
      action: 'send_alert',
      room_id: 7,
      session_token: 'tok123'
    })
  })
})
