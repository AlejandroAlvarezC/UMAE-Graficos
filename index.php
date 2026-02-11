<?php 
session_start();

// 1. Si ya inició sesión como ADMINISTRADOR
if(isset($_SESSION['admin'])){
    // Lo mandamos directo a su panel
    header('Location: vistas/roles/admin.php');
    exit();
} 

// 2. Si ya inició sesión como USUARIO NORMAL
elseif(isset($_SESSION['users'])){
    // Lo mandamos a su panel de usuario
    header('Location: vistas/roles/user.php');
    exit();
} 

// 3. Si NO ha iniciado sesión (Nadie logueado)
else {
    // AQUÍ ESTABA EL ERROR 404:
    // Ahora le damos la dirección exacta de tu login:
    header('Location: vistas/admin/login.php');
    exit();
}
?>