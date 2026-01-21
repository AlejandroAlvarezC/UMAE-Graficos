<?php session_start();

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['login_error'] = "Debes iniciar sesión como administrador para acceder a esa sección.";
    header('Location: ../admin/login.php');  // O la ruta correcta de tu login
    exit();
} ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar datos | VENCER | Administrador</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
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

        .btn-urgencia {
            background-color: #d55500ff;
            /* rojo fuerte y serio */
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-urgencia:hover {
            background-color: #dc6300ff;
            /* más claro al pasar */
            box-shadow: 0 6px 16px rgba(179, 143, 0, 0.4);
            transform: translateY(-2px);
            text-decoration: none;
            color: white;
        }
    </style>

</head>

<body style="background-color: #fffef6ff">

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
                                    <li><a class="dropdown-item" href="./unidades_que_reportan.php">Productividad Total</a></li>
                                    <li><a class="dropdown-item" href="./paramedicos.php">Paramédicos</a>
                                    </li>

                                    <!-- Especialidades -->
                                    <li>
                                        <a class="dropdown-item dropdown-submenu-toggle">Especialidades</a>
                                        <ul class="dropdown-submenu">
                                            <li><a class="dropdown-item"
                                                    href="./especialidades_inicio.php">Especialidades</a>
                                            </li>
                                            <li><a class="dropdown-item" href="./Especialidad_Ocasion.php">Especialidad
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
                            <li><a class="dropdown-item" href="./urgencias_inicio.php">Urgencias</a></li>

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
                    <a href="./paramedicos.php" class="btn btn btn-outline-light"><i class="bi bi-arrow-left"
                            title="Atrás"></i></a>
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
    <div class="container my-5">

        <!-- Tarjeta: Subir archivo CSV -->
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-header bg-dark text-white rounded-top-4">
                <h4 class="mb-0"><i class="bi bi-upload me-2"></i> Cargar Archivo CSV - Nuevos registros VENCER</h4>
            </div>

            <div class="card-body">
                <form id="formCSV" enctype="multipart/form-data" method="post">
                    <input type="hidden" name="a" value="CargarCSV">

                    <!-- Campo archivo CSV -->
                    <div class="row align-items-end g-3">
                        <div class="col-md-9">
                            <label for="csv" class="form-label fw-semibold">Selecciona tu archivo en formato CSV:</label>
                            <input type="file" name="csv" id="csv" class="form-control rounded-3 shadow-sm" accept=".csv" required>
                        </div>
                        <div class="col-md-3 text-end">
                            <button type="submit" class="btn btn-urgencia w-100 py-2 shadow-sm">
                                <i class="bi bi-cloud-arrow-up me-1"></i> Subir archivo
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Resultado de la carga -->
                <div id="resultadoCarga" class="mt-4">
                    <div class="alert-placeholder"></div>
                </div>
            </div>

            <div class="card-footer text-end bg-light rounded-bottom-4">
                <a href="./vencer.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>

        <!-- Tarjeta: Visualizar y descargar plantilla -->
        <div class="card shadow-lg border-0 rounded-4 mt-5">
            <div class="card-header bg-dark text-white rounded-top-4">
                <h4 class="mb-0"><i class="bi bi-eye me-2"></i> Visualizar / Descargar plantilla CSV</h4>
            </div>

            <div class="card-body">
                <p class="mb-3">Aquí puedes revisar la plantilla de ejemplo antes de cargar tu archivo:</p>

                <!-- Tabla CSV -->
                <div class="table-responsive border rounded-3 shadow-sm" style="max-height: 180px; overflow-y: auto;">
                    <table class="table table-bordered table-sm mb-0">
                        <tbody>
                            <?php
                            $ruta_csv = '../../vencer.csv';
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

                <!-- Botón de descarga -->
                <div class="text-end mt-3">
                    <a href="../../vencer.csv" class="btn btn-urgencia shadow-sm" download>
                        <i class="bi bi-download me-1"></i> Descargar plantilla
                    </a>
                </div>
            </div>
        </div>
    </div>
    <br><br><br><br>


    <!-- Footer -->
    <footer class="text-white text-center py-3 mt-5" style="background-color: #7a123a;">
        <div class="container">
            <p class="mb-0">© <?php echo date("Y"); ?> IMSS. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Scripts -->

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>

    <script src="../../js/scripts.js" defer></script>
    <script src="../../js/bootstrap.bundle.min.js" defer></script>


    <script>
        document.getElementById('formCSV').addEventListener('submit', function(e) {
            e.preventDefault();

            const fileInput = document.getElementById('csv');
            const submitButton = this.querySelector('button[type="submit"]');
            const resultado = document.getElementById('resultadoCarga');
            const contenedor = resultado.querySelector('.alert-placeholder');

            contenedor.innerHTML = ''; // Limpiar alertas anteriores

            // Validación: archivo obligatorio
            if (!fileInput.files.length) {
                const alerta = document.createElement('div');
                alerta.className = 'alert alert-warning alert-dismissible fade show mb-2 d-flex align-items-center';
                alerta.setAttribute('role', 'alert');
                alerta.innerHTML = `
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div>⚠️ Debes seleccionar un archivo CSV antes de enviarlo.</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
                contenedor.appendChild(alerta);
                return;
            }

            const formData = new FormData(this);
            submitButton.disabled = true; // Evita doble clic

            fetch('../../controladores/vencer.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    contenedor.innerHTML = '';

                    // Detectar tipo de mensaje
                    let icon = 'bi-info-circle-fill';
                    let alertClass = 'info';

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

                    if (alertClass !== 'danger') {
                        setTimeout(() => {
                            alerta.classList.remove('show');
                            alerta.classList.add('hide');
                            setTimeout(() => {
                                alerta.remove();
                                window.location.href = './vencer.php';
                            }, 500);
                        }, 8000);
                    } else {
                        submitButton.disabled = false; // Reactivar botón si hubo error
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
                    submitButton.disabled = false; // Reactivar botón
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