<?php
declare(strict_types=1);

// Estas son tus credenciales correctas
$host = 'sql112.infinityfree.com';
$dbname = 'if0_41125231_vencer'; 
$username = 'if0_41125231';
$password = 'DEtK59bqZzA';

function jsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Primera conexión: Intentamos conectar directo a la base de datos
    try {
        $pdo = new PDO(
            "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $e) {
        // Segunda conexión: Si falla (porque la DB no existe), nos conectamos al servidor base
        $pdoServer = new PDO(
            "mysql:host={$host};charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        // Creamos la base de datos
        $pdoServer->exec(
            "CREATE DATABASE IF NOT EXISTS `{$dbname}`
             CHARACTER SET utf8mb4
             COLLATE utf8mb4_unicode_ci"
        );

        // Nos conectamos de nuevo, ahora a la base recién creada
        $pdo = new PDO(
            "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    // Creación de las tablas
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS inventario (
            id INT AUTO_INCREMENT PRIMARY KEY,
            clave VARCHAR(20) NOT NULL,
            descripcion TEXT NOT NULL,
            cantidad INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_clave (clave)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS metodologia (
            id INT AUTO_INCREMENT PRIMARY KEY,
            clave_padre VARCHAR(20) NOT NULL,
            descripcion_padre TEXT NOT NULL,
            clave_hijo VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_padre (clave_padre),
            INDEX idx_hijo (clave_hijo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    // OMITIMOS LA TABLA USUARIOS
    // Como acordamos usar tu login principal, ya no necesitas crear ni revisar la tabla 'usuarios' aquí.

} catch (Throwable $e) {
    jsonResponse([
        'ok' => false,
        'message' => 'Error de conexión a MySQL: ' . $e->getMessage(),
    ], 500);
}