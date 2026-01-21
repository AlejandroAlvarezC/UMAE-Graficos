<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['login_error'] = "Debes iniciar sesión como administrador para acceder a esa sección.";
    header('Location: ../admin/login.php');  // O la ruta correcta de tu login
    exit();
}

require_once '../../modelos/dias_paciente.php';

require_once '../../modelos/ingreso.php';

require_once '../../modelos/egreso.php';


// Contar Días Paciente
$totalPaciente = Paciente::contar();

// Contar Ingresos
$totalIngreso = Ingreso::contar();

// Contar Egresos
$totalEgreso = Egreso::contar();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Opciones Hospitalización - IMSS</title>

    <!-- Bootstrap y estilos -->
    <link rel="stylesheet" href="../../css/bootstrap.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <script src="../../iconos/fontawesome-free-6.7.2-web/js/all.js" crossorigin="anonymous"></script>
    <script src="../../js/jquery-3.7.1.js" crossorigin="anonymous"></script>
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
            background-color: #eaeaea;
        }

        .content {
            flex: 1;
            padding: 15px;
            background-color: #eaeaea;
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

        .card-body i {
            font-size: 40px;
        }

        .card .btn {
            margin-top: 10px;
            padding: 10px 20px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
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

        .dropdown-item.dropdown-submenu-toggle.active,
        .dropdown-item.dropdown-submenu-toggle:hover {
            background-color: rgb(202, 155, 26) !important;
            color: white;
        }

        /* ==== Tarjetas elegantes y configurables ==== */
        .custom-card {
            background-color: #ffffff;
            color: #343a40;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            padding: 25px;
            min-height: 340px;
        }

        .custom-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 30px rgba(0, 0, 0, 0.15);
            background-color: #f8f9fa;
        }

        /* Refinar iconos */
        .card-body i {
            font-size: 64px;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }

        .card-body i:hover {
            transform: scale(1.15);
        }

        /* Sombra más sutil en iconos */
        .icon-style {
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.25));
        }

        /* Botón: Días Paciente (Azul) */
        .btn-dias-paciente {
            background: linear-gradient(135deg, #4a90e2, #357ab8);
            color: white;
            border: none;
        }

        .btn-dias-paciente:hover {
            background: linear-gradient(135deg, #357ab8, #2d639a);
            box-shadow: 0 6px 16px rgba(74, 144, 226, 0.4);
        }

        /* Botón: Días Cama (Verde oscuro) */
        .btn-dias-cama {
            background: linear-gradient(135deg, rgb(34, 117, 112), rgb(26, 89, 85));
            color: white;
        }

        .btn-dias-cama:hover {
            background: linear-gradient(135deg, rgb(26, 89, 85), rgb(20, 70, 67));
            box-shadow: 0 6px 16px rgba(34, 117, 112, 0.4);
        }

        /* Botón: Ingresos (Amarillo oscuro) */
        .btn-ingresos {
            background: linear-gradient(135deg, #d4a017, #b88b14);
            color: white;
        }

        .btn-ingresos:hover {
            background: linear-gradient(135deg, #b88b14, #9a7511);
            box-shadow: 0 6px 16px rgba(212, 160, 23, 0.4);
        }

        /* Botón: Egresos (Naranja oscuro) */
        .btn-egresos {
            background: linear-gradient(135deg, #e7843f, #c46d30);
            color: white;
        }

        .btn-egresos:hover {
            background: linear-gradient(135deg, #c46d30, #a85c26);
            box-shadow: 0 6px 16px rgba(231, 132, 63, 0.4);
        }

        /* ==== Contador con colores configurables ==== */
        .card-body p strong.text-success,
        .card-body p strong.text-primary,
        .card-body p strong.text-danger,
        .card-body p strong.text-info {
            font-weight: 700;
            font-size: 1.2rem;
        }

        /* Opcional: transiciones suaves para cambios de color */
        .card-body p strong {
            transition: color 0.3s ease;
        }

        /* Iconos con sombra para destacar */
        .icon-style {
            filter: drop-shadow(0 2px 2px rgba(0, 0, 0, 0.3));
        }
    </style>
</head>

<body>

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

                    <!-- Normatividad -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../normatividad/normatividad_inicio.php">Normatividad</a>
                    </li>

                    <li class="nav-item"><a class="nav-link text-white" href="../admin/usuariosAdmin.php">Usuario</a></li>
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


        <div class="content d-flex justify-content-center" style="padding: 40px 0; background-color: #eaeaea;">

            <div class="container mt-4">

                <div class="text-center mb-5">
                    <h1 class="fw-bold">Sección de Hospitalización</h1>
                    <p class="text-muted">Aquí puedes seleccionar la opción de tu preferencia.</p>
                </div>

                <div class="row g-4 justify-content-center">


                    <!-- Días Paciente -->
                    <div class="col-md-6 col-lg-6">
                        <div class="card custom-card h-100 border-0">
                            <div class="card-body text-center">
                                <a href="../hospitalizacion/pacientes_inicio.php">
                                    <i class="fas fa-bed fa-5x icon-style" style="color: #4a90e2;"></i>
                                </a>
                                <h5 class="card-title">Días paciente</h5>
                                <p>Actualmente hay <span class="card-counter" style="color:  #4a90e2;"><?= $totalPaciente; ?></span> registros.</p>
                                <a href="../hospitalizacion/pacientes_inicio.php" class="btn btn-dias-paciente w-50">Ver</a>
                            </div>
                        </div>
                    </div>

                    <!-- Días Cama -->
                    <div class="col-md-6 col-lg-6">
                        <div class="card custom-card h-100 border-0">
                            <div class="card-body text-center">
                                <a href="../productividad/Especialidad_Ocasion.php">
                                    <i class="fas fa-procedures fa-5x icon-style" style="color: rgb(34, 117, 112);"></i>
                                </a>
                                <h5 class="card-title">Días cama</h5>
                                <p>Actualmente hay <span class="card-counter" style="color: rgb(34, 117, 112);"></span> registros.</p>
                                <a href="#" class="btn btn-dias-cama w-50">Ver</a>
                            </div>
                        </div>
                    </div>

                    <!-- Ingresos -->
                    <div class="col-md-6 col-lg-6">
                        <div class="card custom-card h-100 border-0">
                            <div class="card-body text-center">
                                <a href="../hospitalizacion/ingresos_inicio.php">
                                    <i class="fas fa-sign-in-alt fa-5x icon-style" style="color: #d4a017;"></i>
                                </a>
                                <h5 class="card-title">Ingresos</h5>
                                <p>Actualmente hay <span class="card-counter" style="color:  #d4a017;"><?= $totalIngreso; ?></span> registros.</p>
                                <a href="../hospitalizacion/ingresos_inicio.php" class="btn btn-ingresos w-50">Ver</a>
                            </div>
                        </div>
                    </div>

                    <!-- Egresos -->
                    <div class="col-md-6 col-lg-6">
                        <div class="card custom-card h-100 border-0">
                            <div class="card-body text-center">
                                <a href="../hospitalizacion/egresos_inicio.php">
                                    <i class="fas fa-sign-out-alt fa-5x icon-style" style="color: #e7843f;"></i>
                                </a>
                                <h5 class="card-title">Egresos</h5>
                                <p>Actualmente hay <span class="card-counter" style="color: #e7843f;"><?= $totalEgreso; ?></span> registros.</p>
                                <a href="../hospitalizacion/egresos_inicio.php" class="btn btn-egresos w-50">Ver</a>
                            </div>
                        </div>
                    </div>



                </div>

            </div>
        </div>


    </div>


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