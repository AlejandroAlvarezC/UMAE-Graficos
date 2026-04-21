<?php
// 1. Cabeceras estrictas para CORS (Permite que React lea los datos sin bloqueos)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// 2. Manejo de la pre-petición (OPTIONS) de los navegadores
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = 'sql112.infinityfree.com';
$dbname = 'if0_41125231_vencer'; 
$username = 'if0_41125231';
$password = 'DEtK59bqZzA';

try {
    // 4. Conexión a la base de datos con PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 5. La consulta SQL con el truco "AS" para traducir las columnas
    // - Tu columna 'Names' se enviará a React como 'nombre'
    // - Tu columna 'Email' se enviará a React como 'correo'
    // - Inventamos 'Activo' as estado para que los puntitos verdes en React funcionen
// Cambia tu consulta SQL por esta:
    $sql = "SELECT Id as id, Names as nombre, Email as correo, rol, 
            IFNULL(last_login, 'Sin registros') as ultimo_acceso, 
            login_count as visitas,
            user_agent as navegador
            FROM admi";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    // 6. Obtener los datos y convertirlos a formato JSON
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Enviamos el JSON limpio a React
    echo json_encode($resultados);

} catch (PDOException $e) {
    // Si hay un error de conexión o de sintaxis SQL, lo reportamos
    http_response_code(500);
    echo json_encode(['error' => 'Error de BD: ' . $e->getMessage()]);
}
?>