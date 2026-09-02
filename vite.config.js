import { defineConfig } from 'vite'
import { svelte } from '@sveltejs/vite-plugin-svelte'
import { nodePolyfills } from 'vite-plugin-node-polyfills'

export default defineConfig({
  // Rutas relativas: la app se sirve desde https://tudominio.com/dist/
  // (los assets se cargan como ./assets/… en vez de /assets/…)
  base: './',

  plugins: [
    svelte(),
    // simple-peer (WebRTC) necesita polyfills de módulos de Node:
    // events, util, stream, buffer, process, global…
    nodePolyfills({
      include: ['events', 'util', 'stream', 'buffer', 'process'],
      globals: { Buffer: true, global: true, process: true },
    }),
  ],

  optimizeDeps: {
    include: ['simple-peer'],
  },

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
