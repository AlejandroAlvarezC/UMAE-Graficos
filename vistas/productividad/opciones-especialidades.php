<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['login_error'] = "Debes iniciar sesión como administrador para acceder a esa sección.";
    header('Location: ../admin/login.php');  // O la ruta correcta de tu login
    exit();
}

require_once '../../modelos/Especialidad_Ocasion.php';

require_once '../../modelos/paramedicos.php';

// Contar Especialidad de Ocasión
$totalEspecialidad_Ocasion = Especialidad_Ocasion::contarEspecialidadOcasion();


// Contar Especialidad de Ocasión
$totalParamedicos = Paramedicos::contar();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Panel de Administración | Administrador</title>

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
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            padding: 30px;
            min-height: 320px;
        }

        .custom-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 30px rgba(0, 0, 0, 0.12);
            background-color: rgb(201, 201, 201);
        }

        /* ==== Iconos configurables ==== */
        .card-body i {
            font-size: 60px;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }

        .card-body i:hover {
            transform: scale(1.1);
        }

        .icon-style {
            filter: drop-shadow(0 3px 4px rgba(0, 0, 0, 0.2));
        }

        /* Botón: Especialidad de Ocasión */
        .btn-especialidad-ocasion {
            background: linear-gradient(135deg, #298362ff, #343a40);
            color: white;
        }

        .btn-especialidad-ocasion:hover {
            background: linear-gradient(135deg, #327d62ff, #212529);
            box-shadow: 0 6px 16px rgba(48, 110, 59, 0.4);
        }

        /* Botón: Especialidad Total */
        .btn-especialidad-total {
            background: linear-gradient(135deg, rgba(41, 141, 134, 1), rgba(24, 85, 81, 1));
            color: white;
        }

        .btn-especialidad-total:hover {
            background: linear-gradient(135deg, rgba(36, 118, 113rgba(18, 66, 63, 1)0, 67));
            box-shadow: 0 6px 16px rgba(34, 117, 112, 0.4);
        }

        /* Botón: Unidades que Reportan */
        .btn-unidades-reportan {
            background: linear-gradient(135deg, #293390ff, #1f6e6a);
            color: white;
        }

        .btn-unidades-reportan:hover {
            background: linear-gradient(135deg, #2a1f6eff, #16514e);
            box-shadow: 0 6px 16px rgba(41, 57, 144, 0.4);
        }

        /* Botón: Paramédicos */
        .btn-paramedicos {
            background: linear-gradient(135deg, rgba(13, 142, 19, 1), rgba(7, 84, 10, 1));
            color: white;
        }

        .btn-paramedicos:hover {
            background: linear-gradient(135deg, rgba(12, 138, 19, 1), rgba(7, 87, 12, 1));
            box-shadow: 0 6px 16px rgba(13, 144, 20, 0.4);
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

                        <a class="nav-link dropdown-toggle text-white" href="#"
                            data-bs-toggle="dropdown">Productividad</a>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">Organigrama</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">Vencer</a>
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
                            <li><a class="dropdown-item" href="../roles/index.php"><i class="fas fa-eye me-2"></i>Ver
                                    como Usuario</a></li>
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