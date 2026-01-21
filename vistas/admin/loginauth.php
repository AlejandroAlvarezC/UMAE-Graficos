<?php
if (isset($_POST['btnLogin'])) {
    $txtEmail = $_POST['email'];
    $txtPassword = $_POST['password'];

    include "../../modelos/conexion.php";

    $conexion = new Conexion();

    $sql = "SELECT * FROM admi WHERE Email = '$txtEmail'";
    $admin = $conexion->consultarUnaFila($sql); 

    if ($admin) {
        if (password_verify($txtPassword, $admin['Pasword'])) {
            session_start();
            $_SESSION['admin_id'] = $admin['Id'];
            $_SESSION['admin_name'] = $admin['Names']; 

            header("Location: admin.php"); 
            exit();
        } else {
            session_start();
            $_SESSION['login_error'] = "Correo o contraseña incorrectos. Por favor, inténtalo de nuevo.";
            header("Location: ./login.php"); 
            exit();
        }
    } else {
        session_start();
        $_SESSION['login_error'] = "Usuario no registrado. Pulsa en 'Crear Cuenta'";
        header('Location: ./login.php'); 
        exit();
    }

}
?>