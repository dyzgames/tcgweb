<script>
  import { createEventDispatcher, onMount } from 'svelte'
  import { authStore } from '../lib/store.js'
  import { listUsers, createUser, deleteUser } from '../lib/api.js'
  import { unwrap, errorMessage, formatDate } from '../lib/utils.js'

  const dispatch = createEventDispatcher()

  let auth = {}
  authStore.subscribe(v => (auth = v))

  let users = []
  let newUsername = ''
  let newPassword = ''
  let loading = false
  let error = ''
  let success = ''

  onMount(load)

  async function load() {
    error = ''
    try {
      const res = await listUsers(auth.sessionToken)
      if (res.success) {
        users = unwrap(res).users || []
      } else {
        error = errorMessage(res, 'No se pudieron cargar los usuarios')
      }
    } catch (e) {
      error = 'Error de conexión: ' + e.message
    }
  }

  async function handleCreate() {
    error = ''
    success = ''

    if (!newUsername || !newPassword) {
      error = 'Usuario y contraseña requeridos'
      return
    }
    if (newUsername.length < 3) {
      error = 'El usuario debe tener al menos 3 caracteres'
      return
    }
    if (newPassword.length < 4) {
      error = 'La contraseña debe tener al menos 4 caracteres'
      return
    }

    loading = true
    try {
      const res = await createUser(auth.sessionToken, newUsername, newPassword)
      if (res.success) {
        success = `Usuario "${newUsername}" creado`
        newUsername = ''
        newPassword = ''
        await load()
      } else {
        error = errorMessage(res, 'No se pudo crear el usuario')
      }
    } catch (e) {
      error = 'Error de conexión: ' + e.message
    } finally {
      loading = false
    }
  }

  async function handleDelete(userId, username) {
    if (!confirm(`¿Eliminar al usuario "${username}"?`)) return
    error = ''
    success = ''
    try {
      const res = await deleteUser(auth.sessionToken, userId)
      if (res.success) {
        success = 'Usuario eliminado'
        await load()
      } else {
        error = errorMessage(res, 'No se pudo eliminar el usuario')
      }
    } catch (e) {
      error = 'Error de conexión: ' + e.message
    }
  }
</script>

<div class="min-h-screen bg-gray-900 p-4">
  {#if !auth.isAdmin}
    <div class="max-w-md mx-auto mt-20 bg-red-900 text-red-100 p-6 rounded-lg text-center">
      ⛔ Acceso denegado: solo administradores
      <button on:click={() => dispatch('navigate', 'menu')} class="btn-primary w-full mt-4">
        Volver
      </button>
    </div>
  {:else}
    <div class="max-w-3xl mx-auto">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Panel de administración</h1>
        <button on:click={() => dispatch('navigate', 'menu')} class="btn bg-gray-700 hover:bg-gray-600">
          ← Menú
        </button>
      </div>

      {#if error}
        <div class="bg-red-900 text-red-200 p-3 rounded mb-3">{error}</div>
      {/if}
      {#if success}
        <div class="bg-green-900 text-green-200 p-3 rounded mb-3">{success}</div>
      {/if}

      <!-- Crear usuario -->
      <div class="card mb-6">
        <h2 class="text-lg font-bold mb-4">Crear usuario</h2>
        <div class="flex flex-col md:flex-row gap-3">
          <input
            type="text"
            placeholder="Usuario"
            bind:value={newUsername}
            class="flex-1 px-4 py-2 bg-gray-700 rounded text-white"
            disabled={loading}
          />
          <input
            type="password"
            placeholder="Contraseña"
            bind:value={newPassword}
            class="flex-1 px-4 py-2 bg-gray-700 rounded text-white"
            disabled={loading}
          />
          <button on:click={handleCreate} disabled={loading} class="btn-primary">
            {loading ? 'Creando…' : 'Crear'}
          </button>
        </div>
      </div>

      <!-- Lista de usuarios -->
      <div class="card">
        <h2 class="text-lg font-bold mb-4">Usuarios ({users.length})</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="text-gray-400 border-b border-gray-700">
              <tr>
                <th class="py-2 pr-4">ID</th>
                <th class="py-2 pr-4">Usuario</th>
                <th class="py-2 pr-4">Rol</th>
                <th class="py-2 pr-4">Creado</th>
                <th class="py-2"></th>
              </tr>
            </thead>
            <tbody>
              {#each users as u (u.user_id)}
                <tr class="border-b border-gray-800">
                  <td class="py-2 pr-4 text-gray-400">{u.user_id}</td>
                  <td class="py-2 pr-4 font-medium">{u.username}</td>
                  <td class="py-2 pr-4">
                    {#if u.is_admin}
                      <span class="bg-purple-700 text-xs px-2 py-1 rounded">ADMIN</span>
                    {:else}
                      <span class="text-gray-400">jugador</span>
                    {/if}
                  </td>
                  <td class="py-2 pr-4 text-gray-400">{formatDate(u.created_at)}</td>
                  <td class="py-2 text-right">
                    {#if !u.is_admin}
                      <button
                        on:click={() => handleDelete(u.user_id, u.username)}
                        class="btn-danger text-xs py-1"
                      >
                        Eliminar
                      </button>
                    {/if}
                  </td>
                </tr>
              {/each}
              {#if users.length === 0}
                <tr><td colspan="5" class="py-4 text-center text-gray-500">Sin usuarios</td></tr>
              {/if}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  {/if}
</div>
