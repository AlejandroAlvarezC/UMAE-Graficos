<?php
require_once '../modelos/Sitio-interes.php';

$accion = $_POST['a'] ?? $_GET['a'] ?? '';
$imagenPorDefecto = 'IMSS.png'; // Define la imagen por defecto

if ($accion === 'Subir') {
    $sitio = new SitioInteres();
    $sitio->nombre = $_POST['nombre'];
    $sitio->descripcion = $_POST['descripcion'] ?? '';
    $sitio->ruta = $_POST['ruta'] ?? '#';

    $directorio = '../archivos/sitios_interes/';

    if (!empty($_FILES['imagen']['name'])) {
        $nombreFinal = time() . '_' . basename($_FILES['imagen']['name']);
        $rutaDestino = $directorio . $nombreFinal;
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
            $sitio->imagen = $nombreFinal;
        } else {
            $sitio->imagen = $imagenPorDefecto;
        }
    } else {
        $sitio->imagen = $imagenPorDefecto;
    }

    $sitio->ingresar();

} elseif ($accion === 'Editar') {
    $sitio = new SitioInteres();
    $sitio->id = base64_decode($_POST['id']);
    $sitio->nombre = $_POST['nombre'];
    $sitio->descripcion = $_POST['descripcion'] ?? '';
    $sitio->ruta = $_POST['ruta'] ?? '#';

    $directorio = '../archivos/sitios_interes/';

    if (!empty($_FILES['imagen']['name'])) {
        $imagenVieja = $directorio . $_POST['imagen_actual'];
        // Solo borrar si no es la imagen por defecto
        if (!empty($_POST['imagen_actual']) && $_POST['imagen_actual'] !== $imagenPorDefecto && file_exists($imagenVieja)) {
            unlink($imagenVieja);
        }

        $nombreFinal = time() . '_' . basename($_FILES['imagen']['name']);
        $rutaDestino = $directorio . $nombreFinal;
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
            $sitio->imagen = $nombreFinal;
        } else {
            $sitio->imagen = $_POST['imagen_actual'];
        }
    } else {
        $sitio->imagen = $_POST['imagen_actual'];
    }

    $sitio->editar();

} elseif ($accion === 'elim') {
    $sitio = new SitioInteres();
    $sitio->id = base64_decode($_GET['id']);

    $sitioDatos = SitioInteres::obtenerPorId($sitio->id);

    $directorio = '../archivos/sitios_interes/';
    $archivo = $directorio . $sitioDatos[3]; // índice 3: imagen

    // Solo borrar si no es la imagen por defecto
    if (!empty($sitioDatos[3]) && $sitioDatos[3] !== $imagenPorDefecto && file_exists($archivo)) {
        unlink($archivo);
    }

    $sitio->eliminar();
}

header("Location: ../vistas/sitios-interes/index.php");
exit;
?>