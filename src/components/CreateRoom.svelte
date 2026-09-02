<script>
  import { createEventDispatcher, onMount } from 'svelte'
  import { authStore, roomStore } from '../lib/store.js'
  import { createRoom } from '../lib/api.js'
  import { unwrap, errorMessage } from '../lib/utils.js'

  const dispatch = createEventDispatcher()

  let token = null
  authStore.subscribe(v => { token = v.sessionToken })

  let loading = true
  let error = ''
  let code = ''
  let roomId = null
  let stun = []
  let copied = false

  onMount(create)

  async function create() {
    loading = true
    error = ''
    try {
      const res = await createRoom(token)
      if (res.success) {
        const d = unwrap(res)
        code = d.code
        roomId = d.room_id
        stun = d.stun_servers || []
      } else {
        error = errorMessage(res, 'No se pudo crear la partida')
      }
    } catch (e) {
      error = 'Error de conexión: ' + e.message
    } finally {
      loading = false
    }
  }

  async function copyCode() {
    try {
      await navigator.clipboard.writeText(code)
      copied = true
      setTimeout(() => (copied = false), 2000)
    } catch (e) {
      error = 'No se pudo copiar al portapapeles'
    }
  }

  function enterRoom() {
    roomStore.set({
      roomId,
      code,
      players: [{ user_id: $authStore.userId, username: $authStore.username }],
      role: 'player',
      playerIndex: 0,
      stunServers: stun,
      isStreaming: false
    })
    dispatch('navigate')
  }
</script>

<div class="card w-full max-w-md mx-auto">
  <h2 class="text-2xl font-bold mb-6 text-center">Crear Partida</h2>

  {#if loading}
    <div class="flex justify-center mb-4">
      <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-blue-500"></div>
    </div>
    <p class="text-center text-gray-300">Generando código...</p>

  {:else if code}
    <div class="bg-green-900 border border-green-600 p-4 rounded mb-4 text-center">
      <p class="text-green-200 text-sm mb-1">Código de la partida</p>
      <p class="text-white text-4xl font-bold font-mono tracking-widest">{code}</p>
    </div>

    <button on:click={copyCode} class="btn bg-gray-700 hover:bg-gray-600 w-full mb-2">
      {copied ? '✓ Copiado' : '📋 Copiar código'}
    </button>

    <button on:click={enterRoom} class="btn-primary w-full mb-2">
      Entrar a la sala
    </button>

    <p class="text-gray-400 text-sm text-center">
      Comparte este código con el otro jugador para que se una.
    </p>
  {/if}

  {#if error}
    <div class="bg-red-900 text-red-200 p-3 rounded my-3">{error}</div>
    <button on:click={create} class="btn-primary w-full mb-2">Reintentar</button>
  {/if}

  <button on:click={() => dispatch('back')} class="btn bg-gray-700 hover:bg-gray-600 w-full mt-3">
    ← Volver
  </button>
</div>
