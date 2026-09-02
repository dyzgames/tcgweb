<script>
  import { createEventDispatcher, onMount, onDestroy } from 'svelte'
  import { authStore, roomStore, videoStore, resetRoom } from '../lib/store.js'
  import { sendSignal, getSignals, leaveRoom, sendAlert } from '../lib/api.js'
  import { WebRTCManager } from '../lib/webrtc.js'
  import VideoGrid from './VideoGrid.svelte'
  import Alert from './Alert.svelte'

  const dispatch = createEventDispatcher()

  let auth = {}
  authStore.subscribe(v => (auth = v))

  let room = { roomId: null, code: '', players: [], role: 'player', playerIndex: 0, stunServers: [] }
  roomStore.subscribe(v => (room = v))

  let vs = { isAudioEnabled: true, isVideoEnabled: true }
  videoStore.subscribe(v => (vs = v))

  let webrtc = null
  let pollInterval = null
  let lastSignalId = 0

  let connState = 'init'        // 'init' | 'waiting' | 'connected' | 'error'
  let cameraError = ''
  let showAlert = false
  let alertMessage = ''
  let leaving = false

  onMount(() => {
    if (!room.roomId) {
      dispatch('navigate', 'menu')
      return
    }
    start()
  })

  onDestroy(cleanup)

  async function start() {
    const isInitiator = room.role === 'player' && (room.playerIndex ?? 0) === 0
    const receiveOnly = room.role === 'spectator'

    webrtc = new WebRTCManager({
      isInitiator,
      receiveOnly,
      roomId: room.roomId,
      userId: auth.userId,
      stunServers: room.stunServers,
      onConnect: () => (connState = 'connected'),
      onError: () => (connState = 'error')
    })

    // 1. Stream local (salvo espectador)
    if (!receiveOnly) {
      try {
        await webrtc.getLocalStream()
      } catch (e) {
        cameraError = 'No se pudo acceder a la cámara/micrófono. ' +
          'Revisa los permisos del navegador. Puedes seguir viendo al oponente.'
        webrtc.receiveOnly = true
      }
    }

    // 2. Conexión P2P: al generar OFFER/ANSWER/ICE lo mandamos al servidor
    await webrtc.initializePeer(async (signalData) => {
      await sendSignal(
        room.roomId,
        signalData.type || 'candidate',
        signalData,
        auth.sessionToken
      )
    })

    connState = 'waiting'

    // 3. Polling de señales del otro extremo cada 1 s
    pollInterval = setInterval(pollSignalsOnce, 1000)
    pollSignalsOnce()
  }

  async function pollSignalsOnce() {
    if (!webrtc) return
    try {
      const res = await getSignals(room.roomId, lastSignalId, auth.sessionToken)
      const signals = (res && (res.signals || (res.data && res.data.signals))) || []
      for (const s of signals) {
        await webrtc.receiveSignal(s.signal_data)
        if (s.signal_id > lastSignalId) lastSignalId = s.signal_id
      }
    } catch (e) {
      // Un fallo puntual de red no debe romper el bucle
    }
  }

  function toggleAudio() {
    webrtc && webrtc.setAudioEnabled(!vs.isAudioEnabled)
  }

  function toggleVideo() {
    webrtc && webrtc.setVideoEnabled(!vs.isVideoEnabled)
  }

  async function triggerAlert() {
    alertMessage = `¡Alerta de ${auth.username || 'un jugador'}!`
    showAlert = true
    try {
      if (room.roomId) await sendAlert(room.roomId, auth.sessionToken)
    } catch (e) {
      /* la alerta local ya se muestra */
    }
  }

  function cleanup() {
    if (pollInterval) {
      clearInterval(pollInterval)
      pollInterval = null
    }
    if (webrtc) {
      webrtc.close()
      webrtc = null
    }
  }

  async function leave() {
    leaving = true
    try {
      if (room.roomId) await leaveRoom(room.roomId, auth.sessionToken)
    } catch (e) {
      /* continuamos igualmente */
    }
    cleanup()
    resetRoom()
    dispatch('navigate', 'menu')
  }
</script>

<div class="min-h-screen flex flex-col bg-gray-900">
  <!-- Barra superior -->
  <header class="bg-gray-800 border-b border-gray-700 p-4 flex flex-wrap gap-3 justify-between items-center">
    <div class="flex items-center gap-3">
      <span class="font-bold">
        Código: <span class="font-mono text-blue-400 tracking-widest">{room.code}</span>
      </span>
      {#if room.role === 'spectator'}
        <span class="bg-purple-700 text-xs px-2 py-1 rounded">ESPECTADOR</span>
      {/if}
      <span
        class="text-xs px-2 py-1 rounded"
        class:bg-yellow-700={connState === 'waiting' || connState === 'init'}
        class:bg-green-700={connState === 'connected'}
        class:bg-red-700={connState === 'error'}
      >
        {connState === 'connected' ? 'Conectado'
          : connState === 'error' ? 'Error de conexión'
          : 'Esperando al otro jugador…'}
      </span>
    </div>

    <div class="flex items-center gap-2">
      {#if room.role !== 'spectator'}
        <button on:click={toggleAudio} class="btn bg-blue-600 hover:bg-blue-700">
          {vs.isAudioEnabled ? '🔊 Audio' : '🔇 Audio'}
        </button>
        <button on:click={toggleVideo} class="btn bg-blue-600 hover:bg-blue-700">
          {vs.isVideoEnabled ? '📹 Vídeo' : '🚫 Vídeo'}
        </button>
      {/if}
      <button on:click={triggerAlert} class="btn-danger font-bold">⚠️ Alerta</button>
      <button on:click={leave} disabled={leaving} class="btn bg-gray-700 hover:bg-gray-600">
        🚪 Salir
      </button>
    </div>
  </header>

  {#if cameraError}
    <div class="bg-red-900 text-red-200 px-4 py-2 text-sm">{cameraError}</div>
  {/if}

  <!-- Jugadores conectados -->
  <div class="bg-gray-800/60 px-4 py-2 text-sm text-gray-300 flex gap-2 items-center">
    <span class="text-gray-400">Jugadores:</span>
    {#if room.players.length === 0}
      <span class="text-gray-500">esperando…</span>
    {:else}
      {#each room.players as p}
        <span class="bg-gray-700 px-2 py-0.5 rounded">{p.username}</span>
      {/each}
    {/if}
  </div>

  <!-- Grid de vídeo -->
  <main class="flex-1 p-4 overflow-auto">
    <VideoGrid />
  </main>

  {#if showAlert}
    <Alert message={alertMessage} on:dismiss={() => (showAlert = false)} />
  {/if}
</div>
