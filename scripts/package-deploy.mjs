/**
 * package-deploy.mjs
 *
 * Genera ./deploy/ con la estructura EXACTA que hay que subir a
 * /public_html/ en OVH:
 *
 *   deploy/
 *   ├── dist/        (frontend compilado)
 *   ├── api/         (endpoints PHP)
 *   ├── config/      (config.php, db.php, utils.php + .htaccess)
 *   ├── db/          (vacío + .htaccess)
 *   └── setup.php    (TEMPORAL: ejecutar una vez y borrar)
 *
 * Uso:
 *   npm run build
 *   node scripts/package-deploy.mjs
 */
import { existsSync, rmSync, mkdirSync, cpSync, copyFileSync, writeFileSync } from 'node:fs'
import { resolve, dirname } from 'node:path'
import { fileURLToPath } from 'node:url'

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..')
const out = resolve(root, 'deploy')

if (!existsSync(resolve(root, 'dist/index.html'))) {
  console.error('✗ No existe dist/index.html. Ejecuta primero:  npm run build')
  process.exit(1)
}

rmSync(out, { recursive: true, force: true })
mkdirSync(out, { recursive: true })

// Frontend
cpSync(resolve(root, 'dist'), resolve(out, 'dist'), { recursive: true })

// Backend
cpSync(resolve(root, 'api'), resolve(out, 'api'), { recursive: true })
cpSync(resolve(root, 'config'), resolve(out, 'config'), { recursive: true })

// db/ vacío con su .htaccess
mkdirSync(resolve(out, 'db'), { recursive: true })
copyFileSync(resolve(root, 'db/.htaccess'), resolve(out, 'db/.htaccess'))
writeFileSync(resolve(out, 'db/.gitkeep'), '')

// setup.php (temporal)
copyFileSync(resolve(root, 'setup.php'), resolve(out, 'setup.php'))

// Quitar .gitkeep sueltos de api/ y config/
for (const p of ['api/.gitkeep', 'config/.gitkeep', 'dist/.gitkeep']) {
  rmSync(resolve(out, p), { force: true })
}

writeFileSync(resolve(out, 'LEEME.txt'),
`Sube TODO el contenido de esta carpeta a /public_html/ (o subdominio) en OVH.

Estructura resultante en el servidor:
  /public_html/dist/      -> https://tudominio.com/dist/
  /public_html/api/       -> https://tudominio.com/api/
  /public_html/config/    (protegido por .htaccess)
  /public_html/db/        (protegido por .htaccess; game.sqlite3 se crea solo)
  /public_html/setup.php  -> abrir 1 vez en el navegador y DESPUES BORRAR

Permisos:
  find . -type d -exec chmod 755 {} \\;
  find . -type f -name "*.php" -exec chmod 644 {} \\;
  chmod 755 db

Luego:
  1. Abrir https://tudominio.com/setup.php   (crea tablas + admin/admin123)
  2. BORRAR setup.php del servidor
  3. Abrir https://tudominio.com/dist/
`)

console.log('✓ deploy/ generado. Sube su contenido a /public_html/ en OVH.')
