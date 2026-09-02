# 🎨 FASE 3: COMPONENTES UI COMPLETOS

**Objetivo:** Crear todos los componentes Svelte para la interfaz  
**Duración:** 2 sesiones  
**Prerequisito:** Fase 2 completada

---

## 📋 COMPONENTES A CREAR

### 1. `CreateRoom.svelte`
Modal para crear nueva partida y mostrar código QR

### 2. `JoinRoom.svelte`
Formulario para unirse a partida con código

### 3. `Room.svelte`
Pantalla principal de la partida (contenedor)

### 4. `VideoGrid.svelte`
Grid de 2 videos + espectadores

### 5. `Alert.svelte`
Componente de notificación con sonido

### 6. `Admin.svelte`
Panel de administración (solo para admins)

---

## 🚀 PASOS PRÁCTICOS

**Dile a Claude Code Desktop:**

```
Consulta DOCUMENTO_TECNICO_v2.md sección 9: "Componentes Svelte"

Crea todos estos componentes en /src/components/:

1. CreateRoom.svelte
   - Input para nombre de partida (opcional)
   - Botón para crear
   - Mostrar código único generado
   - Copiar código al portapapeles
   - Mostrar QR del código

2. JoinRoom.svelte
   - Input para código de partida
   - Botón para unirse
   - Validar código antes de unirse
   - Error si código no existe

3. Room.svelte
   - Componente padre
   - Pasa video grid, botones, etc
   - Gestiona estado de la sala
   - Mostrar players conectados

4. VideoGrid.svelte
   - 2 videos en grid 16:9
   - Botón para agrandar cada video
   - Mostrar nombres de players
   - Mostrar indicadores de audio/video

5. Alert.svelte
   - Componente de notificación
   - Mostrar alerta con animación
   - Reproducir sonido /public/alert-sound.mp3
   - Auto-desaparecer después de 3 segundos

6. Admin.svelte
   - Tabla con usuarios
   - Botón para crear usuario
   - Formulario modal
   - Listar usuarios (GET /admin.php)

Usa Tailwind CSS para estilos.
Integra con stores de Svelte (authStore, roomStore, videoStore).
```

---

## 🔧 ESTRUCTURA RECOMENDADA

```
src/components/
├── Login.svelte           # ✓ Hecho en Fase 2
├── MainMenu.svelte        # ✓ Hecho en Fase 2
├── CreateRoom.svelte      # ← Nuevo
├── JoinRoom.svelte        # ← Nuevo
├── Room.svelte            # ← Nuevo
├── VideoGrid.svelte       # ← Nuevo
├── Alert.svelte           # ← Nuevo
└── Admin.svelte           # ← Nuevo
```

---

## 💡 NOTAS IMPORTANTES

- **Estilos:** Usa Tailwind utilities (no CSS custom)
- **Reactividad:** Usa `bind:` para inputs
- **Async:** Usa `async/await` para llamadas API
- **Eventos:** Usa `on:click`, `on:submit`, etc
- **Stores:** Importa y usa con `$` prefix
- **Audio:** Para reproducir sonido: `new Audio('/alert-sound.mp3').play()`

---

## ✅ CÓMO TESTEAR

```bash
npm run dev
```

Luego navega:
1. Login → MainMenu
2. Click "Crear Partida" → CreateRoom
3. Click "Unirse Partida" → JoinRoom
4. Crear partida → Ver Room con VideoGrid

---

## 📌 PRÓXIMO PASO

Cuando Fase 3 esté lista:

**"Fase 3 completada. Voy con Fase 4: WebRTC con simple-peer"**

