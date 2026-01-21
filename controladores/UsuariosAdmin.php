<?php require_once '../modelos/UsuariosAdmin.php';

$accion = $_POST['a'] ?? $_GET['a'] ?? '';

if ($accion != '') {
	$rol = new Usuarios();

	switch ($accion) {
		case 'Ingresar':
			$rol->names = $_POST['names'];
			$rol->email=$_POST['email'];
			$rol->pasword= password_hash($_POST['pasword'], PASSWORD_DEFAULT);
			$rol->ingresar();

            header('Location: ../vistas/admin/usuariosAdmin.php');

			break;

        case 'Registrar':
            $rol->names = $_POST['names'];
            $rol->email=$_POST['email'];
            $rol->pasword= password_hash($_POST['pasword'], PASSWORD_DEFAULT);
            $rol->ingresar(); 

            header('Location: ../vistas/admin/usuariosAdmin.php');
            break;

		case 'Editar':
			$rol->id= base64_decode($_POST['id']);
			$rol->names = $_POST['names'];
			$rol->email=$_POST['email'];
			
		
			 if (!empty($_POST['pasword'])) {
             
                $rol->pasword = password_hash($_POST['pasword'], PASSWORD_DEFAULT);
            } else {
            
                $rol->pasword = $_POST['pasword_actual'];
            }

            $rol->editar();
            
         
            header('Location: ../vistas/admin/usuariosAdmin.php?msg=Usuario editado correctamente');
            exit();

		case 'Eliminar':
			$rol->id = base64_decode($_GET['id']);
			$rol->eliminar();

            header('Location: ../vistas/admin/usuariosAdmin.php');
			break;

    case 'CambiarContraseña':
      
    $userId = $_POST['udmin_id'];
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    
    if ($newPassword !== $confirmPassword) {
        header('Location: ../vistas/admin/usuariosAdmin.php?error=Las contraseñas no coinciden');
        exit();
    }

    
    $resultado = Usuarios::cambiarPassword($userId, $currentPassword, $newPassword);

    
    if ($resultado) {
        header('Location: ../vistas/admin/perfil.php?msg=Contraseña actualizada correctamente');
    } else {
        header('Location: ../vistas/admin/perfil.php?error=Error al actualizar la contraseña');
    }
    exit();

    }
}
