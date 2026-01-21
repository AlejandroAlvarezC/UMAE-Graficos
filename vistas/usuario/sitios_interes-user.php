<?php
require_once '../../modelos/Sitio-interes.php';
$sitios = SitioInteres::listar();

session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <!-- Bootstrap y estilos -->
    <link rel="stylesheet" href="../../css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="icon" type="image/png" href="../../logo-imss.png" />
    <title>UMAE-48 | Sitios Interés</title>

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


        /* ----------------------------------------------
         Dropdown submenu y navegación
      ------------------------------------------------*/

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
            background-color: #00664d !important;
            color: white;
        }

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

        /* ----------------------------------------------
             Footer
          ------------------------------------------------*/

        footer {
            background-color: #00664d;
            width: 100%;
            text-align: center;
            color: white;
            font-weight: bold;
            padding: 10px 0;
        }
    </style>
</head>

<body style="background-color: aliceblue;">


    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #00664d;">

        <div class="container-fluid">

            <!-- Logo para pantallas grandes (antes del enlace "Inicio") -->
            <a href="../roles/index.php" class="me-2 d-none d-lg-block">
                <img src="../../img/umae-48.jpg" alt="Logo UMAE" height="80" class="rounded-circle">
            </a>


            <a class="navbar-brand" href="../roles/index.php">Inicio</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    <!-- PRODUCTIVIDAD -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#"
                            data-bs-toggle="dropdown">Productividad</a>
                        <ul class="dropdown-menu" data-bs-auto-close="outside">

                            <!-- Consulta externa -->
                            <li>
                                <a class="dropdown-item dropdown-submenu-toggle">Consulta externa</a>
                                <ul class="dropdown-submenu">
                                    <li><a class="dropdown-item" href="./unidadesreportan_user.php">Productividad Total</a></li>
                                    <li><a class="dropdown-item" href="./paramedicos_user.php">Paramédicos</a>
                                    </li>

                                    <!-- Especialidades -->
                                    <li>
                                        <a class="dropdown-item dropdown-submenu-toggle">Especialidades</a>
                                        <ul class="dropdown-submenu">
                                            <li><a class="dropdown-item"
                                                    href="./especialidades_user.php">Especialidades</a>
                                            </li>
                                            <li><a class="dropdown-item"
                                                    href="./EspecilidadOcas_user.php">Especialidad de
                                                    ocasión</a></li>
                                        </ul>
                                    </li>

                                </ul>
                            </li>

                            <!-- Hospitalización -->
                            <li>
                                <a class="dropdown-item dropdown-submenu-toggle">Hospitalización</a>
                                <ul class="dropdown-submenu">
                                    <li><a class="dropdown-item" href="./ingresos_user.php">Ingresos</a></li>
                                    <li><a class="dropdown-item" href="./egresos_user.php">Egresos</a></li>
                                    <li><a class="dropdown-item" href="./paciente_user.php">Días Paciente</a></li>
                                    <li><a class="dropdown-item" href="#">Días Cama</a></li>
                                </ul>
                            </li>

                            <!-- Cirugía -->
                            <li><a class="dropdown-item" href="./cirugia_user.php">Cirugía</a></li>

                            <!-- Urgencias -->
                            <li><a class="dropdown-item" href="./urgencias_user.php">Urgencias</a></li>

                        </ul>
                    </li>

                    <!-- Organigrama -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./organigrama.php">Organigrama</a>
                    </li>

                    <!-- Normatividad -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../usuario/normatividad_user.php">Normatividad</a>
                    </li>

                    <!-- Vencer -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./vencer_user.php">Vencer</a>
                    </li>

                    <!-- Sitios de interés -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./sitios_interes-user.php">Sitios de interés</a>
                    </li>
                </ul>



                <!-- Logo centrado SOLO para móviles -->
                <div class="d-block d-lg-none w-100 text-center my-2">
                    <a href="../roles/index.php">
                        <img src="../../img/umae-48.jpg" alt="Logo UMAE" height="60" class="rounded-circle">
                    </a>
                </div>



                <!-- Sesión -->
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <?php if (isset($_SESSION['admin_name'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="../admin/admin.php"
                                data-bs-toggle="dropdown">
                                Ir a Panel de <?php echo $_SESSION['admin_name']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="../admin/admin.php">
                                        <i class="bi bi-speedometer2 me-1"></i>Volver al panel</a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="../admin/logout.php">
                                        <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión</a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <div class="d-flex align-items-center gap-2">
                            <a class="btn btn-outline-light" onclick="anterior()" href="#" title="Atrás"><i class="bi bi-arrow-left"></i></a>
                            <a class="btn btn-outline-light" href="../admin/login.php">Iniciar Sesión</a>
                        </div>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>




    <div class="content">
        <div class="container-fluid px-3 mt-4"> <!-- Menos margen a los lados -->

            <h1 style="text-align: center; color:#444; font-weight:bold" class="text-center">Sitios de Interés</h1>
            <hr><br>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3">
                <?php if (!empty($sitios)): ?>
                    <?php foreach ($sitios as $sitio): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0 rounded-4">
                                <div class="position-relative">
                                    <img src="../../archivos/sitios_interes/<?= htmlspecialchars($sitio[3]) ?>"
                                        class="card-img-top rounded-top-4 p-2 bg-white img-click"
                                        alt="<?= htmlspecialchars($sitio[1]) ?>"
                                        style="height: 200px; object-fit: contain; cursor: pointer;"
                                        onclick="mostrarTexto(this)">
                                </div>
                                <div class="card-body d-flex flex-column p-2">
                                    <h5 class="card-text text-dark fw-bold text-center"><?= htmlspecialchars($sitio[1]) ?></h5>
                                    <p class="card-text text-muted mb-3" style="display: none;"><?= htmlspecialchars($sitio[2]) ?></p>
                                    <a href="<?= htmlspecialchars($sitio[4]) ?>" target="_blank"
                                        class="btn btn-outline-primary mt-auto mx-auto"
                                        style="width: 80%;">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Visitar sitio
                                    </a>
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

    <script>
        function mostrarTexto(img) {
            const texto = img.closest('.card').querySelector('p.card-text');
            if (texto.style.display === 'none') {
                texto.style.display = 'block';
            } else {
                texto.style.display = 'none';
            }
        }
    </script>

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