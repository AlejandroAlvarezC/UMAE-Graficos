<?php
declare(strict_types=1);

error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conexion.php';
session_start();

function responderJson($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson(['ok' => false, 'message' => 'Método no permitido'], 405);
}

$payload = json_decode(file_get_contents('php://input') ?: '', true);
$inventario = $payload['inventario'] ?? [];
$isFirstChunk = $payload['isFirstChunk'] ?? true; 

if (($payload['clear'] ?? false) === true) {
    try {
        $pdo->exec('TRUNCATE TABLE inventario');
        responderJson(['ok' => true, 'message' => 'Inventario borrado', 'total' => 0]);
    } catch (Throwable $e) {
        responderJson(['ok' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
}

if (!is_array($inventario) || count($inventario) === 0) {
    responderJson(['ok' => false, 'message' => 'Lote vacío o inválido'], 400);
}

try {
    // 1. SOLO vaciamos la tabla si es el primer envío (Y LO HACEMOS ANTES DE LA TRANSACCIÓN)
    if ($isFirstChunk) {
        $pdo->exec('TRUNCATE TABLE inventario');
    }

    // 2. AHORA SÍ, iniciamos la transacción para insertar datos rápido y seguro
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO inventario (clave, descripcion, cantidad)
         VALUES (:clave, :descripcion, :cantidad)'
    );

    foreach ($inventario as $item) {
        $stmt->execute([
            ':clave' => trim((string)$item['clave']),
            ':descripcion' => trim((string)$item['descripcion']),
            ':cantidad' => (int)$item['cantidad'],
        ]);
    }

    // 3. Confirmamos los inserts
    $pdo->commit();

    responderJson([
        'ok' => true,
        'message' => 'Lote guardado correctamente'
    ]);
} catch (Throwable $e) {
    // Validación segura por si la transacción nunca inició
    if (isset($pdo) && method_exists($pdo, 'inTransaction') && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    responderJson([
        'ok' => false,
        'message' => 'Error al guardar lote: ' . $e->getMessage()
    ], 500);
}