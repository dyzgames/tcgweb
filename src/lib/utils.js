/**
 * utils.js - Funciones helper compartidas del frontend
 * (Base para Fases 3-4: se irá ampliando)
 */

/**
 * Pausa asíncrona.
 * @param {number} ms milisegundos
 */
export function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms))
}

/**
 * Normaliza un código de sala: mayúsculas y sin espacios.
 */
export function normalizeCode(code) {
  return String(code || '').trim().toUpperCase()
}

/**
 * Valida el formato de un código de sala (letras/números, 4-8 chars).
 */
export function isValidCode(code) {
  return /^[A-Z0-9]{4,8}$/.test(normalizeCode(code))
}

/**
 * Extrae el objeto de datos de una respuesta del backend,
 * tolerando tanto { ...campos } como { data: { ...campos } }.
 */
export function unwrap(response) {
  if (!response) return {}
  return response.data && typeof response.data === 'object' ? response.data : response
}

/**
 * Devuelve el mensaje de error de una respuesta, o un texto por defecto.
 */
export function errorMessage(response, fallback = 'Error desconocido') {
  return (response && response.error) || fallback
}

/**
 * Reproduce un sonido corto sin bloquear.
 * @param {string} src ruta del audio
 */
export function playSound(src) {
  try {
    const audio = new Audio(src)
    audio.play().catch(() => {})
  } catch (e) {
    /* ignorar */
  }
}

/**
 * Formatea una fecha ISO / 'Y-m-d H:i:s' a algo legible.
 */
export function formatDate(value) {
  if (!value) return ''
  const d = new Date(String(value).replace(' ', 'T'))
  return isNaN(d.getTime()) ? String(value) : d.toLocaleString()
}
