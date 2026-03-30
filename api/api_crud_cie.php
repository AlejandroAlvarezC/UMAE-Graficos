<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Respondemos rápido a la petición de validación de seguridad (CORS) del navegador
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit; }

$host = 'sql112.infinityfree.com';
$dbname = 'if0_41125231_vencer'; 
$username = 'if0_41125231';
$password = 'DEtK59bqZzA'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $metodo = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);

    if ($metodo == 'POST') {
        // --- CREAR NUEVO ---
        $stmt = $pdo->prepare("INSERT INTO cat_cie10 (codigo, descripcion) VALUES (:codigo, :descripcion)");
        $stmt->execute(['codigo' => strtoupper($input['codigo']), 'descripcion' => $input['descripcion']]);
        echo json_encode(['success' => true, 'message' => 'Diagnóstico agregado']);
        
    } elseif ($metodo == 'PUT') {
        // --- EDITAR EXISTENTE ---
        $stmt = $pdo->prepare("UPDATE cat_cie10 SET descripcion = :descripcion WHERE codigo = :codigo");
        $stmt->execute(['codigo' => strtoupper($input['codigo']), 'descripcion' => $input['descripcion']]);
        echo json_encode(['success' => true, 'message' => 'Diagnóstico actualizado']);
        
    } elseif ($metodo == 'DELETE') {
        // --- BORRAR ---
        $codigo = $_GET['codigo'];
        $stmt = $pdo->prepare("DELETE FROM cat_cie10 WHERE codigo = :codigo");
        $stmt->execute(['codigo' => strtoupper($codigo)]);
        echo json_encode(['success' => true, 'message' => 'Diagnóstico eliminado']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    // Si el código ya existe, MySQL lanza el error 23000 (Duplicate entry)
    if ($e->getCode() == '23000') {
        echo json_encode(['success' => false, 'error' => 'El código CIE-10 ya existe en la base de datos.']);
    } else {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>