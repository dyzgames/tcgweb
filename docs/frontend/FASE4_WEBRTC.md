# 🔌 FASE 4: WEBRTC CON SIMPLE-PEER

**Objetivo:** Implementar streaming de video P2P entre jugadores  
**Duración:** 2-3 sesiones  
**Prerequisito:** Fase 3 completada  
**Complejidad:** MEDIA-ALTA

---

## 📝 QUÉ HACE SIMPLE-PEER

`simple-peer` es una librería que abstrae toda la complejidad de WebRTC:

```javascript
// Sin simple-peer (COMPLEJO):
const pc = new RTCPeerConnection()
const offer = await pc.createOffer()
// ... 50 líneas de ICE candidates, SDP parsing, etc

// Con simple-peer (SIMPLE):
const peer = new SimplePeer({ initiator: true })
peer.on('stream', stream => video.srcObject = stream)
// ¡Eso es todo!
```

---

## 🎯 ARQUITECTURA WEBRTC

```
┌────────────────┐                    ┌────────────────┐
│  Jugador 1     │                    │  Jugador 2     │
│  (Initiator)   │                    │  (Non-init)    │
└────────────────┘                    └────────────────┘
       │                                       │
       │ 1. Crear peer (iniciador)            │
       │ 2. Generar OFFER                     │
       │    ↓                                  │
       └──────────────────────────────────────→
              Señalización HTTP
       ←──────────────────────────────────────┘
       │ 3. Recibir ANSWER                    │
       │ 4. Intercambiar ICE candidates      │
       │    ↓                                  │
       └──→ CONEXIÓN P2P ESTABLECIDA ←────────┘
       │                                       │
       ├─── Stream de video/audio ────────────┤
       ├─── Stream inverso ───────────────────┤
       │                                       │
```

---

## 🔨 TAREA: CREAR `webrtc.js`

**Archivo:** `src/lib/webrtc.js`

Este archivo gestiona toda la lógica de WebRTC.

```javascript
import SimplePeer from 'simple-peer'
import { videoStore } from './store.js'

export class WebRTCManager {
  constructor(options = {}) {
    this.peer = null
    this.localStream = null
    this.remoteStream = null
    this.roomId = null
    this.userId = null
    this.isInitiator = options.isInitiator || false
    this.stunServers = options.stunServers || [
      { urls: ['stun:stun.l.google.com:19302'] },
      { urls: ['stun:stun1.l.google.com:19302'] }
    ]
  }

  // 1. Obtener acceso a webcam/micrófono
  async getLocalStream() {
    try {
      this.localStream = await navigator.mediaDevices.getUserMedia({
        video: { width: 1280, height: 720 },
        audio: true
      })
      videoStore.update(store => ({
        ...store,
        localStream: this.localStream
      }))
      return this.localStream
    } catch (error) {
      console.error('Error accediendo a cámara:', error)
      throw error
    }
  }

  // 2. Inicializar conexión P2P
  async initializePeer(signalCallback) {
    // Asegurar que tenemos stream local
    if (!this.localStream) {
      await this.getLocalStream()
    }

    this.peer = new SimplePeer({
      initiator: this.isInitiator,
      stream: this.localStream,
      config: {
        iceServers: this.stunServers
      }
    })

    // Cuando generamos señal (OFFER/ANSWER/ICE)
    this.peer.on('signal', async (data) => {
      console.log('Signal generada:', data.type)
      // Enviar al servidor para que la reenvíe al otro jugador
      await signalCallback(data)
    })

    // Cuando recibimos stream del otro jugador
    this.peer.on('stream', (stream) => {
      console.log('Stream remoto recibido')
      this.remoteStream = stream
      videoStore.update(store => ({
        ...store,
        remoteStream: stream
      }))
    })

    // Errores de conexión
    this.peer.on('error', (error) => {
      console.error('Error WebRTC:', error)
    })

    // Conexión establecida
    this.peer.on('connect', () => {
      console.log('✓ Conexión P2P establecida')
    })
  }

  // 3. Recibir señal del otro jugador
  async receiveSignal(signalData) {
    if (this.peer) {
      try {
        this.peer.signal(signalData)
      } catch (error) {
        console.error('Error procesando señal:', error)
      }
    }
  }

  // 4. Controlar audio
  setAudioEnabled(enabled) {
    if (this.localStream) {
      this.localStream.getAudioTracks().forEach(track => {
        track.enabled = enabled
      })
      videoStore.update(store => ({
        ...store,
        isAudioEnabled: enabled
      }))
    }
  }

  // 5. Controlar video
  setVideoEnabled(enabled) {
    if (this.localStream) {
      this.localStream.getVideoTracks().forEach(track => {
        track.enabled = enabled
      })
      videoStore.update(store => ({
        ...store,
        isVideoEnabled: enabled
      }))
    }
  }

  // 6. Cerrar conexión
  close() {
    if (this.localStream) {
      this.localStream.getTracks().forEach(track => track.stop())
    }
    if (this.peer) {
      this.peer.destroy()
    }
  }
}
```

---

## 🎬 TAREA: INTEGRAR EN `Room.svelte`

```svelte
<script>
  import { onMount } from 'svelte'
  import { roomStore, videoStore } from '../lib/store.js'
  import { sendSignal, getSignals } from '../lib/api.js'
  import { WebRTCManager } from '../lib/webrtc.js'
  import VideoGrid from './VideoGrid.svelte'
  import Alert from './Alert.svelte'
  
  let webrtc = null
  let pollInterval = null
  let lastSignalId = 0
  
  onMount(async () => {
    // 1. Obtener información de la sala
    const room = $roomStore
    
    // 2. Inicializar WebRTC
    const isInitiator = room.isInitiator // Quien creó la sala
    webrtc = new WebRTCManager({ isInitiator })
    
    // 3. Obtener stream local
    await webrtc.getLocalStream()
    
    // 4. Inicializar peer connection
    await webrtc.initializePeer(async (signalData) => {
      // Cuando generamos OFFER/ANSWER/ICE, enviamos al servidor
      await sendSignal(room.roomId, signalData.type, signalData, $authStore.sessionToken)
    })
    
    // 5. Polling para recibir señales del otro jugador
    pollInterval = setInterval(async () => {
      const result = await getSignals(room.roomId, lastSignalId)
      if (result.data && result.data.length > 0) {
        for (const signal of result.data) {
          if (signal.from_player_id !== $authStore.userId) {
            // Señal del OTRO jugador
            await webrtc.receiveSignal(signal.data)
            lastSignalId = signal.id
          }
        }
      }
    }, 1000) // Polling cada 1 segundo
    
    return () => {
      clearInterval(pollInterval)
      webrtc.close()
    }
  })
  
  function toggleAudio() {
    const enabled = !$videoStore.isAudioEnabled
    webrtc.setAudioEnabled(enabled)
  }
  
  function toggleVideo() {
    const enabled = !$videoStore.isVideoEnabled
    webrtc.setVideoEnabled(enabled)
  }
</script>

<div class="h-screen flex flex-col">
  <VideoGrid />
  
  <div class="flex gap-4 justify-center p-4 bg-gray-800">
    <button on:click={toggleAudio} class="btn-primary">
      {$videoStore.isAudioEnabled ? '🔊 Audio ON' : '🔇 Audio OFF'}
    </button>
    <button on:click={toggleVideo} class="btn-primary">
      {$videoStore.isVideoEnabled ? '📹 Video ON' : '🚫 Video OFF'}
    </button>
    <button class="btn-danger">
      Salir Partida
    </button>
  </div>
</div>
```

---

## 🔍 FLUJO COMPLETO

```
1. Jugador 1 crea partida
   → isInitiator = true
   → Genera OFFER
   → Envía OFFER al servidor
   → Servidor guarda OFFER

2. Jugador 2 se une a partida
   → isInitiator = false
   → Polling: obtiene OFFER
   → Genera ANSWER
   → Envía ANSWER al servidor

3. Jugador 1 polling obtiene ANSWER
   → Procesa ANSWER
   → Intercambian ICE candidates
   → ✓ CONEXIÓN ESTABLECIDA

4. Ambos reciben streams
   → videoStore se actualiza
   → VideoGrid muestra ambas cámaras
```

---

## ⚠️ PUNTOS CRÍTICOS

1. **STUN Servers:** Necesarios para NAT traversal
2. **Señalización:** HTTP Polling cada 1-2 segundos
3. **Orden:** OFFER → ANSWER → ICE candidates
4. **Streams:** Asegurar que ambos tienen stream local antes de conectar
5. **Cleanup:** Cerrar conexión cuando salir de sala

---

## ✅ CÓMO TESTEAR

```bash
# Terminal 1: Backend
php -S localhost:8000

# Terminal 2: Frontend
npm run dev
```

**Prueba con 2 navegadores:**

1. **Navegador 1:**
   - Login
   - Crear partida
   - Ver código

2. **Navegador 2:**
   - Login (otro usuario)
   - Unirse partida con código

3. **Verificar:**
   - ✓ Ambos ven sus propias cámaras
   - ✓ Ambos ven la cámara del otro
   - ✓ Audio funciona
   - ✓ Botones audio/video funcionan
   - ✓ Console sin errores

---

## 🆘 TROUBLESHOOTING

**"Cannot access mediaDevices"**
- Asegurar HTTPS en producción (HTTP falla en algunos navegadores)

**"Peer connection fails"**
- Verificar STUN servers públicos
- Checkear firewall/NAT

**"No veo video remoto"**
- Verificar polling interval (1-2 seg máximo)
- Revisar que OFFER/ANSWER se envíen correctamente

---

## 📌 PRÓXIMO PASO

Cuando Fase 4 esté lista:

**"Fase 4 completada. Voy con Fase 5: Testing"**

