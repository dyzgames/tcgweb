# ✅ FASE 5: TESTING CON VITEST

**Objetivo:** Escribir tests unitarios e integración  
**Duración:** 1 sesión  
**Prerequisito:** Fase 4 completada

---

## 🎯 TIPOS DE TESTS

### Unit Tests (Funciones individuales)
```javascript
test('login devuelve token', async () => {
  const result = await login('admin', 'admin123')
  expect(result.success).toBe(true)
  expect(result.data.session_token).toBeDefined()
})
```

### Component Tests (Componentes Svelte)
```javascript
test('Login muestra error con credenciales inválidas', async () => {
  const { component } = render(Login)
  // Interactuar y verificar
})
```

### Integration Tests (Múltiples partes)
```javascript
test('Crear y unirse partida', async () => {
  const room = await createRoom(token)
  const joined = await joinRoom(room.code, token2)
  expect(joined.success).toBe(true)
})
```

---

## 📝 TAREAS DE FASE 5

**Dile a Claude:**

```
Consulta DOCUMENTO_TECNICO_v2.md sección 11: "Testing Vitest"

Crea estos archivos de test en /tests/:

1. tests/setup.js
   - Mock de navigator.mediaDevices
   - Mock de fetch si es necesario
   - Configuración global de tests

2. tests/api.test.js
   - Test login success/error
   - Test create room
   - Test join room
   - Test send signal
   - Test send alert

3. tests/components/Login.test.js
   - Renderizar componente
   - Verificar inputs visibles
   - Test submit login
   - Test error messages

4. tests/components/Room.test.js
   - Inicializar WebRTC
   - Test audio toggle
   - Test video toggle

5. tests/webrtc.test.js
   - Test getLocalStream
   - Test initializePeer
   - Test receiveSignal
   - Test close

Usa happy-dom como environment.
Cubre funcionalidad crítica (no necesitas 100%).
```

---

## 🚀 EJECUTAR TESTS

```bash
# Tests en modo watch
npm run test

# Tests una sola vez
npm run test:run

# UI interactiva
npm run test:ui
```

---

## ✅ CHECKLIST

- [ ] Todos los tests pasan
- [ ] Cobertura > 70% en funciones críticas
- [ ] No hay warnings en console
- [ ] `npm run test:run` retorna exit code 0

---

## 📌 PRÓXIMO PASO

**"Fase 5 completada. Voy con Fase 6: Build & Deploy"**

