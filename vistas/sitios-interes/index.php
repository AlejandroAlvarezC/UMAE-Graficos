<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['login_error'] = "Debes iniciar sesión como administrador para acceder a esa sección.";
    header('Location: ../admin/login.php');  // O la ruta correcta de tu login
    exit();
}

require_once '../../modelos/Sitio-interes.php';
$sitios = SitioInteres::listar();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Sitios de Interés | Administrador</title>
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
            background-color: #f0f2f5;
        }

        .card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            background-color: #ffffff;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
        }

        .card-img-top {
            height: 200px;
            object-fit: contain;
            background-color: #fff;
            padding: 15px;
        }

        .card-title {
            font-weight: 600;
            font-size: 1.2rem;
            color: #1d1d1f;
        }

        .card-text {
            color: #555;
            font-size: 0.95rem;
            margin-bottom: 10px;
        }


        /* -----------------------
   Botones personalizados
------------------------ */
        .btn {
            font-size: 0.9rem;
            border-radius: 8px;
            padding: 6px 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        /* Botón: Visitar sitio */
        .btn-outline-primary {
            color: #003f88;
            border-color: #003f88;
            background-color: transparent;
        }

        .btn-outline-primary:hover {
            background-color: #003f88;
            color: white;
        }

        /* Botón: Editar */
        .btn-outline-warning {
            color: #00796b;
            border-color: #00796b;
            background-color: transparent;
        }

        .btn-outline-warning:hover {
            background-color: #00796b;
            color: white;
        }

        .btn-outline-warning2 {
            color: white;
            background-color: #00796b;
        }

        .btn-outline-warning2:hover {
            background-color: #00685cff;
            color: white;
            border-color: #00796b;
        }

        /* Botón: Eliminar */
        .btn-outline-danger {
            color: #9c1c28;
            border-color: #9c1c28;
            background-color: transparent;
        }

        .btn-outline-danger:hover {
            background-color: #9c1c28;
            color: white;
        }

        /* Botón: Agregar nuevo */
        .btn-success {
            background-color: #2e7d32;
            border-color: #2e7d32;
            color: white;
        }

        .btn-success:hover {
            background-color: #1b5e20;
            border-color: #1b5e20;
        }

        /* Link en nav */
        .nav-link:hover {
            color: #f7c74a !important;
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

        /* Footer */
        footer {
            background-color: #7a123a;
            color: white;
            padding: 10px 0;
            text-align: center;
            font-weight: bold;
        }

        .section-header {
            background-color: #dee2e6;
            border-left: 5px solid rgba(58, 58, 58, 1);
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .section-header h1 {
            color: #343a40;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .section-header small {
            color: #6c757d;
            font-weight: 500;
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
                            <li><a class="dropdown-item" href="../cirugia/cirugia_inicio.php">Cirugías</a></li>

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
                    <a class="btn btn-outline-light" href="../usuario/sitios_interes-user.php">Vista de usuario</a>

                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../usuario/sitios_interes-user.php"><i class="fas fa-eye me-2"></i>Ver
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

    <div class="content">


        <div class="container mt-4">
            <div class="section-header">
                <h1 class="h2 mb-0">Sitios de Interés</h1>
                <small class="text-muted">Sitios para Visitar.</small>
            </div>
            <a href="./ingresar-sitios.php" class="btn  btn-outline-warning2 mb-4">Agregar Nuevo Sitio</a>

            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php if (!empty($sitios)): ?>
                    <?php foreach ($sitios as $sitio): ?>
                        <div class="col">
                            <div class="card h-100 shadow-lg border-0 rounded-4">
                                <div class="position-relative">
                                    <img src="../../archivos/sitios_interes/<?= htmlspecialchars($sitio[3]) ?>"
                                        class="card-img-top rounded-top-4 p-3 bg-white"
                                        alt="<?= htmlspecialchars($sitio[1]) ?>"
                                        style="height: 200px; object-fit: contain;" />
                                </div>
                                <div class="card-body d-flex flex-column p-4">
                                    <h5 class="card-title text-dark fw-bold"><?= htmlspecialchars($sitio[1]) ?></h5>
                                    <p class="card-text text-muted mb-3"><?= htmlspecialchars($sitio[2]) ?></p>
                                    <a href="<?= htmlspecialchars($sitio[4]) ?>" target="_blank" class="btn btn-outline-primary mt-auto">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Visitar sitio
                                    </a>
                                    <div class="d-flex justify-content-between mt-3">
                                        <a href="./editar-sitios.php?id=<?= base64_encode($sitio[0]) ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                            <i class="fa fa-pencil-alt"></i> Editar
                                        </a>
                                        <a href="../../controladores/SitiosInteres.php?a=elim&id=<?= base64_encode($sitio[0]) ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('¿Estás seguro de eliminar este sitio?');"
                                            title="Eliminar">
                                            <i class="fa fa-trash-alt"></i> Eliminar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center">No hay sitios de interés registrados.</p>
                <?php endif; ?>
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