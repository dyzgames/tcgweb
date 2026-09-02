# 📋 DOCUMENTO TÉCNICO v2.0 - Plataforma de Videojuego P2P con WebRTC

**Proyecto:** Plataforma tipo Spell Table para juegos de 2 jugadores con streaming de webcam  
**Stack Frontend:** Svelte 4 | Vite | simple-peer | Tailwind CSS  
**Stack Backend:** PHP 7.4+ | SQLite3  
**Build:** Estático (sin Node.js en producción)  
**Servidor:** OVH (WordPress existente)  
**Fecha:** 2026  

---

## 📑 TABLA DE CONTENIDOS

1. [Visión General](#1-visión-general)
2. [Stack Tecnológico Detallado](#2-stack-tecnológico-detallado)
3. [Arquitectura del Sistema](#3-arquitectura-del-sistema)
4. [Base de Datos](#4-base-de-datos)
5. [Endpoints PHP](#5-endpoints-php)
6. [Flujo de WebRTC + STUN](#6-flujo-de-webrtc--stun)
7. [Estructura de Proyecto (Desarrollo)](#7-estructura-de-proyecto-desarrollo)
8. [Configuración Vite + Svelte](#8-configuración-vite--svelte)
9. [Componentes Svelte](#9-componentes-svelte)
10. [Integración WebRTC (simple-peer)](#10-integración-webrtc-simple-peer)
11. [Testing Vitest](#11-testing-vitest)
12. [Build y Deployment](#12-build-y-deployment)
13. [Pseudo-código Backend (PHP)](#13-pseudo-código-backend-php)
14. [Consideraciones de Seguridad](#14-consideraciones-de-seguridad)

---

## 1. VISIÓN GENERAL

### Objetivo
Plataforma web donde:
- 2 jugadores se conectan vía webcam P2P
- Ambos ven video en tiempo real (mínimo lag)
- Pueden ampliar cualquiera de las 2 cámaras
- Hasta 5 espectadores ven lo mismo
- Botón alerta genérico con sonido
- Sistema de login + admin que crea usuarios

### Características Técnicas
- **Frontend:** Compilado estático (solo copiar archivos)
- **Backend:** PHP + SQLite (sin Motor BD)
- **P2P:** WebRTC directo + STUN gratuito
- **Señalización:** HTTP polling (1-2 seg)
- **Tamaño Final:** ~200KB gzipped

---

## 2. STACK TECNOLÓGICO DETALLADO

### Frontend (Desarrollo)

```
Svelte 4.x
  └─ Componentes reactivos
     ├─ Login.svelte
     ├─ MainMenu.svelte
     ├─ Room.svelte
     ├─ Admin.svelte
     └─ [otros]

Vite 5.x
  └─ Build ultrarrápido
     ├─ Dev server con HMR
     └─ Producción: dist/ estático

simple-peer 9.x
  └─ WebRTC wrapper
     ├─ Manejo automático SDP/ICE
     └─ Evento 'data' y 'stream'

Tailwind CSS 3.x
  └─ Utilities CSS
     ├─ PurgeCSS en build
     └─ ~20-30KB final

TypeScript 5.x (opcional pero recomendado)
  └─ Type safety
     └─ Mejor mantenibilidad
```

### Backend (Producción)

```
PHP 7.4+
  └─ Endpoints JSON

SQLite 3.x
  └─ Base de datos en archivos
     └─ /db/game.sqlite3
```

### Testing

```
Vitest 1.x
  └─ Testing unit + integration
     ├─ jsdom (simulación DOM)
     ├─ Happy DOM (alternativa ligera)
     └─ Pruebas de carga de componentes
```

---

## 3. ARQUITECTURA DEL SISTEMA

(Igual que documento v1, ver sección 2 de anterior)

### Flujo Simplificado

```
┌─────────────────────────────────────────────────────────┐
│              NAVEGADORES (Svelte + simple-peer)         │
├──────────────────┬──────────────────┬──────────────────┤
│  Jugador 1       │   Jugador 2      │  Espectadores    │
│  (Webcam)        │   (Webcam)       │  (Solo lectura)  │
│  simple-peer P2P │   simple-peer P2P│  simple-peer P2P │
└──────────────────┴──────────────────┴──────────────────┘
           ↓                ↓                   ↓
           └────────────────┼───────────────────┘
                            ↓
           ┌─────────────────────────────────┐
           │  HTTP POLLING (1-2 seg)         │
           │  Señalización WebRTC            │
           │  Alertas                        │
           └─────────────────────────────────┘
                            ↓
┌──────────────────────────────────────────────────────────┐
│              SERVIDOR OVH (PHP + SQLite)                 │
│  (Mismo que v1)                                          │
└──────────────────────────────────────────────────────────┘
```

---

## 4. BASE DE DATOS

(Idéntica a documento v1, ver sección 3)

---

## 5. ENDPOINTS PHP

(Idénticos a documento v1, ver sección 4)

Los endpoints retornan JSON exactamente igual.

---

## 6. FLUJO DE WEBRTC + STUN

(Idéntico a documento v1, ver sección 5)

La diferencia es que `simple-peer` maneja automáticamente:
- Creación de RTCPeerConnection
- Generación de OFFER/ANSWER
- Manejo de ICE candidates

---

## 7. ESTRUCTURA DE PROYECTO (DESARROLLO)

### Estructura de Carpetas Locales

```
game-webrtc/
│
├── src/
│   ├── components/
│   │   ├── Login.svelte              # Pantalla de login
│   │   ├── MainMenu.svelte           # Menú principal
│   │   ├── CreateRoom.svelte         # Crear partida
│   │   ├── JoinRoom.svelte           # Unirse a partida
│   │   ├── Room.svelte               # Pantalla de partida (componente padre)
│   │   ├── VideoGrid.svelte          # Grid de 2 vídeos
│   │   ├── Admin.svelte              # Panel admin
│   │   └── Alert.svelte              # Notificación de alerta
│   │
│   ├── lib/
│   │   ├── api.js                    # Llamadas a endpoints PHP
│   │   ├── webrtc.js                 # Gestión simple-peer
│   │   ├── auth.js                   # Gestión de sesión
│   │   ├── store.js                  # Svelte stores (estado global)
│   │   └── utils.js                  # Funciones comunes
│   │
│   ├── styles/
│   │   └── app.css                   # Tailwind + custom CSS
│   │
│   ├── App.svelte                    # Componente raíz
│   └── main.js                       # Entry point
│
├── public/
│   ├── alert-sound.mp3               # Sonido alerta (compilado con assets)
│   └── favicon.ico
│
├── tests/
│   ├── api.test.js                   # Tests de llamadas HTTP
│   ├── webrtc.test.js                # Tests de WebRTC
│   ├── components/
│   │   ├── Login.test.js
│   │   ├── Room.test.js
│   │   └── Admin.test.js
│   └── setup.js                      # Config Vitest
│
├── vite.config.js                    # Configuración Vite
├── vitest.config.js                  # Configuración Vitest
├── tailwind.config.js                # Configuración Tailwind
├── postcss.config.js                 # Procesamiento CSS
├── package.json
├── package-lock.json
└── index.html                        # HTML raíz
```

### Carpeta `/dist/` (Post-Build - Esto se sube al servidor)

```
dist/
├── index.html                        # HTML compilado
├── assets/
│   ├── index-XXXXX.js               # JS compilado
│   ├── index-XXXXX.css              # CSS compilado
│   └── alert-sound-XXXXX.mp3        # Asset compilado
└── [otros assets compilados]
```

---

## 8. CONFIGURACIÓN VITE + SVELTE

### `package.json`

```json
{
  "name": "game-webrtc",
  "version": "1.0.0",
  "type": "module",
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview",
    "test": "vitest",
    "test:ui": "vitest --ui",
    "test:run": "vitest run"
  },
  "devDependencies": {
    "@sveltejs/vite-plugin-svelte": "^3.0.0",
    "@vitest/ui": "^1.0.0",
    "autoprefixer": "^10.4.0",
    "happy-dom": "^12.0.0",
    "postcss": "^8.4.0",
    "tailwindcss": "^3.4.0",
    "vite": "^5.0.0",
    "vitest": "^1.0.0"
  },
  "dependencies": {
    "simple-peer": "^9.11.0"
  }
}
```

### `vite.config.js`

```javascript
import { defineConfig } from 'vite'
import { svelte } from '@sveltejs/vite-plugin-svelte'

export default defineConfig({
  plugins: [svelte()],
  
  server: {
    port: 5173,
    strictPort: false,
    open: true,
    // Proxy a servidor PHP local (desarrollo)
    proxy: {
      '/api': {
        target: 'http://localhost:8000', // Cambiar a tu servidor local
        changeOrigin: true,
      }
    }
  },
  
  build: {
    // Optimizaciones
    minify: 'terser',
    sourcemap: false,
    
    // Tamaño máximo de chunks
    rollupOptions: {
      output: {
        manualChunks: {
          'vendor': ['simple-peer']
        }
      }
    },
    
    // Target
    target: 'es2020',
  },
  
  // Assets
  assetsInclude: ['**/*.mp3', '**/*.wav'],
})
```

### `vitest.config.js`

```javascript
import { defineConfig } from 'vitest/config'
import { svelte } from '@sveltejs/vite-plugin-svelte'

export default defineConfig({
  plugins: [svelte()],
  
  test: {
    globals: true,
    environment: 'happy-dom',
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      exclude: ['node_modules/', 'tests/']
    }
  }
})
```

### `tailwind.config.js`

```javascript
export default {
  content: [
    './index.html',
    './src/**/*.{svelte,js,ts}',
  ],
  theme: {
    extend: {
      colors: {
        primary: '#3B82F6',
        success: '#10B981',
        danger: '#EF4444',
        warning: '#F59E0B',
      }
    },
  },
  plugins: [],
  // Desactivar preflight si lo necesitas
  corePlugins: {
    preflight: true,
  }
}
```

### `postcss.config.js`

```javascript
export default {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
}
```

### `index.html` (Raíz)

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <link rel="icon" type="image/svg+xml" href="/favicon.ico" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Videojuego P2P - Spell Table</title>
</head>
<body>
    <div id="app"></div>
    <script type="module" src="/src/main.js"></script>
</body>
</html>
```

---

## 9. COMPONENTES SVELTE

### `src/main.js`

```javascript
import App from './App.svelte'

const app = new App({
  target: document.getElementById('app'),
})

export default app
```

### `src/App.svelte` (Componente Raíz)

```svelte
<script>
  import { sessionToken, userId, isAdmin } from './lib/store.js'
  import Login from './components/Login.svelte'
  import MainMenu from './components/MainMenu.svelte'
  import Room from './components/Room.svelte'
  import Admin from './components/Admin.svelte'

  let currentPage = 'login'

  // Restaurar sesión si existe
  onMount(() => {
    const token = localStorage.getItem('session_token')
    if (token) {
      sessionToken.set(token)
      currentPage = 'menu'
    }
  })

  // Subscribirse a cambios de página
  function handlePageChange(e) {
    currentPage = e.detail
  }
</script>

<style>
  @import './styles/app.css';
</style>

<div class="min-h-screen bg-gray-900 text-white">
  {#if currentPage === 'login'}
    <Login on:navigate={handlePageChange} />
  {:else if currentPage === 'menu'}
    <MainMenu on:navigate={handlePageChange} />
  {:else if currentPage === 'room'}
    <Room on:navigate={handlePageChange} />
  {:else if currentPage === 'admin'}
    <Admin on:navigate={handlePageChange} />
  {/if}
</div>
```

### `src/components/Login.svelte`

```svelte
<script>
  import { sessionToken, userId, username, isAdmin } from '../lib/store.js'
  import { login } from '../lib/api.js'
  import { createEventDispatcher } from 'svelte'

  const dispatch = createEventDispatcher()
  
  let formUsername = ''
  let formPassword = ''
  let loading = false
  let error = ''

  async function handleLogin() {
    if (!formUsername || !formPassword) {
      error = 'Usuario y contraseña requeridos'
      return
    }

    loading = true
    error = ''

    try {
      const result = await login(formUsername, formPassword)
      
      if (result.success) {
        // Guardar en localStorage
        localStorage.setItem('session_token', result.session_token)
        localStorage.setItem('user_id', result.user_id)
        localStorage.setItem('username', result.username)
        localStorage.setItem('is_admin', result.is_admin)

        // Actualizar stores
        sessionToken.set(result.session_token)
        userId.set(result.user_id)
        username.set(result.username)
        isAdmin.set(result.is_admin)

        // Navegar a menú
        dispatch('navigate', 'menu')
      } else {
        error = result.error || 'Error desconocido'
      }
    } catch (err) {
      error = 'Error de conexión: ' + err.message
    } finally {
      loading = false
    }
  }

  function handleAdminLogin() {
    dispatch('navigate', 'admin')
  }

  function handleKeyDown(e) {
    if (e.key === 'Enter') {
      handleLogin()
    }
  }
</script>

<div class="flex items-center justify-center h-screen bg-gradient-to-br from-blue-900 to-gray-900">
  <div class="bg-gray-800 p-8 rounded-lg shadow-2xl w-96">
    <h1 class="text-3xl font-bold mb-8 text-center">Videojuego P2P</h1>
    
    <input
      type="text"
      placeholder="Usuario"
      bind:value={formUsername}
      on:keydown={handleKeyDown}
      class="w-full px-4 py-2 mb-4 bg-gray-700 text-white rounded border border-gray-600 focus:border-blue-500 focus:outline-none"
    />
    
    <input
      type="password"
      placeholder="Contraseña"
      bind:value={formPassword}
      on:keydown={handleKeyDown}
      class="w-full px-4 py-2 mb-6 bg-gray-700 text-white rounded border border-gray-600 focus:border-blue-500 focus:outline-none"
    />
    
    {#if error}
      <div class="bg-red-900 text-red-100 p-3 rounded mb-4">
        {error}
      </div>
    {/if}
    
    <button
      on:click={handleLogin}
      disabled={loading}
      class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-600 text-white font-bold py-2 px-4 rounded mb-2"
    >
      {loading ? 'Ingresando...' : 'LOGIN'}
    </button>
    
    <button
      on:click={handleAdminLogin}
      class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded"
    >
      ADMIN
    </button>
  </div>
</div>
```

### `src/components/MainMenu.svelte`

```svelte
<script>
  import { sessionToken, username } from '../lib/store.js'
  import { logout } from '../lib/api.js'
  import { createEventDispatcher } from 'svelte'
  import CreateRoom from './CreateRoom.svelte'
  import JoinRoom from './JoinRoom.svelte'

  const dispatch = createEventDispatcher()

  let view = 'main' // 'main', 'create', 'join'
  let loading = false

  async function handleLogout() {
    await logout($sessionToken)
    localStorage.clear()
    dispatch('navigate', 'login')
  }

  function handleBack() {
    view = 'main'
  }
</script>

<div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-blue-900 to-gray-900 p-4">
  {#if view === 'main'}
    <div class="bg-gray-800 p-8 rounded-lg shadow-2xl w-full max-w-md">
      <h1 class="text-3xl font-bold mb-2 text-center">Bienvenido</h1>
      <p class="text-gray-400 text-center mb-6">{$username}</p>
      
      <button
        on:click={() => view = 'create'}
        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded mb-3"
      >
        🎮 CREAR PARTIDA
      </button>
      
      <button
        on:click={() => view = 'join'}
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded mb-3"
      >
        👥 UNIRSE A PARTIDA
      </button>
      
      <button
        on:click={handleLogout}
        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded"
      >
        🚪 LOGOUT
      </button>
    </div>

  {:else if view === 'create'}
    <CreateRoom on:back={handleBack} on:navigate={e => dispatch('navigate', e.detail)} />

  {:else if view === 'join'}
    <JoinRoom on:back={handleBack} on:navigate={e => dispatch('navigate', e.detail)} />
  {/if}
</div>
```

### `src/components/Room.svelte` (Partida)

```svelte
<script>
  import { sessionToken, userId, username } from '../lib/store.js'
  import { createEventDispatcher, onMount } from 'svelte'
  import VideoGrid from './VideoGrid.svelte'
  import { WebRTCManager } from '../lib/webrtc.js'
  import * as api from '../lib/api.js'

  export let roomCode = ''
  export let roomId = null
  export let role = 'player' // 'player' o 'spectator'
  export let playerIndex = 0

  const dispatch = createEventDispatcher()

  let webrtcManager
  let otherPlayers = []
  let remoteStreams = {}
  let localStream = null
  let soundEnabled = true
  let alertMessage = ''
  let showAlert = false

  let lastSignalId = 0
  let lastAlertId = 0
  let pollInterval

  onMount(async () => {
    // Iniciar WebRTC
    webrtcManager = new WebRTCManager()
    
    // Obtener cámara local
    try {
      localStream = await navigator.mediaDevices.getUserMedia({
        video: { width: 1280, height: 720 },
        audio: false
      })
    } catch (error) {
      console.error('Error accediendo a cámara:', error)
      alert('No se pudo acceder a la cámara')
      return
    }

    // Conectarse con otros jugadores
    for (const player of otherPlayers) {
      await webrtcManager.connect(player.user_id, $sessionToken, roomId, localStream)
    }

    // Escuchar streams remotos
    webrtcManager.on('stream', (userId, stream) => {
      remoteStreams[userId] = stream
      remoteStreams = remoteStreams // Trigger reactivity
    })

    // Iniciar polling
    startPolling()

    return () => {
      // Cleanup
      if (pollInterval) clearInterval(pollInterval)
      if (localStream) {
        localStream.getTracks().forEach(t => t.stop())
      }
      webrtcManager.closeAll()
    }
  })

  async function startPolling() {
    pollInterval = setInterval(async () => {
      // Polling de señales WebRTC
      const signalsRes = await api.pollSignals($sessionToken, roomId, lastSignalId)
      if (signalsRes.success && signalsRes.signals.length > 0) {
        for (const signal of signalsRes.signals) {
          await webrtcManager.processSignal(signal)
          lastSignalId = signal.signal_id
        }
        // Marcar como consumidas
        await api.markSignalsConsumed($sessionToken, signalsRes.signals.map(s => s.signal_id))
      }

      // Polling de alertas
      const alertsRes = await api.pollAlerts($sessionToken, roomId, lastAlertId)
      if (alertsRes.success && alertsRes.alerts.length > 0) {
        for (const alert of alertsRes.alerts) {
          displayAlert(alert)
          lastAlertId = alert.alert_id
        }
        // Marcar como consumidas
        await api.markAlertsConsumed($sessionToken, alertsRes.alerts.map(a => a.alert_id))
      }
    }, 1500)
  }

  async function triggerAlert() {
    await api.triggerAlert($sessionToken, roomId)
    displayAlert({ triggered_by_username: username })
  }

  function displayAlert(alert) {
    alertMessage = `⚠️ ALERTA de ${alert.triggered_by_username}!`
    showAlert = true

    // Reproducir sonido
    const audio = new Audio('/assets/alert-sound.mp3')
    audio.play().catch(e => console.error('Error reproduciendo sonido:', e))

    // Ocultar después de 2 segundos
    setTimeout(() => {
      showAlert = false
    }, 2000)
  }

  function toggleSound() {
    soundEnabled = !soundEnabled
  }

  async function handleLeave() {
    await api.leaveRoom($sessionToken, roomId)
    if (pollInterval) clearInterval(pollInterval)
    if (localStream) {
      localStream.getTracks().forEach(t => t.stop())
    }
    webrtcManager.closeAll()
    dispatch('navigate', 'menu')
  }
</script>

<div class="h-screen bg-gray-900 flex flex-col">
  <div class="bg-gray-800 p-4 flex justify-between items-center border-b border-gray-700">
    <div class="text-white font-bold">
      Código: <span class="text-blue-400">{roomCode}</span>
    </div>
    
    <div class="flex gap-2">
      <button
        on:click={toggleSound}
        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded"
      >
        {soundEnabled ? '🔊' : '🔇'} SON
      </button>
      
      <button
        on:click={triggerAlert}
        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded font-bold"
      >
        ⚠️ ALERTA
      </button>
      
      <button
        on:click={handleLeave}
        class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded"
      >
        🚪 SALIR
      </button>
    </div>
  </div>

  <div class="flex-1 p-4 overflow-auto">
    <VideoGrid {localStream} {remoteStreams} {playerIndex} />
  </div>

  {#if showAlert}
    <div class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg animate-pulse z-50">
      {alertMessage}
    </div>
  {/if}
</div>

<style>
  :global(body) {
    margin: 0;
    padding: 0;
  }
</style>
```

### `src/components/VideoGrid.svelte`

```svelte
<script>
  export let localStream = null
  export let remoteStreams = {}
  export let playerIndex = 0

  let expandedIndex = null
  let videoRefs = {}

  function toggleExpand(index) {
    expandedIndex = expandedIndex === index ? null : index
  }

  // Actualizar elementos video cuando cambian los streams
  $: {
    if (videoRefs[0] && localStream) {
      videoRefs[0].srcObject = localStream
    }
  }
</script>

<div class="w-full h-full">
  {#if expandedIndex !== null}
    <!-- Vista expandida -->
    <div class="w-full h-full relative bg-black">
      <video
        bind:this={videoRefs[expandedIndex]}
        autoplay
        playsinline
        class="w-full h-full object-cover"
      />
      <button
        on:click={() => expandedIndex = null}
        class="absolute top-4 right-4 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded"
      >
        ✕ Volver
      </button>
    </div>
  {:else}
    <!-- Vista de grilla 2 cámaras -->
    <div class="grid grid-cols-2 gap-4 h-full">
      <!-- Cámara Local (Tu cámara) -->
      <div
        on:click={() => toggleExpand(playerIndex)}
        class="bg-black rounded-lg overflow-hidden cursor-pointer hover:ring-2 hover:ring-blue-500 relative group"
      >
        <video
          bind:this={videoRefs[playerIndex]}
          autoplay
          playsinline
          muted
          class="w-full h-full object-cover"
        />
        <div class="absolute bottom-2 left-2 bg-black bg-opacity-50 text-white px-2 py-1 rounded text-sm">
          Tu Cámara
        </div>
        <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 group-hover:bg-opacity-20 transition">
          <span class="text-white text-sm opacity-0 group-hover:opacity-100">Ampliar</span>
        </div>
      </div>

      <!-- Cámara Remota (Oponente) -->
      <div
        on:click={() => toggleExpand(1 - playerIndex)}
        class="bg-black rounded-lg overflow-hidden cursor-pointer hover:ring-2 hover:ring-blue-500 relative group"
      >
        {#if Object.keys(remoteStreams).length > 0}
          {@const remoteIndex = Object.keys(remoteStreams)[0]}
          <video
            bind:this={videoRefs[remoteIndex]}
            autoplay
            playsinline
            class="w-full h-full object-cover"
          />
          <div class="absolute bottom-2 left-2 bg-black bg-opacity-50 text-white px-2 py-1 rounded text-sm">
            Oponente
          </div>
        {:else}
          <div class="w-full h-full flex items-center justify-center text-gray-400">
            Esperando conexión...
          </div>
        {/if}
        <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 group-hover:bg-opacity-20 transition">
          <span class="text-white text-sm opacity-0 group-hover:opacity-100">Ampliar</span>
        </div>
      </div>
    </div>
  {/if}
</div>
```

### `src/components/CreateRoom.svelte`

```svelte
<script>
  import { sessionToken } from '../lib/store.js'
  import { createRoom } from '../lib/api.js'
  import { createEventDispatcher } from 'svelte'

  const dispatch = createEventDispatcher()
  
  let loading = true
  let roomCode = ''
  let roomId = null
  let error = ''

  async function handleCreate() {
    loading = true
    error = ''

    try {
      const result = await createRoom($sessionToken)
      
      if (result.success) {
        roomCode = result.code
        roomId = result.room_id
        
        // Navegar a sala
        dispatch('navigate', {
          page: 'room',
          code: roomCode,
          id: roomId,
          role: 'player',
          playerIndex: 0
        })
      } else {
        error = result.error || 'Error creando partida'
      }
    } catch (err) {
      error = 'Error de conexión: ' + err.message
    } finally {
      loading = false
    }
  }

  handleCreate() // Crear automáticamente al cargar
</script>

<div class="bg-gray-800 p-8 rounded-lg shadow-2xl w-full max-w-md">
  <h2 class="text-2xl font-bold mb-6 text-center">Crear Partida</h2>
  
  {#if loading}
    <div class="flex justify-center mb-4">
      <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-blue-500"></div>
    </div>
    <p class="text-center text-gray-300">Generando código...</p>
  {:else if roomCode}
    <div class="bg-green-900 border border-green-500 p-4 rounded mb-6">
      <p class="text-green-100 text-center text-sm mb-2">Tu código:</p>
      <p class="text-white text-center text-3xl font-bold font-mono">{roomCode}</p>
      <p class="text-green-200 text-center text-sm mt-2">Comparte este código con otro jugador</p>
    </div>
    
    <button
      disabled
      class="w-full bg-green-600 text-white font-bold py-3 px-4 rounded"
    >
      ✓ Esperando jugador...
    </button>
  {/if}
  
  {#if error}
    <div class="bg-red-900 text-red-100 p-3 rounded mb-4">
      {error}
    </div>
  {/if}
  
  <button
    on:click={() => dispatch('back')}
    class="w-full bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 px-4 rounded mt-3"
  >
    ← VOLVER
  </button>
</div>
```

### `src/components/JoinRoom.svelte`

```svelte
<script>
  import { sessionToken } from '../lib/store.js'
  import { joinRoom, spectate } from '../lib/api.js'
  import { createEventDispatcher } from 'svelte'

  const dispatch = createEventDispatcher()
  
  let roomCode = ''
  let loading = false
  let error = ''

  async function handleJoin() {
    if (!roomCode) {
      error = 'Ingresa un código'
      return
    }

    loading = true
    error = ''

    try {
      const result = await joinRoom($sessionToken, roomCode.toUpperCase())
      
      if (result.success) {
        dispatch('navigate', {
          page: 'room',
          code: roomCode,
          id: result.room_id,
          role: 'player',
          playerIndex: result.your_player_index,
          otherPlayers: result.other_players
        })
      } else {
        error = result.error || 'Error uniéndose a partida'
      }
    } catch (err) {
      error = 'Error de conexión: ' + err.message
    } finally {
      loading = false
    }
  }

  async function handleSpectate() {
    if (!roomCode) {
      error = 'Ingresa un código'
      return
    }

    loading = true
    error = ''

    try {
      const result = await spectate($sessionToken, roomCode.toUpperCase())
      
      if (result.success) {
        dispatch('navigate', {
          page: 'room',
          code: roomCode,
          id: result.room_id,
          role: 'spectator',
          otherPlayers: result.players
        })
      } else {
        error = result.error || 'Error uniéndose como espectador'
      }
    } catch (err) {
      error = 'Error de conexión: ' + err.message
    } finally {
      loading = false
    }
  }

  function handleKeyDown(e) {
    if (e.key === 'Enter') {
      handleJoin()
    }
  }
</script>

<div class="bg-gray-800 p-8 rounded-lg shadow-2xl w-full max-w-md">
  <h2 class="text-2xl font-bold mb-6 text-center">Unirse a Partida</h2>
  
  <input
    type="text"
    placeholder="Código (ej: ABC123)"
    bind:value={roomCode}
    on:keydown={handleKeyDown}
    class="w-full px-4 py-2 mb-4 bg-gray-700 text-white rounded border border-gray-600 focus:border-blue-500 focus:outline-none uppercase"
  />
  
  {#if error}
    <div class="bg-red-900 text-red-100 p-3 rounded mb-4">
      {error}
    </div>
  {/if}
  
  <button
    on:click={handleJoin}
    disabled={loading}
    class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-600 text-white font-bold py-3 px-4 rounded mb-2"
  >
    {loading ? 'Conectando...' : '👥 UNIRSE COMO JUGADOR'}
  </button>
  
  <button
    on:click={handleSpectate}
    disabled={loading}
    class="w-full bg-purple-600 hover:bg-purple-700 disabled:bg-gray-600 text-white font-bold py-3 px-4 rounded mb-3"
  >
    {loading ? 'Conectando...' : '👁️ VER COMO ESPECTADOR'}
  </button>
  
  <button
    on:click={() => dispatch('back')}
    class="w-full bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 px-4 rounded"
  >
    ← VOLVER
  </button>
</div>
```

### `src/components/Admin.svelte`

```svelte
<script>
  import { sessionToken, isAdmin } from '../lib/store.js'
  import { createUser, deleteUser, listUsers } from '../lib/api.js'
  import { createEventDispatcher, onMount } from 'svelte'

  const dispatch = createEventDispatcher()

  let users = []
  let newUsername = ''
  let newPassword = ''
  let loading = false
  let error = ''
  let success = ''

  onMount(async () => {
    await loadUsers()
  })

  async function loadUsers() {
    try {
      const result = await listUsers($sessionToken)
      if (result.success) {
        users = result.users
      } else {
        error = result.error || 'Error cargando usuarios'
      }
    } catch (err) {
      error = 'Error de conexión: ' + err.message
    }
  }

  async function handleCreateUser() {
    if (!newUsername || !newPassword) {
      error = 'Usuario y contraseña requeridos'
      return
    }

    loading = true
    error = ''
    success = ''

    try {
      const result = await createUser($sessionToken, newUsername, newPassword)
      
      if (result.success) {
        success = `Usuario ${newUsername} creado`
        newUsername = ''
        newPassword = ''
        await loadUsers()
      } else {
        error = result.error || 'Error creando usuario'
      }
    } catch (err) {
      error = 'Error de conexión: ' + err.message
    } finally {
      loading = false
    }
  }

  async function handleDeleteUser(userId) {
    if (!confirm('¿Eliminar este usuario?')) return

    try {
      const result = await deleteUser($sessionToken, userId)
      
      if (result.success) {
        success = 'Usuario eliminado'
        await loadUsers()
      } else {
        error = result.error || 'Error eliminando usuario'
      }
    } catch (err) {
      error = 'Error de conexión: ' + err.message
    }
  }

  function handleLogout() {
    dispatch('navigate', 'login')
  }
</script>

<div class="min-h-screen bg-gradient-to-br from-blue-900 to-gray-900 p-4">
  {#if !$isAdmin}
    <div class="flex items-center justify-center h-screen">
      <div class="bg-red-900 text-red-100 p-6 rounded-lg">
        ⛔ Acceso Denegado: Solo administradores
      </div>
    </div>
  {:else}
    <div class="max-w-2xl mx-auto">
      <div class="bg-gray-800 p-8 rounded-lg shadow-2xl">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-white">Panel de Admin</h1>
          <button
            on:click={handleLogout}
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded"
          >
            LOGOUT
          </button>
        </div>

        <!-- Crear usuario -->
        <div class="mb-8">
          <h2 class="text-xl font-bold text-white mb-4">Crear Usuario</h2>
          
          <input
            type="text"
            placeholder="Usuario"
            bind:value={newUsername}
            class="w-full px-4 py-2 mb-3 bg-gray-700 text-white rounded border border-gray-600 focus:border-blue-500 focus:outline-none"
          />
          
          <input
            type="password"
            placeholder="Contraseña"
            bind:value={newPassword}
            class="w-full px-4 py-2 mb-3 bg-gray-700 text-white rounded border border-gray-600 focus:border-blue-500 focus:outline-none"
          />
          
          {#if error}
            <div class="bg-red-900 text-red-100 p-3 rounded mb-3">
              {error}
            </div>
          {/if}
          
          {#if success}
            <div class="bg-green-900 text-green-100 p-3 rounded mb-3">
              {success}
            </div>
          {/if}
          
          <button
            on:click={handleCreateUser}
            disabled={loading}
            class="bg-green-600 hover:bg-green-700 disabled:bg-gray-600 text-white font-bold py-2 px-4 rounded"
          >
            {loading ? 'Creando...' : '✓ CREAR'}
          </button>
        </div>

        <!-- Listar usuarios -->
        <div>
          <h2 class="text-xl font-bold text-white mb-4">Usuarios Existentes</h2>
          
          <div class="space-y-2">
            {#each users as user (user.user_id)}
              <div class="bg-gray-700 p-3 rounded flex justify-between items-center">
                <div>
                  <p class="text-white font-bold">{user.username}</p>
                  <p class="text-gray-400 text-sm">{user.created_at}</p>
                </div>
                
                <div>
                  {#if user.is_admin}
                    <span class="bg-purple-600 text-white px-3 py-1 rounded text-sm">ADMIN</span>
                  {:else}
                    <button
                      on:click={() => handleDeleteUser(user.user_id)}
                      class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm"
                    >
                      ELIMINAR
                    </button>
                  {/if}
                </div>
              </div>
            {/each}
          </div>
        </div>
      </div>
    </div>
  {/if}
</div>
```

---

## 10. INTEGRACIÓN WEBRTC (simple-peer)

### `src/lib/webrtc.js`

```javascript
import SimplePeer from 'simple-peer'
import * as api from './api.js'

export class WebRTCManager {
  constructor() {
    this.peers = {} // {userId: SimplePeer}
    this.dataChannels = {} // {userId: RTCDataChannel}
    this.eventHandlers = {}
  }

  async connect(userId, sessionToken, roomId, localStream) {
    try {
      // Crear SimplePeer
      const peer = new SimplePeer({
        initiator: true, // Somos el que inicia
        trickle: true,
        stream: localStream,
        iceServers: await this.getSTUNServers(), // Obtenido de servidor
      })

      this.peers[userId] = peer

      // Evento: stream remoto recibido
      peer.on('stream', (stream) => {
        console.log('Stream recibido de', userId)
        if (this.eventHandlers['stream']) {
          this.eventHandlers['stream'](userId, stream)
        }
      })

      // Evento: signal (OFFER/ANSWER/candidate)
      peer.on('signal', async (signal) => {
        // Detectar tipo de señal
        const signalType = this.detectSignalType(signal)
        
        // Enviar al servidor
        await api.sendSignal(sessionToken, roomId, userId, signalType, signal)
      })

      // Evento: error
      peer.on('error', (err) => {
        console.error('WebRTC error:', err)
      })

      // Evento: conexión establecida
      peer.on('connect', () => {
        console.log('Conectado con', userId)
        if (this.eventHandlers['connected']) {
          this.eventHandlers['connected'](userId)
        }
      })

    } catch (error) {
      console.error('Error creando conexión P2P:', error)
    }
  }

  async processSignal(signal) {
    const peer = this.peers[signal.from_user_id]
    
    if (!peer) {
      // Si no existe peer, crear uno en modo responder
      // (Este peer recibirá un OFFER)
      const peer = new SimplePeer({
        initiator: false, // Respondemos
        trickle: true,
        iceServers: await this.getSTUNServers(),
      })

      this.peers[signal.from_user_id] = peer

      // Configurar eventos igual que en connect()
      // ... (mismo código)
    }

    try {
      // Procesar señal según tipo
      if (signal.signal_type === 'offer') {
        peer.signal(signal.signal_data)
      } else if (signal.signal_type === 'answer') {
        peer.signal(signal.signal_data)
      } else if (signal.signal_type === 'candidate') {
        peer.signal(signal.signal_data)
      }
    } catch (error) {
      console.error('Error procesando señal:', error)
    }
  }

  detectSignalType(signal) {
    if (signal.type === 'offer') return 'offer'
    if (signal.type === 'answer') return 'answer'
    if (signal.candidate) return 'candidate'
    return 'unknown'
  }

  async getSTUNServers() {
    // Retornar lista hardcoded de STUN servers
    // (O obtenerla del servidor)
    return [
      { urls: 'stun:stun.l.google.com:19302' },
      { urls: 'stun:stun1.l.google.com:19302' },
      { urls: 'stun:stun2.l.google.com:19302' },
      { urls: 'stun:stun3.l.google.com:19302' },
      { urls: 'stun:stun4.l.google.com:19302' },
    ]
  }

  on(event, handler) {
    this.eventHandlers[event] = handler
  }

  closeAll() {
    Object.values(this.peers).forEach(peer => {
      peer.destroy()
    })
    this.peers = {}
  }
}
```

### `src/lib/store.js` (Svelte Stores)

```javascript
import { writable } from 'svelte/store'

export const sessionToken = writable(localStorage.getItem('session_token') || null)
export const userId = writable(parseInt(localStorage.getItem('user_id') || '0'))
export const username = writable(localStorage.getItem('username') || '')
export const isAdmin = writable(localStorage.getItem('is_admin') === 'true')

// Suscribirse a cambios y actualizar localStorage
sessionToken.subscribe(value => {
  if (value) localStorage.setItem('session_token', value)
  else localStorage.removeItem('session_token')
})

userId.subscribe(value => {
  if (value) localStorage.setItem('user_id', value)
  else localStorage.removeItem('user_id')
})

username.subscribe(value => {
  if (value) localStorage.setItem('username', value)
  else localStorage.removeItem('username')
})

isAdmin.subscribe(value => {
  localStorage.setItem('is_admin', value)
})
```

### `src/lib/api.js`

```javascript
// Configurar BASE_URL según ambiente
const BASE_URL = import.meta.env.VITE_API_URL || '/api'

// ===== AUTENTICACIÓN =====

export async function login(username, password) {
  const res = await fetch(`${BASE_URL}/auth.php?action=login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, password })
  })
  return res.json()
}

export async function logout(sessionToken) {
  const res = await fetch(`${BASE_URL}/auth.php?action=logout`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ session_token: sessionToken })
  })
  return res.json()
}

// ===== SALAS =====

export async function createRoom(sessionToken) {
  const res = await fetch(`${BASE_URL}/rooms.php?action=create`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ session_token: sessionToken })
  })
  return res.json()
}

export async function joinRoom(sessionToken, code) {
  const res = await fetch(`${BASE_URL}/rooms.php?action=join`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ session_token: sessionToken, code })
  })
  return res.json()
}

export async function spectate(sessionToken, code) {
  const res = await fetch(`${BASE_URL}/rooms.php?action=spectate`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ session_token: sessionToken, code })
  })
  return res.json()
}

export async function leaveRoom(sessionToken, roomId) {
  const res = await fetch(`${BASE_URL}/rooms.php?action=leave`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ session_token: sessionToken, room_id: roomId })
  })
  return res.json()
}

// ===== SEÑALIZACIÓN WEBRTC =====

export async function sendSignal(sessionToken, roomId, toUserId, signalType, signalData) {
  const res = await fetch(`${BASE_URL}/signal.php?action=send`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      session_token: sessionToken,
      room_id: roomId,
      to_user_id: toUserId,
      signal_type: signalType,
      signal_data: signalData
    })
  })
  return res.json()
}

export async function pollSignals(sessionToken, roomId, sinceId = 0) {
  const res = await fetch(
    `${BASE_URL}/signal.php?action=poll&room_id=${roomId}&session_token=${sessionToken}&since_id=${sinceId}`
  )
  return res.json()
}

export async function markSignalsConsumed(sessionToken, signalIds) {
  const res = await fetch(`${BASE_URL}/signal.php?action=mark_consumed`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ session_token: sessionToken, signal_ids: signalIds })
  })
  return res.json()
}

// ===== ALERTAS =====

export async function triggerAlert(sessionToken, roomId) {
  const res = await fetch(`${BASE_URL}/alerts.php?action=trigger`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ session_token: sessionToken, room_id: roomId })
  })
  return res.json()
}

export async function pollAlerts(sessionToken, roomId, sinceId = 0) {
  const res = await fetch(
    `${BASE_URL}/alerts.php?action=poll&room_id=${roomId}&session_token=${sessionToken}&since_id=${sinceId}`
  )
  return res.json()
}

export async function markAlertsConsumed(sessionToken, alertIds) {
  const res = await fetch(`${BASE_URL}/alerts.php?action=mark_consumed`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ session_token: sessionToken, alert_ids: alertIds })
  })
  return res.json()
}

// ===== ADMIN =====

export async function createUser(sessionToken, username, password) {
  const res = await fetch(`${BASE_URL}/admin.php?action=create_user`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ session_token: sessionToken, username, password })
  })
  return res.json()
}

export async function deleteUser(sessionToken, userId) {
  const res = await fetch(`${BASE_URL}/admin.php?action=delete_user`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ session_token: sessionToken, user_id: userId })
  })
  return res.json()
}

export async function listUsers(sessionToken) {
  const res = await fetch(
    `${BASE_URL}/admin.php?action=list_users&session_token=${sessionToken}`
  )
  return res.json()
}
```

### `src/styles/app.css`

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom Animations */
@keyframes flash {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.animate-flash {
  animation: flash 0.5s infinite;
}

/* Video Grid */
.video-container {
  position: relative;
  width: 100%;
  padding-bottom: 75%; /* Ratio 4:3 */
  background: #000;
  border-radius: 0.5rem;
  overflow: hidden;
}

.video-container video {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Responsive */
@media (max-width: 640px) {
  .grid-cols-2 {
    grid-template-columns: 1fr;
  }
}
```

---

## 11. TESTING VITEST

### `tests/setup.js`

```javascript
import { expect, afterEach, vi } from 'vitest'
import { cleanup } from '@testing-library/svelte'

// Cleanup después de cada test
afterEach(() => {
  cleanup()
})

// Mock de localStorage
const localStorageMock = (() => {
  let store = {}
  return {
    getItem: (key) => store[key] || null,
    setItem: (key, value) => {
      store[key] = value.toString()
    },
    removeItem: (key) => {
      delete store[key]
    },
    clear: () => {
      store = {}
    }
  }
})()

Object.defineProperty(window, 'localStorage', {
  value: localStorageMock
})

// Mock de navigator.mediaDevices
Object.defineProperty(navigator, 'mediaDevices', {
  value: {
    getUserMedia: vi.fn(async () => ({
      getTracks: () => [],
      getVideoTracks: () => [],
      getAudioTracks: () => []
    }))
  }
})
```

### `tests/api.test.js`

```javascript
import { describe, it, expect, beforeEach, vi } from 'vitest'
import * as api from '../src/lib/api.js'

describe('API Functions', () => {
  beforeEach(() => {
    global.fetch = vi.fn()
  })

  it('should call login endpoint', async () => {
    fetch.mockResolvedValueOnce({
      json: () => Promise.resolve({ success: true, session_token: 'token123' })
    })

    const result = await api.login('user', 'pass')
    
    expect(result.success).toBe(true)
    expect(result.session_token).toBe('token123')
  })

  it('should handle login error', async () => {
    fetch.mockResolvedValueOnce({
      json: () => Promise.resolve({ success: false, error: 'Invalid credentials' })
    })

    const result = await api.login('user', 'wrongpass')
    
    expect(result.success).toBe(false)
    expect(result.error).toBe('Invalid credentials')
  })

  it('should create room', async () => {
    fetch.mockResolvedValueOnce({
      json: () => Promise.resolve({ 
        success: true, 
        room_id: 42, 
        code: 'ABC123',
        stun_servers: []
      })
    })

    const result = await api.createRoom('token123')
    
    expect(result.success).toBe(true)
    expect(result.code).toBe('ABC123')
  })
})
```

### `tests/components/Login.test.js`

```javascript
import { describe, it, expect, vi } from 'vitest'
import { render, screen, fireEvent } from '@testing-library/svelte'
import Login from '../../src/components/Login.svelte'

describe('Login Component', () => {
  it('should render login form', () => {
    render(Login)
    
    expect(screen.getByPlaceholderText('Usuario')).toBeTruthy()
    expect(screen.getByPlaceholderText('Contraseña')).toBeTruthy()
  })

  it('should display error on empty fields', async () => {
    const { component } = render(Login)
    
    const button = screen.getByText('LOGIN')
    await fireEvent.click(button)

    expect(screen.getByText(/requeridos/i)).toBeTruthy()
  })

  it('should submit login', async () => {
    global.fetch = vi.fn(() => 
      Promise.resolve({
        json: () => Promise.resolve({ 
          success: true, 
          session_token: 'token123',
          user_id: 1,
          username: 'testuser',
          is_admin: false
        })
      })
    )

    const { component } = render(Login)
    
    const userInput = screen.getByPlaceholderText('Usuario')
    const passInput = screen.getByPlaceholderText('Contraseña')
    const button = screen.getByText('LOGIN')

    await fireEvent.update(userInput, 'testuser')
    await fireEvent.update(passInput, 'password')
    await fireEvent.click(button)

    // Verificar que fetch fue llamado
    expect(global.fetch).toHaveBeenCalled()
  })
})
```

---

## 12. BUILD Y DEPLOYMENT

### `.env.local` (Desarrollo)

```
VITE_API_URL=http://localhost:8000/api
```

### `.env.production` (Producción)

```
VITE_API_URL=/api
```

### Scripts de Build

```bash
# Desarrollo con HMR (Hot Module Replacement)
npm run dev

# Build para producción (minificado)
npm run build

# Preview de build local
npm run preview

# Correr tests
npm run test

# Correr tests con UI
npm run test:ui

# Correr tests una sola vez (CI/CD)
npm run test:run
```

### Proceso de Compilación

```
1. npm run test:run          # Verificar que pasen tests
2. npm run build             # Compilar a /dist/
3. Copiar /dist/* a servidor OVH
4. Copiar /api/* a servidor OVH (endpoints PHP)
5. Ejecutar setup.php una sola vez
6. ✓ Acceder a tudominio.com/dist/index.html
```

### Estructura en Servidor (Después de Deploy)

```
/home/usuario/public_html/
├── dist/                              # Archivos compilados (Svelte)
│   ├── index.html
│   ├── assets/
│   │   ├── index-XXXXX.js
│   │   ├── index-XXXXX.css
│   │   └── alert-sound-XXXXX.mp3
│   └── ...
│
├── api/                               # Endpoints PHP
│   ├── auth.php
│   ├── rooms.php
│   ├── signal.php
│   ├── alerts.php
│   └── admin.php
│
├── db/                                # SQLite
│   └── game.sqlite3
│
├── config.php
├── db.php
├── utils.php
└── setup.php                          # (Ejecutar una sola vez)
```

### Archivo `.htaccess` (Reescritura URLs - Opcional)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # No reescribir archivos/directorios existentes
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    
    # Servir index.html para rutas
    RewriteRule ^ index.html [QSA,L]
</IfModule>
```

---

## 13. PSEUDO-CÓDIGO BACKEND (PHP)

(Ver Documento v1, secciones 9.1 - 9.8)

**Cambios mínimos:**
- Los endpoints son idénticos
- La BD es idéntica
- La configuración es idéntica

Lo único que cambia es el cliente (Svelte en lugar de vanilla JS).

---

## 14. CONSIDERACIONES DE SEGURIDAD

(Idéntico a Documento v1, sección 10)

### Añadido: CORS en PHP

Si el frontend está en diferente dominio:

```php
<?php
// En todos los endpoints PHP

header('Access-Control-Allow-Origin: https://tudominio.com');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
?>
```

---

## 15. GUÍA RÁPIDA PARA IA PROGRAMADORA

### Fase 1: Backend (Sin cambios de v1)
1. Crear estructura `/api/`, `/db/`, archivos PHP
2. Implementar endpoints (auth, rooms, signal, alerts, admin)
3. Crear BD SQLite automáticamente
4. Testear endpoints con Postman/cURL

### Fase 2: Frontend (Nuevo con Svelte)
1. Crear proyecto: `npm create vite@latest -- --template svelte`
2. Instalar dependencias: `npm install`
3. Crear componentes en `/src/components/`
4. Implementar lógica en `/src/lib/`
5. Configurar Vite + Tailwind
6. Testear con `npm run dev`

### Fase 3: WebRTC (simple-peer)
1. Implementar `WebRTCManager` en `/src/lib/webrtc.js`
2. Integrar en componente `Room.svelte`
3. Testear conexión P2P con 2 navegadores
4. Ajustar polling interval según lag

### Fase 4: Testing
1. Escribir tests en `/tests/`
2. Correr `npm run test`
3. Asegurar cobertura básica

### Fase 5: Build & Deploy
1. Correr `npm run build`
2. Copiar `/dist/` al servidor
3. Copiar `/api/`, config.php, db.php al servidor
4. Ejecutar setup.php
5. ✓ Acceder y testear

---

## 📊 COMPARATIVA v1 vs v2

| Aspecto | v1 (Vanilla JS) | v2 (Svelte) |
|--------|-----------------|------------|
| Tamaño JS | ~80KB | ~50KB |
| Reactividad | Manual | Automática |
| Mantenibilidad | Media | Alta |
| Curva aprendizaje IA | Baja | Media |
| Performance | Bueno | Excelente |
| Bundling | Ninguno | Vite |
| Testing | Difícil | Fácil |
| Desarrollo | Lento | Rápido |

---

## ✅ CHECKLIST PRE-DEPLOY v2

- [ ] `npm run test:run` pasa (0 errores)
- [ ] `npm run build` genera `/dist/`
- [ ] Tamaño `/dist/` < 250KB
- [ ] Backend PHP funcionando (endpoints testados)
- [ ] SQLite creada automáticamente
- [ ] Admin usuario: admin / admin123
- [ ] STUN servers configurados
- [ ] HTTPS habilitado en servidor
- [ ] `/dist/` copiado al servidor
- [ ] `/api/` copiado al servidor
- [ ] setup.php ejecutado y eliminado
- [ ] Login funciona
- [ ] Crear partida funciona
- [ ] WebRTC P2P funciona (2 navegadores)
- [ ] Alertas funcionan
- [ ] Espectadores funcionan

---

**FIN DEL DOCUMENTO TÉCNICO v2.0**

**Versión:** 2.0  
**Stack:** Svelte + Vite + simple-peer + Tailwind  
**Estado:** Listo para desarrollo  
**Compatibilidad:** Firefox, Chrome, Edge, Safari (iOS 11+)  
**Tamaño final:** ~200KB gzipped

