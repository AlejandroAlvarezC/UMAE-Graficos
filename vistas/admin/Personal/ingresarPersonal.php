<?php session_start();
require_once '../../../modelos/Personal.php';

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
    <link rel="icon" type="image/png" href="../../../logo-imss.png" />
    <link rel="stylesheet" href="../../../css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <title>Agregar Personal | Organigrama</title>


    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        .content {
            flex: 1;
        }

        .form-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 25px 20px;
        }

        .form-label {
            font-size: 0.95rem;
        }

        .form-control {
            font-size: 0.9rem;
            padding: 5px 9px;
        }

        .btn-sm {
            padding: 6px 16px;
            font-size: 0.875rem;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: start;
            margin-top: 20px;
        }

        .select2-selection__clear {
            font-size: 1.3rem !important;
            height: 100%;
            display: flex;
            align-items: center;
        }

        /* -----------------------------------
   NAVBAR Y DROPDOWN
----------------------------------- */
        .nav-link {
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: rgb(204, 167, 17) !important;
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

        .dropdown-item.dropdown-submenu-toggle.active,
        .dropdown-item.dropdown-submenu-toggle:hover {
            background-color: rgb(202, 155, 26) !important;
            color: white;
        }

        /* -----------------------------------
   FOOTER
----------------------------------- */
        footer {
            background-color: #7a123a;
            width: 100%;
            text-align: center;
            color: white;
            font-weight: bold;
            padding-top: 10px;
        }
    </style>
</head>

<body class="bg-light">

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
                    <li class="nav-item"><a class="nav-link text-white" href="../../admin/admin.php">INICIO</a></li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#"
                            data-bs-toggle="dropdown">Productividad</a>
                        <ul class="dropdown-menu" data-bs-auto-close="outside">

                            <!-- Consulta externa -->
                            <li>
                                <a class="dropdown-item dropdown-submenu-toggle">Consulta externa</a>
                                <ul class="dropdown-submenu">
                                    <li><a class="dropdown-item" href="../../productividad/unidades_que_reportan.php">Productividad total</a></li>
                                    <li><a class="dropdown-item" href="../../productividad/paramedicos.php">Paramédicos</a>
                                    </li>

                                    <!-- Especialidades -->
                                    <li>
                                        <a class="dropdown-item dropdown-submenu-toggle">Especialidades</a>
                                        <ul class="dropdown-submenu">
                                            <li><a class="dropdown-item"
                                                    href="../../productividad/especialidades_inicio.php">Especialidades</a>
                                            </li>
                                            <li><a class="dropdown-item" href="../../productividad/Especialidad_Ocasion.php">Especialidad
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
                                    <li><a class="dropdown-item" href="../../hospitalizacion/ingresos_inicio.php">Ingresos</a></li>
                                    <li><a class="dropdown-item" href="../../hospitalizacion/egresos_inicio.php">Egresos</a></li>
                                    <li><a class="dropdown-item" href="../../hospitalizacion/pacientes_inicio.php">Días Paciente</a></li>
                                    <li><a class="dropdown-item" href="#">Días Cama</a></li>
                                </ul>
                            </li>

                            <!-- Cirugía -->
                            <li><a class="dropdown-item" href="../../cirugia/cirugia_inicio.php">Cirugía</a></li>

                            <!-- Urgencias -->
                            <li><a class="dropdown-item" href="../../productividad/urgencias_inicio.php">Urgencias</a></li>

                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="../../admin/Personal/personal.php">Organigrama</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="../../productividad/vencer.php">Vencer</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="../../normatividad/normatividad_inicio.php">Normatividad</a>
                    </li>

                    <li class="nav-item"><a class="nav-link text-white" href="../../admin/usuariosAdmin.php">Usuario</a>
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
                            <li><a class="dropdown-item" href="../../admin/usuariosAdmin.php"><i
                                        class="fas fa-id-badge me-2"></i>Perfil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="../../admin/logout.php"><i
                                        class="fas fa-sign-out-alt me-2"></i>Cerrar sesión</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>



    <div class="container form-container bg-white rounded shadow-sm">
        <h4 class="mb-3 text-center">Agregar Personal</h4>
        <form action="../../../controladores/PersonalController.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="a" value="Ingresar">

            <div class="row mb-2">
                <div class="col-md-4">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="apaterno" class="form-label">Apellido Paterno</label>
                    <input type="text" name="apaterno" id="apaterno" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="amaterno" class="form-label">Apellido Materno</label>
                    <input type="text" name="amaterno" id="amaterno" class="form-control" required>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-6">
                    <label for="area" class="form-label">Área</label>
                    <input type="text" name="area" id="area" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="puesto" class="form-label">Puesto</label>
                    <input type="text" name="puesto" id="puesto" class="form-control" required>
                </div>
            </div>

            <div class="mb-2">
                <label for="jefe_id" class="form-label">Jefe Directo</label>
                <select name="jefe_id" id="jefe_id" class="form-select js-jefe-select"
                    style="font-size: 0.9rem; padding: 5px 9px;">
                    <option value="">-- Ninguno --</option>
                    <?php foreach (Personal::listar() as $jefe): ?>
                        <option value="<?= $jefe[0] ?>">
                            <?= $jefe[1] . ' ' . ($jefe[2] ?? '') . ' ' . ($jefe[3] ?? '') ?>
                            (<?= $jefe[4] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>


            <div class="row mb-2">
                <div class="col-md-4">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" name="telefono" id="telefono" class="form-control" required maxlength="10">
                </div>
                <div class="col-md-4">
                    <label for="extension" class="form-label">Extensión</label>
                    <input type="text" name="extension" id="extension" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="correo" class="form-label">Correo</label>
                    <input type="email" name="correo" id="correo" class="form-control" required>
                </div>
            </div>

            <div class="mb-2">
                <label for="foto" class="form-label">Foto (opcional)</label>
                <input type="file" name="foto" id="foto" class="form-control">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success btn-sm">Guardar</button>
                <a href="personal.php" class="btn btn-secondary btn-sm">Cancelar</a>
            </div>
        </form>
    </div>



    <!-- Footer -->
    <footer class="text-white text-center py-3 mt-5" style="background-color: #7a123a;">
        <div class="container">
            <p class="mb-0">© <?php echo date("Y"); ?> IMSS. Todos los derechos reservados.</p>
        </div>
    </footer>


    <script src="../../../js/bootstrap.bundle.min.js"></script>
    <script src="../../../js/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.js-jefe-select').select2({
                placeholder: "-- Ninguno --",
                allowClear: true,
                width: '100%'
            });

            // Cuando el select se abre
            $('.js-jefe-select').on('select2:open', function() {
                // Esperamos un poco para asegurar que el input existe
                setTimeout(() => {
                    let input = document.querySelector(
                        '.select2-container input.select2-search__field');

                }, 100);
            });
        });

        // SUBMENU --- //
        document.querySelectorAll('.card-submenu-toggle').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();

                document.querySelectorAll('.card-submenu-toggle').forEach(function(toggle) {
                    if (toggle !== el) {
                        toggle.classList.remove('active');
                        const submenu = toggle.nextElementSibling;
                        if (submenu && submenu.classList.contains('card-submenu')) {
                            submenu.style.display = 'none';
                        }
                    }
                });

                el.classList.toggle('active');
                const submenu = el.nextElementSibling;
                if (submenu && submenu.classList.contains('card-submenu')) {
                    submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
                }
            });
        });

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