<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

$host = 'sql112.infinityfree.com';
$dbname = 'if0_41125231_vencer'; 
$username = 'if0_41125231';
$password = 'DEtK59bqZzA'; // Recuerda cambiarla después en tu panel

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Hacemos la consulta, pero no la guardamos toda en memoria
    $stmt = $pdo->query("SELECT * FROM productividad_externa ORDER BY fecha_atencion DESC");
    
    // Imprimimos el inicio del arreglo JSON
    echo "[";
    
    $primero = true;
    
    // El "Modo Goteo": Vamos sacando un registro a la vez y lo imprimimos
    while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!$primero) {
            echo ","; // Ponemos coma entre registros
        }
        echo json_encode($fila);
        $primero = false;
    }
    
    // Cerramos el arreglo JSON
    echo "]";

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de conexión BD: ' . $e->getMessage()]);
}
?>