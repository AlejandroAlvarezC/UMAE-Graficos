<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Content-Type: application/json");

$pdo = new PDO("mysql:host=sql112.infinityfree.com;dbname=if0_41125231_vencer;charset=utf8", "if0_41125231", "DEtK59bqZzA");
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET': // LEER
        $stmt = $pdo->query("SELECT * FROM consultorios ORDER BY id DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST': // CREAR
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $pdo->prepare("INSERT INTO consultorios (nombre_consultorio) VALUES (?)");
        $stmt->execute([$data['nombre_consultorio']]);
        echo json_encode(["success" => true]);
        break;

    case 'PUT': // EDITAR
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $pdo->prepare("UPDATE consultorios SET nombre_consultorio = ? WHERE id = ?");
        $stmt->execute([$data['nombre_consultorio'], $data['id']]);
        echo json_encode(["success" => true]);
        break;

    case 'DELETE': // ELIMINAR
        $id = $_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM consultorios WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["success" => true]);
        break;
}
?>