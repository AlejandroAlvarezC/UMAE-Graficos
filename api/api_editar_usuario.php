<?php
// Permitir que React se comunique desde otro origen
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

try {
    $pdo = new PDO("mysql:host=sql112.infinityfree.com;dbname=if0_41125231_vencer;charset=utf8", "if0_41125231", "DEtK59bqZzA");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['id'])) {
        $id = $data['id'];
        $nombre = $data['nombre'];
        $correo = $data['correo'];
        $rol = $data['rol'];
        $pass = $data['password'];

        // Consulta base
        $sql = "UPDATE admi SET Names = :nom, Email = :em, rol = :rol";
        $params = [':nom' => $nombre, ':em' => $correo, ':rol' => $rol, ':id' => $id];

        // Solo actualizamos el password si no viene vacío
        if (!empty($pass)) {
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $sql .= ", Pasword = :pass";
            $params[':pass'] = $hashed;
        }

        $sql .= " WHERE Id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(["success" => true, "mensaje" => "Actualizado"]);
    } else {
        echo json_encode(["success" => false, "error" => "No llegó el ID"]);
    }

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>