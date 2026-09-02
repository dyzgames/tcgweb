<script>
  import { createEventDispatcher } from 'svelte'
  import { authStore, roomStore } from '../lib/store.js'
  import { joinRoom, spectateRoom } from '../lib/api.js'
  import { normalizeCode, isValidCode, unwrap, errorMessage } from '../lib/utils.js'

  const dispatch = createEventDispatcher()

  let token = null
  authStore.subscribe(v => { token = v.sessionToken })

  let code = ''
  let loading = false
  let error = ''

  async function connect(asSpectator) {
    const clean = normalizeCode(code)

    if (!clean) {
      error = 'Introduce un código de partida'
      return
    }
    if (!isValidCode(clean)) {
      error = 'El código debe tener 4-8 letras o números'
      return
    }

    loading = true
    error = ''
    try {
      const res = asSpectator
        ? await spectateRoom(clean, token)
        : await joinRoom(clean, token)

      if (res.success) {
        const d = unwrap(res)
        roomStore.set({
          roomId: d.room_id,
          code: clean,
          players: d.other_players || d.players || [],
          role: asSpectator ? 'spectator' : 'player',
          playerIndex: d.your_player_index ?? 1,
          stunServers: d.stun_servers || [],
          isStreaming: false
        })
        dispatch('navigate')
      } else {
        error = errorMessage(res, 'El código no existe o la sala está llena')
      }
    } catch (e) {
      error = 'Error de conexión: ' + e.message
    } finally {
      loading = false
    }
  }

  function handleKeyDown(e) {
    if (e.key === 'Enter') connect(false)
  }
</script>

<div class="card w-full max-w-md mx-auto">
  <h2 class="text-2xl font-bold mb-6 text-center">Unirse a Partida</h2>

  {#if error}
    <div class="bg-red-900 text-red-200 p-3 rounded mb-4">{error}</div>
  {/if}

  <input
    type="text"
    placeholder="Código (ej: ABC123)"
    bind:value={code}
    on:keydown={handleKeyDown}
    maxlength="8"
    class="w-full px-4 py-2 bg-gray-700 rounded mb-4 text-white uppercase tracking-widest text-center text-lg"
    disabled={loading}
  />

  <button on:click={() => connect(false)} disabled={loading} class="btn-primary w-full mb-2">
    {loading ? 'Conectando...' : '👥 Unirse como jugador'}
  </button>

  <button
    on:click={() => connect(true)}
    disabled={loading}
    class="btn bg-purple-600 hover:bg-purple-700 w-full mb-3"
  >
    {loading ? 'Conectando...' : '👁️ Ver como espectador'}
  </button>

  <button on:click={() => dispatch('back')} class="btn bg-gray-700 hover:bg-gray-600 w-full">
    ← Volver
  </button>
</div>
