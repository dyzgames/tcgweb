<script>
  import { onMount } from 'svelte'
  import Login from './components/Login.svelte'
  import MainMenu from './components/MainMenu.svelte'
  import Room from './components/Room.svelte'
  import Admin from './components/Admin.svelte'
  import { authStore } from './lib/store.js'

  let isAuthenticated = false
  let currentPage = 'menu'   // 'menu' | 'room' | 'admin'

  authStore.subscribe(value => {
    isAuthenticated = !!value.sessionToken
    if (!isAuthenticated) currentPage = 'menu'
  })

  onMount(() => {
    // Restaurar sesión guardada
    const saved = localStorage.getItem('session_token')
    if (saved) {
      authStore.set({
        sessionToken: saved,
        userId: parseInt(localStorage.getItem('user_id') || '0') || null,
        username: localStorage.getItem('username') || null,
        isAdmin: localStorage.getItem('is_admin') === '1'
      })
    }
  })

  // Los componentes hijos disparan  on:navigate={{ detail: 'menu' | 'room' | 'admin' }}
  function handleNavigate(e) {
    currentPage = e.detail
  }
</script>

<main>
  {#if !isAuthenticated}
    <Login />
  {:else if currentPage === 'room'}
    <Room on:navigate={handleNavigate} />
  {:else if currentPage === 'admin'}
    <Admin on:navigate={handleNavigate} />
  {:else}
    <MainMenu on:navigate={handleNavigate} />
  {/if}
</main>
