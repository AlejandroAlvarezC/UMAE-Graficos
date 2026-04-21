<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data['id']) {
        echo json_encode(["error" => "ID de usuario no proporcionado"]);
        exit;
    }

    $id = $data['id'];
    $nombre = $data['nombre'];
    $correo = $data['correo'];
    $rol = $data['rol'];
    $password = $data['password'];

    try {
        $pdo = new PDO("mysql:host=sql112.infinityfree.com;dbname=if0_41125231_vencer;charset=utf8", "if0_41125231", "DEtK59bqZzA");
        
        // 1. Iniciamos la consulta base
        $sql = "UPDATE admi SET Names = :nombre, Email = :correo, rol = :rol";
        $params = [
            ':nombre' => $nombre,
            ':correo' => $correo,
            ':rol' => $rol,
            ':id' => $id
        ];

        // 2. ¿Se incluyó una contraseña nueva?
        if (!empty($password)) {
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", Pasword = :pass"; // Fíjate en la coma
            $params[':pass'] = $hashed_pass;
        }

        $sql .= " WHERE Id = :id";
        
        $stmt = $pdo->prepare($sql);
        $resultado = $stmt->execute($params);

        echo json_encode(["success" => true, "mensaje" => "Usuario actualizado correctamente"]);

    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}
?>