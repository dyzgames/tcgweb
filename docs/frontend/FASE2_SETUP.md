# 🎨 FASE 2: FRONTEND SETUP (Vite + Svelte + Tailwind)

**Objetivo:** Configurar stack frontend, crear estructura base, primeros componentes  
**Duración:** 1-2 sesiones  
**Prerequisito:** Fase 1 completada

---

## 📝 TAREAS DE FASE 2

### Tarea 2.1: Configurar Vite

Crear `vite.config.js` en la raíz:

```javascript
import { defineConfig } from 'vite'
import { svelte } from '@sveltejs/vite-plugin-svelte'

export default defineConfig({
  plugins: [svelte()],
  
  server: {
    port: 5173,
    strictPort: false,
    open: false,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      }
    }
  },
  
  build: {
    minify: 'terser',
    sourcemap: false,
    target: 'es2020',
    rollupOptions: {
      output: {
        manualChunks: {
          'vendor': ['simple-peer']
        }
      }
    }
  },
  
  assetsInclude: ['**/*.mp3', '**/*.wav'],
})
```

---

### Tarea 2.2: Configurar Tailwind CSS

Crear `tailwind.config.js`:

```javascript
export default {
  content: [
    "./index.html",
    "./src/**/*.{svelte,js}"
  ],
  theme: {
    extend: {
      colors: {
        primary: '#3b82f6',
        secondary: '#ef4444'
      }
    }
  },
  plugins: []
}
```

Crear `postcss.config.js`:

```javascript
export default {
  plugins: {
    tailwindcss: {},
    autoprefixer: {}
  }
}
```

---

### Tarea 2.3: Crear estructura Svelte

Crear archivo de estilos `src/styles/app.css`:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom styles */
:root {
  --primary: #3b82f6;
  --secondary: #ef4444;
}

body {
  @apply bg-gray-900 text-white;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.container {
  @apply max-w-7xl mx-auto px-4;
}

.btn {
  @apply px-4 py-2 rounded font-medium transition-colors;
}

.btn-primary {
  @apply btn bg-blue-600 hover:bg-blue-700;
}

.btn-danger {
  @apply btn bg-red-600 hover:bg-red-700;
}

.card {
  @apply bg-gray-800 rounded-lg p-6 shadow-lg;
}
```

---

### Tarea 2.4: Crear App.svelte y main.js

`src/App.svelte`:

```svelte
<script>
  import { onMount } from 'svelte'
  import Login from './components/Login.svelte'
  import MainMenu from './components/MainMenu.svelte'
  import { authStore } from './lib/store.js'
  
  let isAuthenticated = false
  
  authStore.subscribe(value => {
    isAuthenticated = !!value.sessionToken
  })
  
  onMount(() => {
    // Verificar si hay sesión guardada
    const saved = localStorage.getItem('session_token')
    if (saved) {
      authStore.set({ sessionToken: saved })
    }
  })
</script>

<main>
  {#if isAuthenticated}
    <MainMenu />
  {:else}
    <Login />
  {/if}
</main>

<style global>
  @import './styles/app.css'
</style>
```

`src/main.js`:

```javascript
import App from './App.svelte'

const app = new App({
  target: document.getElementById('app'),
})

export default app
```

---

### Tarea 2.5: Crear index.html

En la raíz, crear `index.html`:

```html
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>P2P Game Platform</title>
  </head>
  <body>
    <div id="app"></div>
    <script type="module" src="/src/main.js"></script>
  </body>
</html>
```

---

### Tarea 2.6: Crear primeros componentes

**`src/components/Login.svelte`:**

```svelte
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
    try {
      const result = await login(usuario, contrasena)
      if (result.success) {
        localStorage.setItem('session_token', result.data.session_token)
        authStore.set({
          sessionToken: result.data.session_token,
          userId: result.data.user_id
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
      class="w-full px-4 py-2 bg-gray-700 rounded mb-3 text-white"
      disabled={loading}
    />
    
    <input
      type="password"
      placeholder="Contraseña"
      bind:value={contrasena}
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
```

**`src/components/MainMenu.svelte`:**

```svelte
<script>
  import { authStore } from '../lib/store.js'
  
  function logout() {
    localStorage.removeItem('session_token')
    authStore.set({ sessionToken: null })
  }
</script>

<nav class="bg-gray-800 p-4 border-b border-gray-700">
  <div class="container flex justify-between items-center">
    <h1 class="text-xl font-bold">P2P Game Platform</h1>
    <button on:click={logout} class="btn-danger">Logout</button>
  </div>
</nav>

<main class="container py-8">
  <div class="grid grid-cols-2 gap-4">
    <div class="card">
      <h2 class="text-xl font-bold mb-4">Crear Partida</h2>
      <p class="text-gray-300 mb-4">Crea una nueva partida y comparte el código</p>
      <button class="btn-primary w-full">Crear</button>
    </div>
    
    <div class="card">
      <h2 class="text-xl font-bold mb-4">Unirse a Partida</h2>
      <p class="text-gray-300 mb-4">Únete a una partida existente con código</p>
      <button class="btn-primary w-full">Unirse</button>
    </div>
  </div>
</main>
```

---

### Tarea 2.7: Crear librerías base

**`src/lib/store.js` (Svelte Stores para estado global):**

```javascript
import { writable } from 'svelte/store'

export const authStore = writable({
  sessionToken: null,
  userId: null,
  username: null
})

export const roomStore = writable({
  roomId: null,
  code: null,
  players: [],
  isStreaming: false
})

export const videoStore = writable({
  localStream: null,
  remoteStream: null,
  isAudioEnabled: true,
  isVideoEnabled: true
})
```

**`src/lib/api.js` (Llamadas a backend):**

```javascript
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

export async function login(usuario, contrasena) {
  return request('auth.php', 'POST', {
    action: 'login',
    usuario,
    contrasena
  })
}

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

export async function sendSignal(roomId, signalType, data, sessionToken) {
  return request('signal.php', 'POST', {
    action: 'send_signal',
    room_id: roomId,
    signal_type: signalType,
    data,
    session_token: sessionToken
  })
}

export async function sendAlert(roomId, sessionToken) {
  return request('alerts.php', 'POST', {
    action: 'send_alert',
    room_id: roomId,
    session_token: sessionToken
  })
}
```

---

## ✅ CÓMO TESTEAR FASE 2

```bash
# Terminal 1: Backend PHP
php -S localhost:8000

# Terminal 2: Frontend Vite
npm run dev
```

Debería abrirse `http://localhost:5173` automáticamente.

**Pruebas:**
1. Ver pantalla de login
2. Loguéarse con admin/admin123
3. Ver MainMenu
4. Click en Logout

---

## 🔍 Verificación de Fase 2

- [ ] `npm run dev` corre sin errores
- [ ] Página carga en `http://localhost:5173`
- [ ] Login funciona con admin/admin123
- [ ] Redirección a MainMenu funciona
- [ ] Logout limpia sesión
- [ ] Tailwind CSS se aplica correctamente
- [ ] Console sin errores

---

## 📌 PRÓXIMO PASO

Cuando Fase 2 esté lista, di:

**"Fase 2 completada. Voy con Fase 3: Componentes de UI"**

