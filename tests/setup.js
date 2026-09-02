import { afterEach, vi } from 'vitest'
import { cleanup } from '@testing-library/svelte'

// Limpiar el DOM y los mocks después de cada test
afterEach(() => {
  cleanup()
  vi.clearAllMocks()
  localStorage.clear()
})

// ---- Mock de localStorage (determinista) ----
const _store = new Map()
const localStorageMock = {
  getItem: (k) => (_store.has(k) ? _store.get(k) : null),
  setItem: (k, v) => _store.set(k, String(v)),
  removeItem: (k) => _store.delete(k),
  clear: () => _store.clear(),
  key: (i) => Array.from(_store.keys())[i] ?? null,
  get length() { return _store.size }
}
Object.defineProperty(globalThis, 'localStorage', {
  value: localStorageMock,
  writable: true,
  configurable: true
})

// ---- Mock de navigator.mediaDevices (webcam / micrófono) ----
const fakeStream = {
  getTracks: () => [],
  getVideoTracks: () => [],
  getAudioTracks: () => []
}
Object.defineProperty(globalThis.navigator, 'mediaDevices', {
  configurable: true,
  value: {
    getUserMedia: vi.fn(async () => fakeStream),
    enumerateDevices: vi.fn(async () => [])
  }
})

// ---- Mock global de fetch (cada test define su respuesta) ----
globalThis.fetch = vi.fn(() =>
  Promise.resolve({ ok: true, json: () => Promise.resolve({}) })
)

// ---- Stubs varios que algún componente puede tocar ----
globalThis.Audio = class {
  play() { return Promise.resolve() }
  pause() {}
}
if (!globalThis.navigator.clipboard) {
  Object.defineProperty(globalThis.navigator, 'clipboard', {
    configurable: true,
    value: { writeText: vi.fn(async () => {}) }
  })
}
