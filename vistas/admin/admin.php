<?php
include('verificar_sesion.php');

require_once '../../modelos/UsuariosAdmin.php';
require_once '../../modelos/Normatividad.php';
require_once '../../modelos/Especialidad_Ocasion.php';
require_once '../../modelos/urgencia.php';

// Verificamos si NO existe la variable rol, o si el rol NO es 'admin'
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    // Si es un 'viewer' (o cualquier otra cosa), lo expulsamos a la vista de usuario normal
    header("Location: ../roles/index.php");
    exit(); // Detenemos la ejecución para que no cargue el resto de la página
}


// Contar usuarios
$totalUsuarios = Usuarios::contarUsuarios();

// Contar Especialidad de Ocasión
$totalEspecialidad_Ocasion = Especialidad_Ocasion::contarEspecialidadOcasion();

// Contar Manuales
$totalManuales = Manual::contarManuales();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Panel de administrador | Administrador</title>

    <!-- Bootstrap y estilos -->
    <link rel="stylesheet" href="../../css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="icon" type="image/png" href="../../logo-imss.png" />

    <style>
        * {
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            padding: 0;
        }

        .content {
            flex: 1;
            padding: 15px;
            background-color: #f5f8fb;
        }

        .card-body i {
            font-size: 40px;
        }

        .card .btn {
            margin-top: 10px;
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

        .card-submenu {
            display: none;
            transition: all 0.3s ease;
        }

        .card-submenu-toggle.active+.card-submenu {
            display: block;
        }

        .card-submenu-toggle {
            cursor: pointer;
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

        /* ----------------------------------------------
           Footer
        ------------------------------------------------*/

        footer {
            background-color: #7a123a;
            width: 100%;
            text-align: center;
            color: white;
            font-weight: bold;
            padding: 10px 0;
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

                    <li class="nav-item"><a class="nav-link text-white" href="admin.php">INICIO</a></li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#"
                            data-bs-toggle="dropdown">Productividad</a>
                        <ul class="dropdown-menu" data-bs-auto-close="outside">

                            <!-- Consulta externa -->
                            <li>
                                <a class="dropdown-item dropdown-submenu-toggle">Consulta externa</a>
                                <ul class="dropdown-submenu">
                                    <li><a class="dropdown-item"
                                            href="../productividad/unidades_que_reportan.php">Unidades
                                            que reportan</a></li>
                                    <li><a class="dropdown-item" href="../productividad/paramedicos.php">Paramédicos</a>
                                    </li>

                                    <!-- Especialidades -->
                                    <li>
                                        <a class="dropdown-item dropdown-submenu-toggle">Especialidades</a>
                                        <ul class="dropdown-submenu">
                                            <li><a class="dropdown-item"
                                                    href="../productividad/especialidades_inicio.php">Especialidades</a>
                                            </li>
                                            <li><a class="dropdown-item"
                                                    href="../productividad/Especialidad_Ocasion.php">Especialidad de
                                                    ocasión</a></li>
                                        </ul>
                                    </li>

                                </ul>
                            </li>

                            <!-- Hospitalización -->
                            <li>
                                <a class="dropdown-item dropdown-submenu-toggle">Hospitalización</a>
                                <ul class="dropdown-submenu">
                                    <li><a class="dropdown-item"
                                            href="../hospitalizacion/ingresos_inicio.php">Ingresos</a></li>
                                    <li><a class="dropdown-item"
                                            href="../hospitalizacion/egresos_inicio.php">Egresos</a></li>
                                    <li><a class="dropdown-item" href="../hospitalizacion/pacientes_inicio.php">Días
                                            Paciente</a></li>
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
                        <a class="nav-link text-white" href="./Personal/personal.php">Organigrama</a>
                    </li>

                    <!--
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../productividad/vencer.php">Vencer</a>
                    </li>
-->

                    <li class="nav-item">
                        <a class="nav-link text-white" href="/">Vencer</a>
                    </li>

                    <!-- Normatividad -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../normatividad/normatividad_inicio.php">Normatividad</a>
                    </li>

                    <li class="nav-item"><a class="nav-link text-white" href="usuariosAdmin.php">Usuario</a></li>
                </ul>

                <!-- Botones a la derecha -->
                <div class="d-flex align-items-center gap-2">
                    <a class="btn btn-outline-light" onclick="anterior()" href="#" title="Atrás"><i
                            class="bi bi-arrow-left"></i></a>
                    <a class="btn btn-outline-light" href="../roles/index.php">Vista de usuario</a>

                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../roles/index.php"><i class="fas fa-eye me-2"></i>Ver
                                    como Usuario</a></li>
                            <li><a class="dropdown-item" href="usuariosAdmin.php"><i
                                        class="fas fa-id-badge me-2"></i>Perfil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i
                                        class="fas fa-sign-out-alt me-2"></i>Cerrar sesión</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>


    <div class="content">

        <!-- Contenido -->
        <div class="container mt-4">
            <div class="text-center mb-4">
                <h1>Bienvenido al Panel de Administración</h1>
                <p>Desde aquí podrás gestionar tus registros, usuarios y más.</p>
            </div>

            <div class="row g-4">

                <!-- PRODUCTIVIDAD -->
                <div class="col-md-4">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line text-success mb-3"></i>
                            <h5 class="card-title">Productividad</h5>
                            <p>Aquí podrás ver lo que hay en productividad.</p>
                            <button class="btn btn-outline-success w-50 card-submenu-toggle">Ver más</button>
                            <div class="card-submenu mt-3">
                                <a href="/graficos/index.html?modulo=productividad&rol=admin"
                                    class="btn btn-sm btn-success w-50">Consulta externa</a>
                                    
                                <a href="../hospitalizacion/opciones-hospitalizacion.php"
                                    class="btn btn-sm btn-success w-50">Hospitalización</a>
                                <a href="#" class="btn btn-sm btn-success w-50">Cirugía</a><br>
                                <a href="../productividad/urgencias_inicio.php"
                                    class="btn btn-sm btn-success w-50">Urgencias</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NORMATIVIDAD (antes "Manuales") -->
                <div class="col-md-4">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-body text-center">
                            <i class="fas fa-file-alt text-primary mb-3"></i>
                            <h5 class="card-title">Normatividad</h5>
                            <p>Actualmente hay <strong class="text-primary"><?php echo $totalManuales; ?></strong>
                                manuales
                                registrados.</p>
                            <a href="../normatividad/normatividad_inicio.php" class="btn btn-outline-primary w-50">Ver
                                Manuales</a>
                        </div>
                    </div>
                </div>

                <!-- ORGANIGRAMA -->
                <div class="col-md-4">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-body text-center">
                            <i class="fas fa-sitemap text-info mb-3"></i>
                            <h5 class="card-title">Organigrama</h5>
                            <p>Consulta la estructura organizacional.</p>
                            <a href="./Personal/personal.php" class="btn btn-outline-info w-50">Ver Organigrama</a>
                        </div>
                    </div>
                </div>


                <!-- VENCER -->
                <div class="col-md-4">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-body text-center">
                            <i class="fas fa-bell text-danger mb-3"></i>
                            <h5 class="card-title">VENCER</h5>
                            <p>Consulta los registros.</p>
                            <a href="../productividad/vencer.php" class="btn btn-outline-danger w-50">Ver más</a>
                        </div>
                    </div>
                </div>


                <!-- SITIOS DE INTERÉS -->
                <div class="col-md-4">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-body text-center">
                            <i class="fas fa-globe text-secondary mb-3"></i>
                            <h5 class="card-title">Sitios de Interés</h5>
                            <p>Acceso a sitios relevantes para el personal.</p>
                            <a href="../sitios-interes/index.php" class="btn btn-outline-secondary w-50">Ver Sitios</a>
                        </div>
                    </div>
                </div>

                <!-- USUARIOS REGISTRADOS -->
                <div class="col-md-4">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-body text-center">
                            <i class="fas fa-users text-dark mb-3"></i>
                            <h5 class="card-title">Control de Cuentas</h5>
                            <p>Actualmente hay <strong class="text-dark"><?php echo $totalUsuarios; ?></strong>
                                administrador
                                registrado.</p>
                            <a href="/graficos/index.html?modulo=usuarios" class="btn btn-outline-dark w-50">Ver Usuarios</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
    <br><br>


    <!-- Footer -->
    <footer>
        <p>Derechos reservados &copy; IMSS 2025</p>
    </footer>


    <script>
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


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>


    <script src="../../js/scripts.js" defer></script>
    <script src="../../js/bootstrap.bundle.min.js" defer></script>

</body>

</html>