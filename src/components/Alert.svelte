<script>
  import { createEventDispatcher, onMount, onDestroy } from 'svelte'
  import { playSound } from '../lib/utils.js'
  import alertSoundUrl from '../assets/alert-sound.mp3'

  export let message = '¡Alerta!'
  export let duration = 3000
  export let sound = alertSoundUrl
  export let withSound = true

  const dispatch = createEventDispatcher()
  let timer

  onMount(() => {
    if (withSound) playSound(sound)
    timer = setTimeout(() => dispatch('dismiss'), duration)
  })

  onDestroy(() => clearTimeout(timer))
</script>

<div
  class="fixed top-6 left-1/2 -translate-x-1/2 z-50 bg-red-600 text-white px-6 py-3
         rounded-lg shadow-2xl font-bold text-lg animate-pulse flex items-center gap-3"
  role="alert"
>
  <span class="text-2xl">⚠️</span>
  <span>{message}</span>
  <button
    on:click={() => dispatch('dismiss')}
    class="ml-2 text-white/80 hover:text-white"
    aria-label="Cerrar alerta"
  >
    ✕
  </button>
</div>
