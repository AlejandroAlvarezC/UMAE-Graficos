<?php
// 1. REGLA DE ORO: session_start siempre al inicio de todo
session_start();

if (isset($_POST['btnLogin'])) {
    $txtEmail = trim($_POST['email']);
    $txtPassword = $_POST['password'];
    $browser = $_SERVER['HTTP_USER_AGENT'];

    try {
        // 2. ABRIMOS UNA SOLA CONEXIÓN PARA TODO
        $host = 'sql112.infinityfree.com';
        $dbname = 'if0_41125231_vencer'; 
        $username = 'if0_41125231';
        $password = 'DEtK59bqZzA';

        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 3. CONSULTA SEGURA (Evita Inyección SQL)
        $sql = "SELECT * FROM admi WHERE Email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $txtEmail]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            // Verificamos si la contraseña es correcta
            if (password_verify($txtPassword, $admin['Pasword'])) {
                
                // Guardamos variables de sesión
                $_SESSION['admin_id'] = $admin['Id'];
                $_SESSION['admin_name'] = $admin['Names']; 
                $_SESSION['rol'] = $admin['rol']; 

                // 4. ACTUALIZAMOS EL CONTADOR (Usando la misma conexión $pdo)
                $sql_update = "UPDATE admi SET 
                                login_count = login_count + 1, 
                                last_login = NOW(), 
                                user_agent = :ua 
                                WHERE Id = :id";
                                
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute([
                    ':ua' => $browser,
                    ':id' => $admin['Id']
                ]);

                // 5. Todo salió perfecto, redirigimos al panel
                header("Location: admin.php"); 
                exit();

            } else {
                // Contraseña incorrecta
                $_SESSION['login_error'] = "Correo o contraseña incorrectos. Por favor, inténtalo de nuevo.";
                header("Location: login.php"); 
                exit();
            }
        } else {
            // Usuario no existe
            $_SESSION['login_error'] = "Usuario no registrado. Pulsa en 'Crear Cuenta'";
            header('Location: login.php'); 
            exit();
        }

    } catch(PDOException $e) {
        // Si la base de datos se cae, no mostramos pantalla blanca, avisamos.
        die("Error de conexión o actualización: " . $e->getMessage());
    }
} else {
    // Si entran directo sin usar el botón, los regresamos
    header('Location: login.php'); 
    exit();
}
?>