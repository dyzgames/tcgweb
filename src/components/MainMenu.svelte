<script>
  import { createEventDispatcher } from 'svelte'
  import { authStore } from '../lib/store.js'
  import { logout as apiLogout } from '../lib/api.js'
  import CreateRoom from './CreateRoom.svelte'
  import JoinRoom from './JoinRoom.svelte'

  const dispatch = createEventDispatcher()

  let username = null
  let isAdmin = false
  authStore.subscribe(v => { username = v.username; isAdmin = v.isAdmin })

  let view = 'main' // 'main' | 'create' | 'join'

  async function logout() {
    const token = localStorage.getItem('session_token')
    try {
      if (token) await apiLogout(token)
    } catch (e) {
      // Ignoramos errores de red al cerrar sesión
    }
    localStorage.removeItem('session_token')
    localStorage.removeItem('user_id')
    localStorage.removeItem('username')
    localStorage.removeItem('is_admin')
    authStore.set({ sessionToken: null, userId: null, username: null, isAdmin: false })
  }

  function goRoom() {
    dispatch('navigate', 'room')
  }
</script>

<nav class="bg-gray-800 p-4 border-b border-gray-700">
  <div class="container flex justify-between items-center">
    <h1 class="text-xl font-bold">P2P Game Platform</h1>
    <div class="flex items-center gap-4">
      {#if username}
        <span class="text-gray-300 text-sm">{username}</span>
      {/if}
      {#if isAdmin}
        <button on:click={() => dispatch('navigate', 'admin')} class="btn bg-purple-600 hover:bg-purple-700">
          Admin
        </button>
      {/if}
      <button on:click={logout} class="btn-danger">Logout</button>
    </div>
  </div>
</nav>

<main class="container py-8">
  {#if view === 'main'}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl mx-auto">
      <div class="card">
        <h2 class="text-xl font-bold mb-4">Crear Partida</h2>
        <p class="text-gray-300 mb-4">Crea una nueva partida y comparte el código con otro jugador</p>
        <button class="btn-primary w-full" on:click={() => view = 'create'}>Crear</button>
      </div>

      <div class="card">
        <h2 class="text-xl font-bold mb-4">Unirse a Partida</h2>
        <p class="text-gray-300 mb-4">Únete a una partida existente introduciendo su código</p>
        <button class="btn-primary w-full" on:click={() => view = 'join'}>Unirse</button>
      </div>
    </div>

  {:else if view === 'create'}
    <CreateRoom on:back={() => view = 'main'} on:navigate={goRoom} />

  {:else if view === 'join'}
    <JoinRoom on:back={() => view = 'main'} on:navigate={goRoom} />
  {/if}
</main>
