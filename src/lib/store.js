import { writable } from 'svelte/store'

export const authStore = writable({
  sessionToken: null,
  userId: null,
  username: null,
  isAdmin: false
})

export const roomStore = writable({
  roomId: null,
  code: null,
  players: [],
  role: 'player',       // 'player' | 'spectator'
  playerIndex: 0,       // 0 o 1
  stunServers: [],
  isStreaming: false
})

export const videoStore = writable({
  localStream: null,
  remoteStream: null,
  isAudioEnabled: true,
  isVideoEnabled: true
})

/**
 * Resetea roomStore/videoStore al salir de una partida.
 */
export function resetRoom() {
  roomStore.set({
    roomId: null,
    code: null,
    players: [],
    role: 'player',
    playerIndex: 0,
    stunServers: [],
    isStreaming: false
  })
  videoStore.set({
    localStream: null,
    remoteStream: null,
    isAudioEnabled: true,
    isVideoEnabled: true
  })
}
