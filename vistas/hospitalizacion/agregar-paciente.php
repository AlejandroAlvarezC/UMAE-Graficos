<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['login_error'] = "Debes iniciar sesión como administrador para acceder a esa sección.";
    header('Location: ../admin/login.php');  // O la ruta correcta de tu login
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar - Días Paciente</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/styles.css">
    <link rel="icon" type="image/png" href="../../logo-imss.png">

    <style>
        .footer-fijo {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: 999;
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

</head>

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
                                    <li><a class="dropdown-item" href="../productividad/unidades_que_reportan.php">Productividad Total</a></li>
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
                                    <li><a class="dropdown-item" href="./ingresos_inicio.php">Ingresos</a></li>
                                    <li><a class="dropdown-item" href="./egresos_inicio.php">Egresos</a></li>
                                    <li><a class="dropdown-item" href="./pacientes_inicio.php">Días Paciente</a></li>
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



    <!-- Contenido principal -->
    <div class="container mt-5">

        <!-- Contenido de carga de archivo de Urgencias -->
        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-upload me-2"></i>Cargar excel.csv para agregar registros - Días paciente</h5>
            </div>
            <div class="card-body">
                <form id="formCSV" enctype="multipart/form-data">
                    <input type="hidden" name="a" value="CargarCSV">
                    <div class="mb-3">
                        <label for="csv" class="form-label">Selecciona tu archivo en formato CSV:</label>
                        <input type="file" name="csv" id="csv" class="form-control" accept=".csv">
                    </div>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-cloud-arrow-up"></i> Subir Archivo
                    </button>
                </form>

                <!-- Contenedor de resultados -->
                <div id="resultadoCarga" class="mt-3">
                    <div class="alert-placeholder"></div>
                </div>
            </div>
            <div class="card-footer text-end">
                <a href="./pacientes_inicio.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>



        <!-- Tarjeta para visualizar y descargar archivo - Urgencias -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-eye me-2"></i> Visualizar y descargar plantilla CSV</h5>
            </div>
            <div class="card-body">
                <p>Puedes visualizar una plantilla de ejemplo antes de cargar tu archivo CSV:</p>

                <!-- Visualización como tabla -->
                <div class="table-responsive" style="max-height: 150px; overflow-y: auto;">
                    <table class="table table-bordered table-sm">
                        <tbody>
                            <?php
                            $ruta_csv = '../../Días_paciente.csv';
                            if (file_exists($ruta_csv)) {
                                if (($handle = fopen($ruta_csv, "r")) !== false) {
                                    while (($line = fgets($handle)) !== false) {
                                        $line = mb_convert_encoding($line, 'UTF-8', 'Windows-1252');
                                        $data = str_getcsv($line, ",");
                                        echo "<tr>";
                                        foreach ($data as $cell) {
                                            echo "<td>" . htmlspecialchars($cell) . "</td>";
                                        }
                                        echo "</tr>";
                                    }
                                    fclose($handle);
                                } else {
                                    echo "<tr><td colspan='100%'>No se pudo leer el archivo.</td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='100%'>Archivo no encontrado.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Botón para descargar -->
                <a href="../../Días_paciente.csv" class="btn btn-danger mt-3" download>
                    <i class="bi bi-download"></i> Descargar plantilla CSV
                </a>
            </div>
        </div>


    </div>
    <br><br><br><br>


    <!-- Footer -->
    <footer class="footer-fijo text-white text-center py-3" style="background-color: #7a123a;">
        <p>Derechos reservados &copy; IMSS 2025</p>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/scripts.js" defer></script>
    <script src="../../js/bootstrap.bundle.min.js" defer></script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>


    <script>
        document.getElementById('formCSV').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const resultado = document.getElementById('resultadoCarga');
            const contenedor = resultado.querySelector('.alert-placeholder');

            contenedor.innerHTML = ''; // Limpiar alertas anteriores

            fetch('../../controladores/dias_pacientes.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    contenedor.innerHTML = '';

                    // Detectar tipo de mensaje según contenido
                    let icon = 'bi-info-circle-fill';
                    let alertClass = 'info';

                    // Prioridad: éxito > error > advertencia > actualización
                    if (data.message.includes('✅') || data.message.toLowerCase().includes('insertados')) {
                        icon = 'bi-check-circle-fill';
                        alertClass = 'success';
                    } else if (data.message.includes('❌') || data.status === 'error') {
                        icon = 'bi-x-circle-fill';
                        alertClass = 'danger';
                    } else if (data.message.includes('⚠️') || data.message.toLowerCase().includes('advertencia')) {
                        icon = 'bi-exclamation-triangle-fill';
                        alertClass = 'warning';
                    } else if (data.message.includes('🔄') || data.message.toLowerCase().includes('actualizados')) {
                        icon = 'bi-arrow-repeat';
                        alertClass = 'primary';
                    }

                    const alerta = document.createElement('div');
                    alerta.className = `alert alert-${alertClass} alert-dismissible fade show mb-2 d-flex align-items-center`;
                    alerta.setAttribute('role', 'alert');
                    alerta.innerHTML = `
            <i class="bi ${icon} me-2 fs-5"></i>
            <div>${data.message}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

                    contenedor.appendChild(alerta);

                    // Si no es error, redirigir después de mostrar
                    if (alertClass !== 'danger') {
                        setTimeout(() => {
                            alerta.classList.remove('show');
                            alerta.classList.add('hide');
                            setTimeout(() => {
                                alerta.remove();
                                window.location.href = './pacientes_inicio.php';
                            }, 500);
                        }, 8000);
                    }
                })
                .catch(err => {
                    const alerta = document.createElement('div');
                    alerta.className = 'alert alert-danger alert-dismissible fade show mb-2 d-flex align-items-center';
                    alerta.setAttribute('role', 'alert');
                    alerta.innerHTML = `
            <i class="bi bi-x-circle-fill me-2 fs-5"></i>
            <div>Error de red o servidor al cargar el archivo.</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
                    contenedor.appendChild(alerta);

                    setTimeout(() => {
                        alerta.classList.remove('show');
                        alerta.classList.add('hide');
                        setTimeout(() => alerta.remove(), 500);
                    }, 6000);

                    console.error(err);
                });
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