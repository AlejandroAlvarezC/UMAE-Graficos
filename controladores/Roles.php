<?php 
require_once '../modelos/Rol.php';

$accion = $_POST['a'] ?? $_GET['a'] ?? '';

if ($accion != '') {
    $rol = new Rol3();
    switch ($accion) {
        case 'Ingresar':
            $rol->num = $_POST['num'];
            $rol->nom = $_POST['nom'];
            $rol->fech = $_POST['fech'];
            $rol->cvv = $_POST['cvv'];
            $rol->user_id = $_POST['user_id']; 
            $rol->ingresar();
            break;
        case 'Editar':
            $rol->id = base64_decode($_POST['id']);
            $rol->num = $_POST['num'];
            $rol->nom = $_POST['nom'];
            $rol->fech = $_POST['fech'];
            $rol->cvv = $_POST['cvv'];
            $rol->editar();
            break;
        case 'Elimi':
            $rol->id = base64_decode($_GET['id']);
            $rol->eliminar();
            break;
    }
}

header('Location: ../vistas/roles/tarjets.php');
?>