<?php
if (isset($_POST['btnRegister'])) {
    $txtNames = $_POST['names'];
    $txtEmail = $_POST['email'];
    $txtPassword = password_hash($_POST['password'], PASSWORD_DEFAULT); 

    include "../../modelos/conexion.php";
    $conexion = new Conexion();
    
    // Verificar si el correo ya está registrado
    $sqlCheck = "SELECT * FROM admi WHERE Email = '$txtEmail'";
    $existe = $conexion->consultarUnaFila($sqlCheck);
    
    if ($existe) {
    session_start();
    $_SESSION['register_error'] = "El correo ya está registrado.";
    header('Location: registerformAdmin.php?msg=Email ya en uso');
    exit();
}
    $sql = "INSERT INTO admi (Names, Email, Pasword)
            VALUES ('$txtNames', '$txtEmail', '$txtPassword')";

    if ($conexion->actualizar($sql)) {
        session_start();

        $adminId = $conexion->obtenerUltimoId();

        $_SESSION['admin_id'] = $adminId;
        $_SESSION['admin_name'] = $txtNames;

        header("Location: login.php?msg=Administrador registrado correctamente");
        exit();
    } else {
        session_start();
        
        $_SESSION['register_error'] = "Error al registrar el administrador. Inténtalo de nuevo.";
        header('Location: registerformAdmin.php?msg=Error Registro');
        exit();
    }
}
?>