<?php
declare(strict_types=1);

// Evitamos errores en texto plano que rompan la comunicación con JavaScript
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

// Conectamos a la base de datos
require_once __DIR__ . '/conexion.php';

// Iniciamos la sesión maestra
session_start();

// Usamos nuestra función unificada para responder a React/JS fácilmente
function responderJson($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Opcional: Validación de seguridad con tu nueva sesión
/*
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    responderJson(['ok' => false, 'message' => 'No tienes permisos de administrador'], 403);
}
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson(['ok' => false, 'message' => 'Método no permitido'], 405);
}

$payload = json_decode(file_get_contents('php://input') ?: '', true);
$file = $payload['file'] ?? [];
$metodologia = $payload['metodologia'] ?? [];
$maxSize = 5 * 1024 * 1024;

if (($payload['clear'] ?? false) === true) {
    try {
        $pdo->exec('TRUNCATE TABLE metodologia');
        responderJson([
            'ok' => true,
            'message' => 'Metodología borrada correctamente',
            'total' => 0,
        ]);
    } catch (Throwable $e) {
        responderJson([
            'ok' => false,
            'message' => 'Error al borrar metodología: ' . $e->getMessage(),
        ], 500);
    }
}

if (!is_array($file) || !is_array($metodologia)) {
    responderJson(['ok' => false, 'message' => 'Solicitud inválida'], 400);
}

$fileName = (string)($file['name'] ?? '');
$fileSize = (int)($file['size'] ?? 0);
$extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if (!in_array($extension, ['xlsx', 'csv'], true)) {
    responderJson(['ok' => false, 'message' => 'Solo se permiten archivos .xlsx o .csv'], 400);
}

if ($fileSize <= 0 || $fileSize > $maxSize) {
    responderJson(['ok' => false, 'message' => 'El archivo debe pesar máximo 5 MB'], 400);
}

if (count($metodologia) === 0) {
    responderJson(['ok' => false, 'message' => 'No se detectaron columnas obligatorias de metodología'], 400);
}

foreach ($metodologia as $clavePadre => $relacion) {
    if (
        trim((string)$clavePadre) === '' ||
        !is_array($relacion) ||
        trim((string)($relacion['descripcion'] ?? '')) === '' ||
        !isset($relacion['hijos']) ||
        !is_array($relacion['hijos'])
    ) {
        responderJson([
            'ok' => false,
            'message' => 'Columnas obligatorias: clave padre, descripción e hijos',
        ], 400);
    }
}

try {
    // 1. Limpiamos la tabla primero, fuera de la transacción para evitar bloqueos
    $pdo->exec('TRUNCATE TABLE metodologia');

    // 2. Iniciamos la transacción después de limpiar
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO metodologia (clave_padre, descripcion_padre, clave_hijo)
         VALUES (:clave_padre, :descripcion_padre, :clave_hijo)'
    );

    $total = 0;

    foreach ($metodologia as $clavePadre => $relacion) {
        $hijos = $relacion['hijos'];

        if (count($hijos) === 0) {
            $stmt->execute([
                ':clave_padre' => trim((string)$clavePadre),
                ':descripcion_padre' => trim((string)$relacion['descripcion']),
                ':clave_hijo' => '',
            ]);
            $total++;
            continue;
        }

        foreach ($hijos as $claveHijo) {
            $stmt->execute([
                ':clave_padre' => trim((string)$clavePadre),
                ':descripcion_padre' => trim((string)$relacion['descripcion']),
                ':clave_hijo' => trim((string)$claveHijo),
            ]);
            $total++;
        }
    }

    // 3. Confirmamos
    $pdo->commit();

    responderJson([
        'ok' => true,
        'message' => 'Metodología actualizada correctamente',
        'total' => $total,
    ]);
} catch (Throwable $e) {
    // Si hay un error, solo hacemos rollback si la transacción fue iniciada
    if (isset($pdo) && method_exists($pdo, 'inTransaction') && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    responderJson([
        'ok' => false,
        'message' => 'Error al guardar metodología: ' . $e->getMessage(),
    ], 500);
}