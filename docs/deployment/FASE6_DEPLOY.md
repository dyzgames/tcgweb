# 🚀 FASE 6: BUILD & DEPLOYMENT

**Objetivo:** Compilar y subir a servidor OVH  
**Duración:** 1 sesión  
**Prerequisito:** Todas las fases anteriores completadas

---

## 📦 PASO 1: BUILD LOCAL

```bash
# En tu máquina local
npm run test:run      # Asegurar tests pasan
npm run build         # Compila a /dist/
```

**Resultado:**
```
dist/
├── index.html                    (~5KB)
├── assets/
│   ├── index-XXXXX.js            (~50KB)
│   ├── index-XXXXX.css           (~20KB)
│   └── alert-sound-XXXXX.mp3     (~50KB)
```

**Tamaño total:** ~150-200KB (muy pequeño ✓)

---

## 📤 PASO 2: PREPARAR ARCHIVOS

**FRONTEND:**
- Copiar `/dist/*` a servidor

**BACKEND:**
- Copiar `/api/*` a servidor
- Copiar `/config/*` a servidor (config.php, db.php, utils.php)
- Copiar `setup.php` a servidor (temporalmente)
- Crear directorio `/db/` vacío

---

## 🖥️ PASO 3: ESTRUCTURA EN SERVIDOR OVH

Después de copiar todo:

```
/home/tu_usuario/public_html/
├── dist/
│   ├── index.html
│   ├── assets/
│   │   ├── index-XXXXX.js
│   │   ├── index-XXXXX.css
│   │   └── alert-sound-XXXXX.mp3
│
├── api/
│   ├── auth.php
│   ├── rooms.php
│   ├── signal.php
│   ├── alerts.php
│   └── admin.php
│
├── config/
│   ├── config.php
│   ├── db.php
│   └── utils.php
│
├── db/                    # Directorio vacío (se crean archivos aquí)
│
└── setup.php              # ← EJECUTAR UNA SOLA VEZ

(Opcional):
├── .htaccess             # Para reescritura de URLs
```

---

## 🔐 PASO 4: CONFIGURACIÓN DE PERMISOS

```bash
# En servidor OVH (vía SSH)

# Permisos de directorios
chmod 755 db/
chmod 755 api/
chmod 755 config/
chmod 755 dist/

# Permisos de archivos
chmod 644 db.php
chmod 644 api/*.php
chmod 644 config/*.php
chmod 644 setup.php
```

---

## ⚙️ PASO 5: EJECUTAR setup.php

```bash
# Vía browser:
https://tudominio.com/setup.php

# Vía curl (SSH):
curl https://tudominio.com/setup.php

# Resultado esperado:
# ✓ Tablas creadas y usuario admin/admin123 creado
```

**Verificar que se creó:**
```bash
ls -la db/game.sqlite3
# Debe existir el archivo
```

---

## 🗑️ PASO 6: ELIMINAR setup.php

**⚠️ CRÍTICO:** Después de ejecutar, **BORRA setup.php del servidor**

```bash
rm setup.php
```

---

## 🧪 PASO 7: VERIFICAR INSTALACIÓN

```bash
# Test 1: Frontend
curl https://tudominio.com/dist/index.html
# Debe retornar HTML válido

# Test 2: Login
curl -X POST https://tudominio.com/api/auth.php \
  -H "Content-Type: application/json" \
  -d '{"action":"login","usuario":"admin","contrasena":"admin123"}'
# Debe retornar: {"success":true,"data":{...},"error":null}

# Test 3: Crear partida
curl -X POST https://tudominio.com/api/rooms.php \
  -H "Content-Type: application/json" \
  -d '{"action":"create_room","session_token":"TOKEN_DEL_TEST_2"}'
```

---

## 🌐 PASO 8: CONFIGURAR DOMINIO

En `vite.config.js` (producción):
```javascript
// .env.production
VITE_API_URL=/api
```

Esto hace que frontend apunte a `/api` (mismo dominio).

---

## ✅ CHECKLIST PRE-DEPLOY

- [ ] `npm run test:run` pasa (0 errores)
- [ ] `npm run build` genera `/dist/` sin errores
- [ ] `/dist/` tiene tamaño < 250KB
- [ ] Todos los archivos `.php` se ven bien
- [ ] Base de datos SQLite configurada
- [ ] HTTPS habilitado en servidor
- [ ] Permisos de directorios correctos (755)
- [ ] setup.php ejecutado sin errores
- [ ] **setup.php eliminado del servidor** ⚠️
- [ ] Login funciona desde browser (https://tudominio.com/dist/)
- [ ] Endpoints responden JSON válido
- [ ] No hay errores en console del browser

---

## 🆘 TROUBLESHOOTING

**Error 403 Forbidden**
```bash
chmod 755 api/
chmod 755 dist/
```

**SQLITE_CANTOPEN**
```bash
chmod 755 db/
# Asegurar que existe el directorio
mkdir -p db/
```

**CORS errors**
```bash
# En api.php, agregar headers CORS
header('Access-Control-Allow-Origin: https://tudominio.com');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
```

**Página en blanco**
1. Revisar console del browser (F12)
2. Revisar logs de servidor PHP
3. Asegurar que `/dist/index.html` existe
4. Verificar que VITE_API_URL apunta a `/api`

---

## 📊 ESTRUCTURA FINAL

```
NAVEGADOR
  ↓
  https://tudominio.com/dist/index.html
  ↓
  Carga /dist/assets/index-XXXXX.js
  ↓
  Fetch a /api/auth.php
  ↓
  Servidor OVH responde JSON
  ↓
  P2P WebRTC entre navegadores
```

---

## 🎉 ¡DONE!

Tu aplicación debe estar funcionando en:
```
https://tudominio.com/dist/
```

**Pruebas finales:**
1. Abrir en 2 navegadores/dispositivos
2. Login con admin/admin123
3. Crear partida en navegador 1
4. Unirse en navegador 2 con código
5. Ver ambas cámaras
6. Botón alerta funciona
7. Audio/video toggles funcionan

---

## 📝 NOTAS DE MANTENIMIENTO

- **Backups:** Hacer backup de `db/game.sqlite3` regularmente
- **Logs:** Revisar logs de PHP en `/var/log/`
- **Updates:** Las nuevas versiones se hace repitiendo pasos 1-3

