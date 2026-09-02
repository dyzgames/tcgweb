import SimplePeer from 'simple-peer'
import { videoStore } from './store.js'

/**
 * WebRTCManager - gestiona una conexión P2P de vídeo/audio con simple-peer.
 *
 * simple-peer se encarga automáticamente de OFFER / ANSWER / ICE candidates.
 * La señalización (transporte de esos mensajes) se hace por HTTP polling
 * desde Room.svelte.
 */
export class WebRTCManager {
  constructor(options = {}) {
    this.peer = null
    this.localStream = null
    this.remoteStream = null
    this.roomId = options.roomId || null
    this.userId = options.userId || null
    this.isInitiator = options.isInitiator || false
    this.receiveOnly = options.receiveOnly || false   // espectador sin cámara
    this.destroyed = false

    this.stunServers = options.stunServers && options.stunServers.length
      ? options.stunServers
      : [
          { urls: ['stun:stun.l.google.com:19302'] },
          { urls: ['stun:stun1.l.google.com:19302'] },
          { urls: ['stun:stun2.l.google.com:19302'] },
          { urls: ['stun:stun3.l.google.com:19302'] },
          { urls: ['stun:stun4.l.google.com:19302'] }
        ]

    // Callbacks opcionales que Room.svelte puede asignar
    this.onConnect = options.onConnect || null
    this.onClose = options.onClose || null
    this.onError = options.onError || null
  }

  // 1. Obtener acceso a webcam/micrófono (con degradado si algo falla)
  async getLocalStream() {
    const constraints = [
      { video: { width: 1280, height: 720 }, audio: true },
      { video: true, audio: false },
      { video: false, audio: true }
    ]

    let lastError = null
    for (const c of constraints) {
      try {
        this.localStream = await navigator.mediaDevices.getUserMedia(c)
        videoStore.update(store => ({
          ...store,
          localStream: this.localStream,
          isVideoEnabled: this.localStream.getVideoTracks().length > 0,
          isAudioEnabled: this.localStream.getAudioTracks().length > 0
        }))
        return this.localStream
      } catch (error) {
        lastError = error
      }
    }

    console.error('Error accediendo a cámara/micrófono:', lastError)
    throw lastError
  }

  // 2. Inicializar conexión P2P
  async initializePeer(signalCallback) {
    if (!this.localStream && !this.receiveOnly) {
      await this.getLocalStream()
    }

    const peerOpts = {
      initiator: this.isInitiator,
      trickle: true,
      config: { iceServers: this.stunServers }
    }
    if (this.localStream) peerOpts.stream = this.localStream

    this.peer = new SimplePeer(peerOpts)

    // Cuando generamos señal (OFFER / ANSWER / ICE candidate)
    this.peer.on('signal', async (data) => {
      console.log('Signal generada:', data.type || 'candidate')
      try {
        await signalCallback(data)
      } catch (e) {
        console.error('Error enviando señal:', e)
      }
    })

    // Cuando recibimos stream del otro jugador
    this.peer.on('stream', (stream) => {
      console.log('Stream remoto recibido')
      this.remoteStream = stream
      videoStore.update(store => ({ ...store, remoteStream: stream }))
    })

    this.peer.on('connect', () => {
      console.log('✓ Conexión P2P establecida')
      if (this.onConnect) this.onConnect()
    })

    this.peer.on('close', () => {
      console.log('Conexión P2P cerrada')
      if (this.onClose) this.onClose()
    })

    this.peer.on('error', (error) => {
      console.error('Error WebRTC:', error)
      if (this.onError) this.onError(error)
    })
  }

  // 3. Recibir señal del otro jugador
  async receiveSignal(signalData) {
    if (!this.peer || this.destroyed) return
    try {
      this.peer.signal(signalData)
    } catch (error) {
      console.error('Error procesando señal:', error)
    }
  }

  // 4. Controlar audio
  setAudioEnabled(enabled) {
    if (this.localStream) {
      this.localStream.getAudioTracks().forEach(track => {
        track.enabled = enabled
      })
    }
    videoStore.update(store => ({ ...store, isAudioEnabled: enabled }))
  }

  // 5. Controlar video
  setVideoEnabled(enabled) {
    if (this.localStream) {
      this.localStream.getVideoTracks().forEach(track => {
        track.enabled = enabled
      })
    }
    videoStore.update(store => ({ ...store, isVideoEnabled: enabled }))
  }

  // 6. Cerrar conexión y liberar cámara/micrófono
  close() {
    this.destroyed = true
    if (this.localStream) {
      this.localStream.getTracks().forEach(track => track.stop())
      this.localStream = null
    }
    if (this.peer) {
      try { this.peer.destroy() } catch (e) { /* ignore */ }
      this.peer = null
    }
    videoStore.update(store => ({
      ...store,
      localStream: null,
      remoteStream: null
    }))
  }
}
