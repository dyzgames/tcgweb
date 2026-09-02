<script>
  import { videoStore, roomStore, authStore } from '../lib/store.js'

  let vs = { localStream: null, remoteStream: null, isAudioEnabled: true, isVideoEnabled: true }
  videoStore.subscribe(v => (vs = v))

  let room = { players: [], playerIndex: 0, role: 'player' }
  roomStore.subscribe(v => (room = v))

  let myName = null
  authStore.subscribe(v => (myName = v.username))

  let expanded = null // null | 'local' | 'remote'
  function toggle(which) {
    expanded = expanded === which ? null : which
  }

  $: localName = room.role === 'spectator'
    ? (room.players[0]?.username || 'Jugador 1')
    : (myName || 'Tú')

  $: remoteName = room.role === 'spectator'
    ? (room.players[1]?.username || 'Jugador 2')
    : (room.players.find(p => p.username && p.username !== myName)?.username || 'Oponente')

  // Refs de <video> — se rellenan cuando exista stream (Fase 4)
  let videoRefs = {}
  $: if (videoRefs.local && vs.localStream) videoRefs.local.srcObject = vs.localStream
  $: if (videoRefs.remote && vs.remoteStream) videoRefs.remote.srcObject = vs.remoteStream

  const tiles = ['local', 'remote']
</script>

<div class="w-full h-full">
  <div class="grid gap-4 {expanded ? 'grid-cols-1' : 'md:grid-cols-2 grid-cols-1'}">
    {#each tiles as tile}
      {#if !expanded || expanded === tile}
        {@const isLocal = tile === 'local'}
        {@const stream = isLocal ? vs.localStream : vs.remoteStream}
        <div class="relative bg-black rounded-lg overflow-hidden aspect-video ring-1 ring-gray-700">
          {#if stream}
            <!-- svelte-ignore a11y-media-has-caption -->
            <video
              bind:this={videoRefs[tile]}
              autoplay
              playsinline
              muted={isLocal}
              class="w-full h-full object-cover"
            ></video>
          {:else}
            <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-500">
              <span class="text-4xl mb-2">📹</span>
              <span class="text-sm">{isLocal ? 'Tu cámara (sin señal)' : 'Esperando al oponente...'}</span>
            </div>
          {/if}

          <div class="absolute bottom-2 left-2 bg-black/60 text-white text-sm px-2 py-1 rounded">
            {isLocal ? localName : remoteName}
          </div>

          <div class="absolute top-2 left-2 flex gap-1">
            <span class="bg-black/60 text-xs px-2 py-1 rounded" title="Micrófono">
              {vs.isAudioEnabled ? '🎤' : '🔇'}
            </span>
            <span class="bg-black/60 text-xs px-2 py-1 rounded" title="Cámara">
              {vs.isVideoEnabled ? '🎥' : '🚫'}
            </span>
          </div>

          <button
            on:click={() => toggle(tile)}
            class="absolute top-2 right-2 bg-black/60 hover:bg-black/80 text-white text-xs px-2 py-1 rounded"
          >
            {expanded === tile ? '↙ Reducir' : '⛶ Agrandar'}
          </button>
        </div>
      {/if}
    {/each}
  </div>
</div>
