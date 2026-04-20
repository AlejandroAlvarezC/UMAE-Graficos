<?php
// 1. Cabeceras estrictas para CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->nombre) && !empty($data->correo) && !empty($data->password)) {

    // Tus credenciales exactas
    $host = 'sql112.infinityfree.com';
    $dbname = 'if0_41125231_vencer'; 
    $username = 'if0_41125231';
    $password = 'DEtK59bqZzA';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Variables desde React
        $names = $data->nombre;
        $email = $data->correo;
        $rol = isset($data->rol) ? $data->rol : 'viewer';
        
        $password_hasheada = password_hash($data->password, PASSWORD_DEFAULT);

        // AQUÍ ESTÁ EL ARREGLO: Usamos la tabla 'admi' y tus columnas exactas
        $sql = "INSERT INTO admi (Names, Email, Pasword, rol) VALUES (:names, :email, :pasword, :rol)";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':names', $names);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':pasword', $password_hasheada); // Escrito con una 's' como en tu tabla
        $stmt->bindParam(':rol', $rol);

        if ($stmt->execute()) {
            echo json_encode(["exito" => true, "mensaje" => "Usuario creado exitosamente"]);
        } else {
            http_response_code(400);
            echo json_encode(["exito" => false, "error" => "No se pudo guardar."]);
        }

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["exito" => false, "error" => "Error de BD: " . $e->getMessage()]);
    }

} else {
    http_response_code(400);
    echo json_encode(["exito" => false, "error" => "Datos incompletos."]);
}
?>