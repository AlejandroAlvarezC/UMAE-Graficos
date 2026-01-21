<?php
session_start();
include "../../modelos/conexion.php"; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_password = $_POST['admin_password'];

    $conexion = new Conexion();

    $sql = "SELECT password FROM Pasword LIMIT 1"; 
    $result = $conexion->consultarUnaFila($sql);

    if ($result) {
        $stored_password = $result['password'];

        if (password_verify($input_password, $stored_password)) {
            header("Location: registerformAdmin.php");
            exit();
        } else {
            $_SESSION['error'] = 'Contraseña incorrecta. Intente nuevamente.';
            header("Location: verificacionAdmin.php");
            exit();
        }
    } else {
        $_SESSION['error'] = 'No se encontró la contraseña en la base de datos.';
        header("Location: verificacionAdmin.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/bootstrap.min.css" defer>
    <link rel="stylesheet" href="../../css/styles.css" defer>
    <link rel="icon" type="image/png" href="../../logo-imss.png">
    <title>Verificación de administrador | Administrador</title>
</head>

<body style="background-color:aliceblue">
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="card1">
            <div class="card2">
                <h1 class="text-center">Verificación de administrador</h1>
                <?php
                if (isset($_SESSION['error'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                    unset($_SESSION['error']);
                }
                ?>
                <form action="verificacionAdmin.php" method="post">
                    <div class="mb-3">
                        <input type="password" name="admin_password" placeholder="Contraseña de Administrador"
                            class="form-control" required>
                    </div>
                    <div class="d-grid">
                        <br><input type="submit" class="btn btn-dark" value="Verificar">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

    <script src="../../js/scripts.js" defer></script>
    <script src="../../js/bootstrap.bundle.min.js" defer></script>

</body>

</html>