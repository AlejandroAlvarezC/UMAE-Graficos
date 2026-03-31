<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit; }

$host = 'sql112.infinityfree.com';
$dbname = 'if0_41125231_vencer'; 
$username = 'if0_41125231';
$password = 'DEtK59bqZzA'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $metodo = $_SERVER['REQUEST_METHOD'];

    if ($metodo == 'GET') {
        // Ordenamos por división y luego por nombre para que se vea ordenado en la tabla
        $stmt = $pdo->query("SELECT * FROM cat_especialidades ORDER BY division ASC, nombre ASC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        
    } else {
        $input = json_decode(file_get_contents('php://input'), true);

        if ($metodo == 'POST') {
            $stmt = $pdo->prepare("INSERT INTO cat_especialidades (clave, nombre, division) VALUES (:clave, :nombre, :division)");
            $stmt->execute([
                'clave' => strtoupper(trim($input['clave'])),
                'nombre' => strtoupper(trim($input['nombre'])),
                'division' => strtoupper(trim($input['division']))
            ]);
            echo json_encode(['success' => true, 'message' => 'Especialidad agregada']);
            
        } elseif ($metodo == 'PUT') {
            $stmt = $pdo->prepare("UPDATE cat_especialidades SET nombre = :nombre, division = :division WHERE clave = :clave");
            $stmt->execute([
                'clave' => strtoupper(trim($input['clave'])),
                'nombre' => strtoupper(trim($input['nombre'])),
                'division' => strtoupper(trim($input['division']))
            ]);
            echo json_encode(['success' => true, 'message' => 'Especialidad actualizada']);
            
        } elseif ($metodo == 'DELETE') {
            $stmt = $pdo->prepare("DELETE FROM cat_especialidades WHERE clave = :clave");
            $stmt->execute(['clave' => $_GET['clave']]);
            echo json_encode(['success' => true, 'message' => 'Especialidad eliminada']);
        }
    }

} catch (PDOException $e) {
    http_response_code(500);
    if ($e->getCode() == '23000') {
        echo json_encode(['success' => false, 'error' => 'La clave de esta especialidad ya existe.']);
    } else {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>