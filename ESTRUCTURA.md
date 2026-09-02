# 📊 ESTRUCTURA DEL PROYECTO - Visualización Completa

```
game-webrtc/
│
├── 📄 INICIO.md                           ← ¡COMIENZA AQUÍ!
├── 📄 README.md                            ← Guía general
├── 📄 DOCUMENTO_TECNICO_v2.md             ← Especificación técnica
├── 📄 ESTRUCTURA.md                        ← Este archivo
├── 📄 .gitignore                           ← Git ignore
│
├── 📁 src/                                 ← FRONTEND (Svelte)
│   ├── 📁 components/                      ← Componentes .svelte
│   │   ├── Login.svelte                    # (En Fase 2)
│   │   ├── MainMenu.svelte                 # (En Fase 2)
│   │   ├── CreateRoom.svelte               # (En Fase 3)
│   │   ├── JoinRoom.svelte                 # (En Fase 3)
│   │   ├── Room.svelte                     # (En Fase 3)
│   │   ├── VideoGrid.svelte                # (En Fase 3)
│   │   ├── Alert.svelte                    # (En Fase 3)
│   │   └── Admin.svelte                    # (En Fase 3)
│   │
│   ├── 📁 lib/                             ← Lógica reutilizable
│   │   ├── api.js                          # Llamadas HTTP (En Fase 2)
│   │   ├── store.js                        # Svelte stores (En Fase 2)
│   │   ├── auth.js                         # Autenticación (En Fase 2)
│   │   ├── webrtc.js                       # WebRTC manager (En Fase 4)
│   │   └── utils.js                        # Helpers (En Fase 2)
│   │
│   ├── 📁 styles/
│   │   └── app.css                         # Tailwind + custom CSS (En Fase 2)
│   │
│   ├── App.svelte                          # Componente raíz (En Fase 2)
│   └── main.js                             # Entry point (En Fase 2)
│
├── 📁 api/                                 ← BACKEND (PHP)
│   ├── auth.php                            # Login, registro, logout (En Fase 1)
│   ├── rooms.php                           # Crear/unirse partidas (En Fase 1)
│   ├── signal.php                          # Señalización WebRTC (En Fase 1)
│   ├── alerts.php                          # Alertas (En Fase 1)
│   └── admin.php                           # Gestión admin (En Fase 1)
│
├── 📁 config/                              ← Configuración compartida
│   ├── config.php                          # Constantes (En Fase 1)
│   ├── db.php                              # Conexión BD (En Fase 1)
│   └── utils.php                           # Helpers PHP (En Fase 1)
│
├── 📁 db/                                  ← Base de datos SQLite
│   └── game.sqlite3                        # (Se crea automáticamente)
│
├── 📁 public/                              ← Assets estáticos
│   ├── alert-sound.mp3                     # Sonido de alerta
│   └── favicon.ico                         # Favicon
│
├── 📁 tests/                               ← Tests con Vitest
│   ├── 📁 components/                      # Tests de componentes (En Fase 5)
│   │   ├── Login.test.js
│   │   ├── Room.test.js
│   │   └── Admin.test.js
│   ├── api.test.js                         # Tests API (En Fase 5)
│   ├── webrtc.test.js                      # Tests WebRTC (En Fase 5)
│   └── setup.js                            # Configuración tests (En Fase 5)
│
├── 📁 docs/                                ← Documentación por fases
│   ├── 📁 backend/
│   │   └── FASE1_BACKEND.md                # Backend PHP + SQLite
│   ├── 📁 frontend/
│   │   ├── FASE2_SETUP.md                  # Vite + Svelte setup
│   │   ├── FASE3_COMPONENTES.md            # Componentes UI
│   │   └── FASE4_WEBRTC.md                 # WebRTC + simple-peer
│   ├── 📁 testing/
│   │   └── FASE5_TESTING.md                # Tests Vitest
│   └── 📁 deployment/
│       └── FASE6_DEPLOY.md                 # Build & Deploy OVH
│
├── 📁 dist/                                ← BUILD OUTPUT (después de npm run build)
│   ├── index.html                          # (Generado)
│   └── 📁 assets/
│       ├── index-XXXXX.js                  # (Generado)
│       ├── index-XXXXX.css                 # (Generado)
│       └── alert-sound-XXXXX.mp3           # (Generado)
│
├── package.json                            # (En Fase 1)
├── vite.config.js                          # Configuración Vite (En Fase 2)
├── tailwind.config.js                      # Configuración Tailwind (En Fase 2)
├── postcss.config.js                       # Config PostCSS (En Fase 2)
├── vitest.config.js                        # Config Vitest (En Fase 5)
│
├── index.html                              # HTML raíz (En Fase 2)
├── .env.local                              # Env desarrollo (En Fase 1)
├── .env.production                         # Env producción (En Fase 1)
│
└── setup.php                               # (Temporal, se borra después de deploy)

```

---

## 🔄 TIMELINE DE CREACIÓN

```
Sesión 1-2:  FASE 1 (Backend PHP)
   ├── package.json
   ├── /api/ (5 endpoints)
   ├── /config/ (3 archivos)
   └── BD SQLite

Sesión 3-4:  FASE 2 (Frontend Setup)
   ├── Vite + Svelte config
   ├── Tailwind CSS
   ├── App.svelte + main.js
   ├── /src/lib/ (store, api, auth)
   └── Login + MainMenu componentes

Sesión 5-6:  FASE 3 (UI Componentes)
   ├── CreateRoom
   ├── JoinRoom
   ├── Room
   ├── VideoGrid
   ├── Alert
   └── Admin

Sesión 7-8:  FASE 4 (WebRTC)
   ├── webrtc.js (WebRTCManager)
   ├── Integración en Room.svelte
   ├── Video streaming P2P
   └── Audio/video controls

Sesión 9:    FASE 5 (Testing)
   ├── Setup tests
   ├── API tests
   ├── Component tests
   └── WebRTC tests

Sesión 10:   FASE 6 (Deploy)
   ├── npm run build
   ├── Copiar a servidor OVH
   ├── Ejecutar setup.php
   └── Testing final

```

---

## 📊 ESTADÍSTICAS DEL PROYECTO

| Aspecto | Valor |
|---------|-------|
| **Lenguajes** | JavaScript, HTML, CSS, PHP, SQL |
| **Dependencias npm** | ~50 packages |
| **Archivos .svelte** | 8 componentes |
| **Archivos .php** | 8 archivos |
| **Tests** | +15 test files |
| **Tamaño final** | ~200KB (gzipped) |
| **Navegadores soportados** | Chrome, Firefox, Edge, Safari (iOS 11+) |
| **Ambiente BD** | SQLite (sin servidor) |
| **Servidor** | PHP 7.4+ (OVH) |

---

## 🎯 ESTADO ACTUAL

```
✓ Estructura de carpetas
✓ Documentación completa (6 fases)
✓ Especificación técnica (DOCUMENTO_TECNICO_v2.md)
✓ README con instrucciones
✓ Este mapa de estructura

⏳ ESPERANDO: Código de Fase 1
→ Abre INICIO.md
→ Sigue con docs/backend/FASE1_BACKEND.md
→ Dale instrucciones a Claude Code
```

---

## 💡 CÓMO USAR ESTA ESTRUCTURA

1. **Para entender el proyecto:** Lee este archivo + DOCUMENTO_TECNICO_v2.md
2. **Para desarrollar:** Sigue INICIO.md
3. **Para cada fase:** Abre docs/*/FASE*.md correspondiente
4. **Para buscar archivos:** Este mapa muestra dónde va cada cosa
5. **Para deploy:** Sigue docs/deployment/FASE6_DEPLOY.md

---

## 🚀 SIGUIENTES PASOS

**Ahora:**
1. Abre INICIO.md
2. Lee README.md
3. Ve a Claude Code Desktop
4. Abre docs/backend/FASE1_BACKEND.md
5. Dale instrucciones a Claude para empezar Fase 1

¡Vamos a crear algo increíble! 🎮

