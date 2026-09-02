<?php
/**
 * setup.php - EJECUTAR UNA SOLA VEZ (deploy inicial)
 *
 * Crea las tablas de la base de datos y el usuario administrador por defecto.
 *
 * Uso local:   php setup.php
 * Uso navegador: http://localhost:8000/setup.php
 *
 * ⚠️ IMPORTANTE: después de ejecutarlo en producción, BORRA este archivo.
 *
 * Snippet base: docs/backend/FASE1_BACKEND.md (PASO 5)
 */

require __DIR__ . '/config/config.php';
require __DIR__ . '/config/db.php';

header('Content-Type: text/plain; charset=utf-8');

// 1. Crear tablas
createTables();
echo "OK Tablas creadas (users, rooms, sessions, signals, alerts)\n";

// 2. Crear usuario admin
$pdo      = getDB();
$username = ADMIN_USER;                                   // 'admin'
$password = password_hash(ADMIN_PASS, PASSWORD_BCRYPT);   // 'admin123'

try {
    $stmt = $pdo->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, 1)");
    $stmt->execute([$username, $password]);
    echo "OK Usuario admin creado -> " . ADMIN_USER . " / " . ADMIN_PASS . "\n";
} catch (PDOException $e) {
    echo "-- El usuario admin ya existe (o error): " . $e->getMessage() . "\n";
}

closeDB();

echo "\nFASE 1 lista. Arranca el backend con:  php -S localhost:8000\n";
echo "Recuerda BORRAR setup.php en el servidor de produccion.\n";
