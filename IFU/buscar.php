<?php
// Evitamos que PHP imprima errores en texto plano que rompan el formato JSON
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

// Conectamos a la base de datos
require_once __DIR__ . '/conexion.php';

// Iniciamos la sesión maestra (por si en el futuro quieres validar $_SESSION['rol'])
session_start();

$q = trim((string)($_GET['q'] ?? ''));

try {
    if ($q !== '') {
        $stmt = $pdo->prepare(
            'SELECT clave, descripcion, cantidad
             FROM inventario
             WHERE clave LIKE :q OR descripcion LIKE :q
             ORDER BY clave ASC, descripcion ASC'
        );
        $stmt->execute([':q' => '%' . $q . '%']);
    } else {
        $stmt = $pdo->query(
            'SELECT clave, descripcion, cantidad
             FROM inventario
             ORDER BY clave ASC, descripcion ASC'
        );
    }

    // Usamos FETCH_ASSOC para que el JSON quede limpio y ligero
    $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $metodologiaStmt = $pdo->query(
        'SELECT clave_padre, descripcion_padre, clave_hijo
         FROM metodologia
         ORDER BY clave_padre ASC, clave_hijo ASC'
    );

    $metodologia = [];

    foreach ($metodologiaStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $padre = (string)$row['clave_padre'];

        if (!isset($metodologia[$padre])) {
            $metodologia[$padre] = [
                'descripcion' => (string)$row['descripcion_padre'],
                'hijos' => [],
            ];
        }

        $hijo = trim((string)$row['clave_hijo']);

        if ($hijo !== '' && !in_array($hijo, $metodologia[$padre]['hijos'], true)) {
            $metodologia[$padre]['hijos'][] = $hijo;
        }
    }

    // Imprimimos la respuesta correcta usando json_encode nativo de PHP
    echo json_encode([
        'ok' => true,
        'inventario' => $inventario,
        'metodologia' => $metodologia,
    ]);

} catch (Throwable $e) {
    // Si hay un error, lo mandamos en formato JSON para que JavaScript lo entienda
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'Error al buscar datos: ' . $e->getMessage()
    ]);
}