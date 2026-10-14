# 🎮 Plataforma P2P WebRTC - Guía de Desarrollo

**Stack:** Svelte 4 | Vite 5 | simple-peer 9 | Tailwind CSS | PHP 7.4+ | SQLite3

---

## 📋 PLAN DE DESARROLLO POR FASES

### ✅ Fase 1: Setup Inicial + Backend PHP (EMPEZAMOS AQUÍ)
**Objetivo:** Crear estructura base, endpoints PHP y BD SQLite
**Duración:** ~2-3 sesiones
**Archivos:** Ver `docs/backend/FASE1_BACKEND.md`

```bash
# Comandos que correrás:
npm init -y
npm install simple-peer tailwindcss postcss autoprefixer vite @sveltejs/vite-plugin-svelte
```

---

### 📋 Fase 2: Frontend Setup (Svelte + Vite + Tailwind)
**Objetivo:** Estructura Vite, primeros componentes, estilos
**Archivos:** Ver `docs/frontend/FASE2_SETUP.md`

---

### 🎨 Fase 3: Componentes UI Base
**Objetivo:** Login, MainMenu, CreateRoom, JoinRoom
**Archivos:** Ver `docs/frontend/FASE3_COMPONENTES.md`

---

### 🔌 Fase 4: WebRTC con simple-peer
**Objetivo:** P2P, streams de video, audio
**Archivos:** Ver `docs/frontend/FASE4_WEBRTC.md`

---

### ✔️ Fase 5: Testing
**Objetivo:** Tests unitarios con Vitest
**Archivos:** Ver `docs/testing/FASE5_TESTING.md`

---

### 🚀 Fase 6: Build & Deployment
**Objetivo:** Compilar, subir al servidor OVH
**Archivos:** Ver `docs/deployment/FASE6_DEPLOY.md`

---

## 🚦 PRÓXIMOS PASOS

### AHORA (En Claude Code Desktop):

1. **Abre este proyecto en VS Code**
   - File > Open Folder > elige esta carpeta `game-webrtc`

2. **Lee la guía de Fase 1:**
   ```
   docs/backend/FASE1_BACKEND.md
   ```

3. **Di en Claude Code:**
   ```
   "Voy a empezar la Fase 1. Necesito crear:
   - package.json con dependencias
   - Estructura /api/ con endpoints PHP
   - BD SQLite automática
   
   Consulta docs/backend/FASE1_BACKEND.md"
   ```

4. **Claude Code hará:**
   - Crear archivos de configuración
   - Generar endpoints PHP
   - Setup inicial

5. **Tú harás:**
   - Copiar/pegar código que Claude genere
   - Ejecutar `npm install`
   - Testear endpoints localmente

---

## 💾 Estructura de Carpetas

```
game-webrtc/
├── 📁 src/                    # Frontend Svelte (Fase 2+)
│   ├── components/            # Componentes .svelte
│   ├── lib/                   # Lógica (api.js, webrtc.js, auth.js, store.js)
│   └── styles/                # CSS (app.css con Tailwind)
│
├── 📁 api/                    # Backend PHP (Fase 1)
│   ├── auth.php              # Login, registro
│   ├── rooms.php             # Crear/unirse partidas
│   ├── signal.php            # Señalización WebRTC
│   ├── alerts.php            # Alertas
│   └── admin.php             # Panel admin
│
├── 📁 db/                     # BD SQLite
│   └── game.sqlite3          # (Se crea automáticamente)
│
├── 📁 config/                 # Configuración
│   ├── config.php
│   ├── db.php
│   └── utils.php
│
├── 📁 tests/                  # Tests Vitest (Fase 5)
│   ├── components/
│   ├── api.test.js
│   └── webrtc.test.js
│
├── 📁 docs/                   # Documentación por fases
│   ├── backend/
│   ├── frontend/
│   ├── deployment/
│   └── testing/
│
├── 📁 public/                 # Assets (favicon, sonidos)
│   └── alert-sound.mp3
│
├── package.json              # Dependencias npm
├── vite.config.js            # Configuración Vite
├── tailwind.config.js        # Configuración Tailwind
├── vitest.config.js          # Configuración tests
└── DOCUMENTO_TECNICO_v2.md   # Documento técnico completo
```

---

## ⚡ Comandos Importantes

```bash
# Desarrollo
npm run dev           # Inicia servidor local con HMR

# Build
npm run build         # Compila a /dist/

# Testing
npm run test          # Corre tests en modo watch
npm run test:ui       # Interfaz visual de tests
npm run test:run      # Tests una sola vez (CI/CD)

# Preview
npm run preview       # Vista previa del build local
```

---

## 🔗 Documentación Técnica Completa

Ver archivo: `DOCUMENTO_TECNICO_v2.md` (en raíz del proyecto)

Este archivo contiene TODO lo que Claude Code necesita saber.

---

## ⚠️ Notas Importantes

- **Fase 1 NO necesita Node.js en servidor** (solo archivos PHP)
- **Fase 2+ necesita Node.js LOCAL** (para desarrollo)
- **Build final es estático** (solo copiar `/dist/` al servidor)
- **Backend PHP se sube tal cual** (sin build)
- **SQLite se crea automáticamente** (primera ejecución de setup.php)

---

## 🆘 Si algo falla:

1. **Backend no responde:** Revisa que PHP esté en `localhost:8000`
2. **npm install falla:** Borra `node_modules/` y `package-lock.json`, intenta de nuevo
3. **Componentes no renderan:** Revisa configuración Vite en `vite.config.js`
4. **WebRTC no conecta:** Revisa STUN servers en `docs/backend/FASE1_BACKEND.md`

---

## 📞 Referencias Rápidas

- **Técnica del frontend:** Svelte reactivity, Stores, async/await
- **Técnica del backend:** PHP OOP, PDO para SQLite, JSON responses
- **WebRTC:** simple-peer abstrae todo, eventos: 'stream', 'data', 'signal'

---


