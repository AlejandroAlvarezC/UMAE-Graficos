<?php
if (isset($_POST['btnLogin'])) {
    $txtEmail = $_POST['email'];
    $txtPassword = $_POST['password'];

    include "../../modelos/conexion.php";

    $conexion = new Conexion();

    $sql = "SELECT * FROM admi WHERE Email = '$txtEmail'";
    $admin = $conexion->consultarUnaFila($sql); 

    if ($admin) {
        // Verificamos si la contraseña es correcta
        if (password_verify($txtPassword, $admin['Pasword'])) {
            
            // 1. Iniciamos sesión y guardamos variables básicas
            session_start();
            $_SESSION['admin_id'] = $admin['Id'];
            $_SESSION['admin_name'] = $admin['Names']; 
            $_SESSION['rol'] = $admin['rol']; 

            // 2. Preparamos los datos para el rastreo
            $idUsuario = $admin['Id'];
            $browser = $_SERVER['HTTP_USER_AGENT'];

            // 3. INTENTAMOS ACTUALIZAR LA BASE DE DATOS (Antes del redireccionamiento)
            try {
                $host = 'sql112.infinityfree.com';
                $dbname = 'if0_41125231_vencer'; 
                $username = 'if0_41125231';
                $password = 'DEtK59bqZzA';

                $pdo_update = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                $pdo_update->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $sql_update = "UPDATE admi SET 
                                login_count = login_count + 1, 
                                last_login = NOW(), 
                                user_agent = :ua 
                                WHERE Id = :id";
                                
                $stmt_update = $pdo_update->prepare($sql_update);
                $stmt_update->execute([
                    ':ua' => $browser,
                    ':id' => $idUsuario
                ]);

            } catch(PDOException $e) {
                // Si falla el contador, nos avisará, pero esto solo pasará si las columnas están mal
                die("Error actualizando contador: " . $e->getMessage());
            }

            // 4. Si todo salió bien (o si el contador se actualizó), mandamos al panel
            header("Location: admin.php"); 
            exit();

        } else {
            // Contraseña incorrecta
            session_start();
            $_SESSION['login_error'] = "Correo o contraseña incorrectos. Por favor, inténtalo de nuevo.";
            header("Location: ./login.php"); 
            exit();
        }
    } else {
        // Usuario no existe
        session_start();
        $_SESSION['login_error'] = "Usuario no registrado. Pulsa en 'Crear Cuenta'";
        header('Location: ./login.php'); 
        exit();
    }
}
?>