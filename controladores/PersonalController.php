<?php
require_once '../modelos/Personal.php';

$accion = $_POST['a'] ?? $_GET['a'] ?? '';

if ($accion != '') {
    $personal = new Personal();

    switch ($accion) {
        case 'Ingresar':
            $personal->nombre = $_POST['nombre'];
            $personal->apaterno = $_POST['apaterno'];
            $personal->amaterno = $_POST['amaterno'];
            $personal->area = $_POST['area'];
            $personal->puesto = $_POST['puesto'];
            $personal->telefono = $_POST['telefono'];
            $personal->extension = $_POST['extension'];
            $personal->correo = $_POST['correo'];
            $personal->jefe_id = !empty($_POST['jefe_id']) ? $_POST['jefe_id'] : null;

            // Manejo de la imagen
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $nombreFoto = time() . '_' . basename($_FILES['foto']['name']);
                $ruta = '../img/' . $nombreFoto;
                move_uploaded_file($_FILES['foto']['tmp_name'], $ruta);
                $personal->foto = $nombreFoto;
            } else {
                $personal->foto = '';
            }

            $personal->ingresar();
            header('Location: ../vistas/admin/Personal/personal.php');
            break;

        case 'Editar':
            $personal->id = base64_decode($_POST['id']);
            $personal->nombre = $_POST['nombre'];
            $personal->apaterno = $_POST['apaterno'];
            $personal->amaterno = $_POST['amaterno'];
            $personal->area = $_POST['area'];
            $personal->puesto = $_POST['puesto'];
            $personal->telefono = $_POST['telefono'];
            $personal->extension = $_POST['extension'];
            $personal->correo = $_POST['correo'];
            $personal->jefe_id = !empty($_POST['jefe_id']) ? $_POST['jefe_id'] : null;


            $foto_anterior = $_POST['foto_actual'];

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $nombreFoto = time() . '_' . basename($_FILES['foto']['name']);
                $ruta = '../img/' . $nombreFoto;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta)) {
                    // Eliminar la foto anterior si existe y no está vacía
                    $rutaAnterior = '../img/' . $foto_anterior;
                    if (!empty($foto_anterior) && file_exists($rutaAnterior)) {
                        unlink($rutaAnterior);
                    }

                    $personal->foto = $nombreFoto;
                } else {
                    // Si no se pudo subir la nueva, conserva la anterior
                    $personal->foto = $foto_anterior;
                }
            } else {
                // Si no se subió ninguna foto nueva, mantener la existente
                $personal->foto = $foto_anterior;
            }

            $personal->editar();
            header('Location: ../vistas/admin/Personal/Personal.php?msg=Personal editado correctamente');
            exit();

        case 'Eliminar':
            $id = base64_decode($_GET['id']);
            $personal->id = $id;

            // Obtener los datos del personal antes de eliminar
            $datos = Personal::obtenerPorId($id);

            // Si existe una foto y no es la imagen por defecto, eliminarla del servidor
            $foto = $datos[9] ?? '';
            if (!empty($foto) && $foto !== 'default.jpg') {
                $ruta_foto = '../img/' . $foto;
                if (file_exists($ruta_foto)) {
                    unlink($ruta_foto);
                }
            }

            // Eliminar el registro de la base de datos
            $personal->eliminar();

            // Redirigir
            header('Location: ../vistas/admin/Personal/personal.php');
            break;



        case 'OrganigramaJerarquico':
            $datos = Personal::obtenerJerarquia();
            header('Content-Type: application/json');
            echo json_encode($datos);
            exit();
            break;
    }
}

header('Location: ../vistas/admin/Personal/personal.php');
exit();
