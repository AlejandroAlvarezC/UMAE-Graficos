<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    jsonResponse([
        'ok' => true,
        'authenticated' => usuarioActual() !== null,
        'usuario' => usuarioActual(),
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'message' => 'Método no permitido'], 405);
}

$payload = json_decode(file_get_contents('php://input') ?: '', true);
$usuario = trim((string)($payload['usuario'] ?? ''));
$password = (string)($payload['password'] ?? '');

if ($usuario === '' || $password === '') {
    jsonResponse(['ok' => false, 'message' => 'Usuario y contraseña son obligatorios'], 400);
}

$stmt = $pdo->prepare(
    'SELECT id, usuario, password_hash, rol, nombre
     FROM usuarios
     WHERE usuario = :usuario AND activo = 1
     LIMIT 1'
);
$stmt->execute([':usuario' => $usuario]);
$row = $stmt->fetch();

if (!$row || !password_verify($password, $row['password_hash'])) {
    jsonResponse(['ok' => false, 'message' => 'Credenciales incorrectas'], 401);
}

session_regenerate_id(true);

$_SESSION['usuario'] = [
    'id' => (int)$row['id'],
    'usuario' => $row['usuario'],
    'rol' => $row['rol'],
    'nombre' => $row['nombre'],
];

jsonResponse([
    'ok' => true,
    'message' => 'Inicio de sesión correcto',
    'usuario' => $_SESSION['usuario'],
]);
