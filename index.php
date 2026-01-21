<?php 
session_start();

if(isset($_SESSION['admin'])){
    header('Location: vistas/roles/admin.php');
    exit();
}
header('Location: vistas/roles');

if(isset($_SESSION['users'])){
    header('Location: vistas/roles/user.php');
    exit();
}
header('Location: vistas/roles');

?>
