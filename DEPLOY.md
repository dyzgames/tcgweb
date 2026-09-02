# 🚀 Despliegue a OVH — Fase 6

## 1. Build local

```bash
npm run test:run      # 10/10 tests deben pasar
npm run build         # compila a dist/
npm run deploy:package # genera deploy/ con la estructura lista para subir
```

`dist/` resultante (~200 KB, ~55 KB gzip):

| Archivo | Tamaño |
|---|---|
| `dist/index.html` | 0,48 KB |
| `dist/assets/index-*.css` | 13,5 KB (gzip 3,3) |
| `dist/assets/index-*.js` | 42 KB (gzip 13,9) |
| `dist/assets/vendor-*.js` (simple-peer + polyfills) | 132 KB (gzip 38,4) |
| `dist/assets/alert-sound-*.mp3` | 16,7 KB |

## 2. Subir a OVH

Sube **todo el contenido de `deploy/`** a `/public_html/` (o al subdirectorio/subdominio que uses):

```
/public_html/
├── dist/          → https://tudominio.com/dist/
│   ├── index.html
│   ├── .htaccess          (cache de assets + fallback SPA)
│   └── assets/
├── api/           → https://tudominio.com/api/
│   ├── auth.php  rooms.php  signal.php  alerts.php  admin.php
├── config/        (config.php, db.php, utils.php + .htaccess que bloquea acceso web)
├── db/            (vacío + .htaccess "Require all denied"; game.sqlite3 se crea solo)
└── setup.php      (TEMPORAL)
```

> `config/` y `db/` llevan `.htaccess` que niega el acceso HTTP. PHP sigue pudiendo
> hacer `require` de esos ficheros (el `.htaccess` solo bloquea peticiones web),
> así que los endpoints funcionan y `db/game.sqlite3` no es descargable.

## 3. Permisos (SSH)

```bash
cd /public_html
find . -type d -exec chmod 755 {} \;
find . -type f -name "*.php" -exec chmod 644 {} \;
chmod 755 db
```

## 4. Inicializar la base de datos

Abrir una vez en el navegador:

```
https://tudominio.com/setup.php
```

Debe mostrar:

```
OK Tablas creadas (users, rooms, sessions, signals, alerts)
OK Usuario admin creado -> admin / admin123
```

## 5. ⚠️ Borrar setup.php

```bash
rm /public_html/setup.php
```

## 6. Verificar

```bash
# Test 1 — Frontend
curl -I https://tudominio.com/dist/

# Test 2 — Login
curl -X POST https://tudominio.com/api/auth.php \
  -H "Content-Type: application/json" \
  -d '{"action":"login","usuario":"admin","contrasena":"admin123"}'
# → {"success":true,"session_token":"...", ... ,"error":null}

# Test 3 — Crear partida  (usa el session_token del test 2)
curl -X POST https://tudominio.com/api/rooms.php \
  -H "Content-Type: application/json" \
  -d '{"action":"create_room","session_token":"TOKEN"}'
# → {"success":true,"code":"XXXXXX", ... }
```

## 7. Probar la app

`https://tudominio.com/dist/` — login `admin` / `admin123`, crear partida en un
navegador, unirse desde otro con el código, aceptar permisos de cámara en ambos.

---

## Notas de configuración

- **`vite.config.js` → `base: './'`**: los assets se cargan como `./assets/…`,
  necesario porque la app se sirve desde `/dist/` y no desde la raíz.
- **`.env.production` → `VITE_API_URL=/api`**: la API es una ruta **absoluta**
  (mismo dominio, distinta carpeta que el frontend). No la cambies a relativa.
- **HTTPS obligatorio**: `getUserMedia` (cámara/micrófono) no funciona sobre HTTP
  salvo en `localhost`.
- **CORS**: `config/config.php` tiene `CORS_ALLOW_ORIGIN = '*'`. Como frontend y API
  comparten dominio no hace falta tocarlo; si sirves el frontend desde otro dominio,
  ponlo a `'https://tudominio.com'`.
- **Backups**: copia periódica de `db/game.sqlite3`.
- **Actualizar**: repetir pasos 1-2 y volver a subir `dist/` + `api/` + `config/`
  (NO subas `setup.php` de nuevo ni borres `db/game.sqlite3`).

## Checklist

- [ ] `npm run test:run` → 0 errores
- [ ] `npm run build` → `dist/` sin errores, < 250 KB
- [ ] `deploy/` subido a `/public_html/`
- [ ] Permisos 755 dirs / 644 php
- [ ] HTTPS activo
- [ ] `setup.php` ejecutado → tablas + admin creados
- [ ] **`setup.php` borrado del servidor**
- [ ] Test 1/2/3 con `curl` OK
- [ ] Login desde `https://tudominio.com/dist/` OK
- [ ] Sin errores en la consola del navegador
- [ ] Vídeo P2P entre 2 navegadores OK
