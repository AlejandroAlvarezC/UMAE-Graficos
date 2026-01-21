<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['login_error'] = "Debes iniciar sesión como administrador para acceder a esa sección.";
    header('Location: ../admin/login.php');  // O la ruta correcta de tu login
    exit();
}
require_once '../../modelos/conexion.php';

// Conectar a la base de datos
$conexion = new Conexion();
$consulta = $conexion->consultar('SELECT DISTINCT clave, anio, especialidad, descripcion FROM especialidades');
$conexion->cerrar();

// Usar arrays para evitar duplicaciones en los filtros
$opcionesAnioUnico = [];
$opcionesEspecialidadUnica = [];
$opcionesDescripcionUnica = [];
$optionsClave = "";
$optionsAnio = "";
$optionsEspecialidad = "";
$optionsDescripcion = "";

if (!empty($consulta)) {
    foreach ($consulta as $fila) {
        // Evitar que los años se repitan en el <select>
        if (!in_array($fila[1], $opcionesAnioUnico)) {
            $optionsAnio .= "<option value='{$fila[1]}'>{$fila[1]}</option>";
            $opcionesAnioUnico[] = $fila[1]; // Guardar el año para evitar duplicados
        }

        // Evitar que las especialidades se repitan en el <select>
        if (!in_array($fila[2], $opcionesEspecialidadUnica)) {
            $optionsEspecialidad .= "<option value='{$fila[2]}'>{$fila[2]}</option>";
            $opcionesEspecialidadUnica[] = $fila[2]; // Guardar la especialidad para evitar duplicados
        }

        // Evitar que las descripciones se repitan en el <select>
        if (!in_array($fila[3], $opcionesDescripcionUnica)) {
            $optionsDescripcion .= "<option value='{$fila[3]}'>{$fila[3]}</option>";
            $opcionesDescripcionUnica[] = $fila[3]; // Guardar la descripción para evitar duplicados
        }

        $optionsClave .= "<option value='{$fila[0]}'>{$fila[0]}</option>";
    }
} else {
    // Si no hay datos en la base de datos, mostrar mensaje en cada filtro
    $optionsClave = "<option value=''>No hay registros</option>";
    $optionsAnio = "<option value=''>No hay registros</option>";
    $optionsEspecialidad = "<option value=''>No hay registros</option>";
    $optionsDescripcion = "<option value=''>No hay registros</option>";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar datos | Especialidad de Ocasión | Administrador</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../../logo-imss.png">
</head>
<style>
    /* Estructura del layout */
    html,
    body {
        height: 100%;
        margin: 0;
        display: flex;
        flex-direction: column;
    }

    /* Contenedor principal que ocupa el espacio disponible */
    .content {
        flex: 1;
    }

    /* hover nav */
    .nav-link {
        transition: color 0.3s;
    }

    .nav-link:hover {
        color: #9A7D0A !important;

    }

    .navbar-brand {
        transition: color 0.3s;
    }

    .navbar-brand:hover {
        color: #9A7D0A !important;
    }

    .dropdown-submenu {
        display: none;
        margin-left: 1rem;
    }

    .dropdown-submenu-toggle.active+.dropdown-submenu {
        display: block;
    }

    .dropdown-submenu-toggle {
        cursor: pointer;
    }

    /* Cambiar el color de fondo cuando el submenú está activo */
    .dropdown-item.dropdown-submenu-toggle.active,
    .dropdown-item.dropdown-submenu-toggle:hover {
        background-color: rgb(202, 155, 26) !important;
        color: white;
    }
</style>

<body style="background-color: aliceblue;">


    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #7a123a;">
        <div class="container-fluid">

            <!-- Botón para colapsar el menú en pantallas pequeñas -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menú principal -->
            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link text-white" href="../admin/admin.php">INICIO</a></li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#"
                            data-bs-toggle="dropdown">Productividad</a>
                        <ul class="dropdown-menu" data-bs-auto-close="outside">

                            <!-- Consulta externa -->
                            <li>
                                <a class="dropdown-item dropdown-submenu-toggle">Consulta externa</a>
                                <ul class="dropdown-submenu">
                                    <li><a class="dropdown-item" href="../productividad/unidades_que_reportan.php">Productividad total</a></li>
                                    <li><a class="dropdown-item" href="../productividad/paramedicos.php">Paramédicos</a>
                                    </li>

                                    <!-- Especialidades -->
                                    <li>
                                        <a class="dropdown-item dropdown-submenu-toggle">Especialidades</a>
                                        <ul class="dropdown-submenu">
                                            <li><a class="dropdown-item"
                                                    href="../productividad/especialidades_inicio.php">Especialidades</a>
                                            </li>
                                            <li><a class="dropdown-item" href="../productividad/Especialidad_Ocasion.php">Especialidad
                                                    de
                                                    ocasión</a></li>
                                        </ul>
                                    </li>

                                </ul>
                            </li>

                            <!-- Hospitalización -->
                            <li>
                                <a class="dropdown-item dropdown-submenu-toggle">Hospitalización</a>
                                <ul class="dropdown-submenu">
                                    <li><a class="dropdown-item" href="../hospitalizacion/ingresos_inicio.php">Ingresos</a></li>
                                    <li><a class="dropdown-item" href="../hospitalizacion/egresos_inicio.php">Egresos</a></li>
                                    <li><a class="dropdown-item" href="../hospitalizacion/pacientes_inicio.php">Días Paciente</a></li>
                                    <li><a class="dropdown-item" href="#">Días Cama</a></li>
                                </ul>
                            </li>

                            <!-- Cirugía -->
                            <li><a class="dropdown-item" href="../cirugia/cirugia_inicio.php">Cirugía</a></li>

                            <!-- Urgencias -->
                            <li><a class="dropdown-item" href="../productividad/urgencias_inicio.php">Urgencias</a></li>

                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="../admin/Personal/personal.php">Organigrama</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="../productividad/vencer.php">Vencer</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="../normatividad/normatividad_inicio.php">Normatividad</a>
                    </li>

                    <li class="nav-item"><a class="nav-link text-white" href="../admin/usuariosAdmin.php">Usuario</a>
                    </li>
                </ul>

                <!-- Botones a la derecha -->
                <div class="d-flex align-items-center gap-2">
                    <a class="btn btn-outline-light" onclick="anterior()" href="#" title="Atrás"><i class="bi bi-arrow-left"></i></a>
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../admin/usuariosAdmin.php"><i
                                        class="fas fa-id-badge me-2"></i>Perfil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="../admin/logout.php"><i
                                        class="fas fa-sign-out-alt me-2"></i>Cerrar sesión</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>



    <div class="content">


        <!-- Contenido principal -->
        <div class="container mt-5">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i> Reemplazar registros</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        <div><strong>Atención:</strong> Esto eliminará los registros actuales y los reemplazará con los del nuevo archivo cargado según los filtros seleccionados.</div>
                    </div>

                    <!-- Contenedor para alertas -->
                    <div id="alert-container" style="position: fixed; top: 20px; right: 20px; z-index: 1050;"></div>

                    <form id="csvForm" action="../../controladores/Especialidad_Ocasion.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="a" value="ReemplazarCSVFiltrado">

                        <label>Año:</label>
                        <select name="filtro_anio" class="form-control">
                            <option value="ninguno">Ninguno</option>
                            <?= $optionsAnio ?>
                        </select>

                        <label>Clave:</label>
                        <select name="filtro_clave" class="form-control">
                            <option value="ninguno">Ninguno</option>
                            <?= $optionsClave ?>
                        </select>

                        <label>Especialidad:</label>
                        <select name="filtro_especialidad" class="form-control">
                            <option value="ninguno">Ninguno</option>
                            <?= $optionsEspecialidad ?>
                        </select>

                        <label>División:</label>
                        <select name="filtro_descripcion" class="form-control">
                            <option value="ninguno">Ninguno</option>
                            <?= $optionsDescripcion ?>
                        </select>

                        <div class="mb-3 mt-3">
                            <label for="csv" class="form-label">Archivo CSV:</label>
                            <input type="file" name="csv" class="form-control" accept=".csv">
                        </div>

                        <button type="submit" class="btn btn-success">Actualizar solo Coincidencias</button>
                    </form>
                </div>

                <div class="card-footer text-end">
                    <a href="Especialidad_Ocasion.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>

    </div>




    <!-- Footer -->
    <footer class="text-white text-center py-3 mt-5" style="background-color: #7a123a;">
        <div class="container">
            <p class="mb-0">© <?php echo date("Y"); ?> IMSS. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Scripts -->

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>

    <script src="../../js/scripts.js" defer></script>
    <script src="../../js/bootstrap.bundle.min.js" defer></script>

    <script>
        document.getElementById('csvForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const alertContainer = document.getElementById('alert-container');
            alertContainer.innerHTML = '';

            const fileInput = this.querySelector('input[type="file"][name="csv"]');
            const file = fileInput.files[0];

            if (!file) {
                showAlert('Por favor, selecciona un archivo', 'danger');
                return;
            }

            if (!file.name.toLowerCase().endsWith('.csv')) {
                showAlert('El archivo debe tener extensión .csv', 'danger');
                return;
            }

            // ✅ Validar año después del archivo
            const anioSeleccionado = this.querySelector('select[name="filtro_anio"]').value;
            if (anioSeleccionado === "ninguno") {
                showAlert('Debes seleccionar un año para continuar con la actualización.', 'danger');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                const text = event.target.result;
                const lines = text.split(/\r\n|\n/);
                if (lines.length < 2) {
                    showAlert('El archivo CSV está vacío o no tiene datos.', 'danger');
                    return;
                }
                const header = lines[0].split(',');
                const expectedColumns = 28; // Igual que en PHP
                if (header.length !== expectedColumns) {
                    showAlert(`El archivo CSV debe tener ${expectedColumns} columnas. Se detectaron ${header.length}.`, 'danger');
                    return;
                }

                const formData = new FormData(document.getElementById('csvForm'));
                fetch('../../controladores/Especialidad_Ocasion.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showAlert(data.message, 'success');
                            setTimeout(() => {
                                // Redirige a la página que quieras
                                window.location.href = 'Especialidad_Ocasion.php';
                            }, 3000);
                        } else {
                            showAlert(data.message, 'danger');
                        }
                    })
                    .catch(() => {
                        showAlert('Error al procesar el archivo.', 'danger');
                    });
            };
            reader.onerror = function() {
                showAlert('No se pudo leer el archivo.', 'danger');
            };
            reader.readAsText(file);

            function showAlert(message, type = 'info') {
                const wrapper = document.createElement('div');
                wrapper.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="min-width: 300px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        `;
                alertContainer.appendChild(wrapper);
                setTimeout(() => {
                    const alert = bootstrap.Alert.getOrCreateInstance(wrapper.querySelector('.alert'));
                    alert.close();
                }, 5000);
            }
        });
    </script>

    <script>
        document.querySelectorAll('.dropdown-submenu-toggle').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Evita que Bootstrap cierre el menú

                // Cierra submenús hermanos
                const parentMenu = el.closest('ul');
                parentMenu.querySelectorAll('.dropdown-submenu-toggle').forEach(function(toggle) {
                    if (toggle !== el) toggle.classList.remove('active');
                });

                // Alterna el submenú actual
                el.classList.toggle('active');
            });
        });
    </script>

</body>

</html>