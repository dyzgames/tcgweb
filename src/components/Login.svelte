<script>
  import { login } from '../lib/api.js'
  import { authStore } from '../lib/store.js'

  let usuario = ''
  let contrasena = ''
  let error = ''
  let loading = false

  async function handleLogin() {
    if (!usuario || !contrasena) {
      error = 'Usuario y contraseña requeridos'
      return
    }

    loading = true
    error = ''
    try {
      const result = await login(usuario, contrasena)
      if (result.success) {
        const data = result.data || result
        localStorage.setItem('session_token', data.session_token)
        localStorage.setItem('user_id', data.user_id)
        localStorage.setItem('username', data.username)
        localStorage.setItem('is_admin', data.is_admin ? '1' : '0')
        authStore.set({
          sessionToken: data.session_token,
          userId: data.user_id,
          username: data.username,
          isAdmin: !!data.is_admin
        })
      } else {
        error = result.error || 'Error en login'
      }
    } catch (e) {
      error = 'Error de conexión: ' + e.message
    } finally {
      loading = false
    }
  }

  function handleKeyDown(e) {
    if (e.key === 'Enter') handleLogin()
  }
</script>

<div class="min-h-screen flex items-center justify-center bg-gray-900">
  <div class="card w-full max-w-md">
    <h1 class="text-3xl font-bold mb-6 text-center">P2P Game</h1>

    {#if error}
      <div class="bg-red-900 text-red-200 p-3 rounded mb-4">
        {error}
      </div>
    {/if}

    <input
      type="text"
      placeholder="Usuario"
      bind:value={usuario}
      on:keydown={handleKeyDown}
      class="w-full px-4 py-2 bg-gray-700 rounded mb-3 text-white"
      disabled={loading}
    />

    <input
      type="password"
      placeholder="Contraseña"
      bind:value={contrasena}
      on:keydown={handleKeyDown}
      class="w-full px-4 py-2 bg-gray-700 rounded mb-4 text-white"
      disabled={loading}
    />

    <button
      on:click={handleLogin}
      disabled={loading}
      class="btn-primary w-full"
    >
      {loading ? 'Conectando...' : 'LOGIN'}
    </button>
  </div>
</div>
