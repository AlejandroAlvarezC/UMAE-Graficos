<?php

require_once '../../modelos/conexion.php';

session_start();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../../logo-imss.png">
    <!-- Links de boostrap -->
    <title>IMSS - UMAE 48</title>

    <style>
        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .custom-hover {
            transition: all 0.3s ease-in-out;
            border: 1px solid transparent;
        }

        .custom-hover:hover {
            background-color: rgba(0, 102, 77, 0.9) !important;
            color: white !important;
            border-color: #00664d;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2) !important;
            transform: translateY(-4px);
        }

        .custom-hover:hover h6,
        .custom-hover2:hover h5 {
            color: white !important;
        }

        .navbar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }

        .carousel-inner img {
            max-height: 600px;
            width: 100%;
            object-fit: cover;
            border-radius: 0.75rem;
            /* .rounded-4 */
        }

        .section-title {
            border-bottom: 3px solid #00664d;
            display: inline-block;
            padding-bottom: 4px;
            margin-bottom: 1rem;
        }

        .custom-hover2 {
            transition: all 0.3s ease;
        }

        .custom-hover2:hover {
            background-color: rgba(0, 123, 255, 0.62) !important;
            /* Azul Bootstrap */
            color: white !important;
            transform: scale(1.03);
        }

        .custom-hover2:hover img {
            filter: brightness(0) invert(1);
            /* Cambia color del ícono a blanco */
        }

        .active-card {
            background-color: #007bff !important;
            color: white !important;
            transform: scale(1.03);
            border: 2px solid #0056b3;
        }

        .active-card h5,
        .active-card h6 {
            color: white !important;
        }

        .active-card img {
            filter: brightness(0) invert(1);
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
    </style>

</head>

<body>

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
                        <a  class="nav-link text-white" href="/graficos/index.html?modulo=productividad&rol=<?php echo $_SESSION['rol']; ?>">
                            Productividad
                        </a>

                    <!-- Indicadores -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/graficos/index.html?modulo=indicadores&rol=<?php echo $_SESSION['rol']; ?>">
                            Indicadores
                        </a>
                    </li>

                    <!-- Normatividad -->

                    <!-- Vencer -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/graficos/index.html?modulo=vencer">Vencer</a>
                    </li>

                    <li class="nav-item">
                       <a class="nav-link text-white" href="/IFU/index.php?rol=<?php echo $_SESSION['rol']; ?>">  IFU </a>
                    </li>

                    <!-- Sitios de interés -->
                </ul>


                <!-- Logo centrado SOLO para móviles -->
                <div class="d-block d-lg-none w-100 text-center my-2">
                    <a href="../roles/index.php">
                        <img src="../../img/umae-48.jpg" alt="Logo UMAE" height="60" class="rounded-circle">
                    </a>
                </div>


                
               <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
    
                    <?php if (isset($_SESSION['admin_name']) && isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" data-bs-toggle="dropdown">
                                Ir a Panel de <?php echo $_SESSION['admin_name']; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="../admin/admin.php">
                                        <i class="bi bi-speedometer2 me-1"></i>Volver al panel
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="../admin/logout.php">
                                        <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
                                    </a>
                                </li>
                            </ul>
                        </li>

                    <?php elseif (isset($_SESSION['admin_name'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i> <?php echo $_SESSION['admin_name']; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item text-danger" href="../admin/logout.php">
                                        <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
                                    </a>
                                </li>
                            </ul>
                        </li>

                    <?php else: ?>
                        <div class="d-flex align-items-center gap-2">
                            <a class="btn btn-outline-light" href="../admin/login.php">Iniciar Sesión</a>
                        </div>
                    <?php endif; ?>

                </ul>
            </div>
        </div>
    </nav>


    <div style="background-color:rgba(232, 220, 202, 0.20);">
        <div class="pt-3 pb-2">


            <div id="carouselExampleInterval" class="carousel slide mx-auto shadow-lg rounded-4 overflow-hidden" style="max-width: 90%;" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active" data-bs-interval="5000">
                        <img src="../../img/imss7.jpg" class="d-block w-100 img-fluid" alt="Imagen 1">
                    </div>
                    <div class="carousel-item" data-bs-interval="5000">
                        <img src="../../img/imss10.jpg" class="d-block w-100 img-fluid" alt="Imagen 2">
                    </div>
                    <div class="carousel-item" data-bs-interval="5000">
                        <img src="../../img/imss6.webp" class="d-block w-100 img-fluid" alt="Imagen 3">
                    </div>
                    <div class="carousel-item" data-bs-interval="5000">
                        <img src="../../img/imss8.jpg" class="d-block w-100 img-fluid" alt="Imagen 4">
                    </div>
                    <div class="carousel-item" data-bs-interval="5000">
                        <img src="../../img/imss9.webp" class="d-block w-100 img-fluid" alt="Imagen 5">
                    </div>
                </div>

                <!-- Controles -->
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleInterval" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleInterval" data-bs-slide="next">
                    <span class="carousel-control-next-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                    <span class="visually-hidden">Siguiente</span>
                </button>

                <!-- Indicadores -->
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#carouselExampleInterval" data-bs-slide-to="0" class="active bg-dark" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#carouselExampleInterval" data-bs-slide-to="1" class="bg-dark" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#carouselExampleInterval" data-bs-slide-to="2" class="bg-dark" aria-label="Slide 3"></button>
                    <button type="button" data-bs-target="#carouselExampleInterval" data-bs-slide-to="3" class="bg-dark" aria-label="Slide 4"></button>
                    <button type="button" data-bs-target="#carouselExampleInterval" data-bs-slide-to="4" class="bg-dark" aria-label="Slide 5"></button>
                </div>
            </div>
            <br>



            <div class="container my-4">
                <div class="row g-4">
                    <div class="col-md-4">
                        <a href="#"
                            class="d-block text-center p-2 bg-white rounded-4 shadow-sm h-100 text-decoration-none custom-hover">
                            <img src="../../img/libro.png" class="mb-1 img-fluid" style="max-height: 120px;" alt="Imagen">
                            <h6 class="text-dark fw-bold">Catálogo de Documentos</h6>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="#"
                            class="d-block text-center p-2 bg-white rounded-4 shadow-sm h-100 text-decoration-none custom-hover">
                            <img src="../../img/lupa.png" class="mb-1 img-fluid" style="max-height: 120px;" alt="Imagen">
                            <h6 class="text-dark fw-bold">Dx Situacional 2019</h6>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="#"
                            class="d-block text-center p-2 bg-white rounded-4 shadow-sm h-100 text-decoration-none custom-hover">
                            <img src="../../img/medicina.jpg" class="mb-4 img-fluid" style="max-height: 100px;" alt="Imagen">
                            <h6 class="text-dark fw-bold">Dx de Salud 2018</h6>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="#"
                            class="d-block text-center p-2 bg-white rounded-4 shadow-sm h-100 text-decoration-none custom-hover">
                            <img src="../../img/estrategico.png" class="mb-1 img-fluid" style="max-height: 120px;" alt="Imagen">
                            <h6 class="text-dark fw-bold">Plan Estratégico 2019-2022</h6>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="#"
                            class="d-block text-center p-2 bg-white rounded-4 shadow-sm h-100 text-decoration-none custom-hover">
                            <img src="../../img/mama.png" class="mb-1 img-fluid" style="max-height: 120px;" alt="Imagen">
                            <h6 class="text-dark fw-bold">Normativa Auditoría Cáncer de Mama</h6>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="#"
                            class="d-block text-center p-2 bg-white rounded-4 shadow-sm h-100 text-decoration-none custom-hover">
                            <img src="../../img/documentos.png" class="mb-1 img-fluid" style="max-height: 120px;" alt="Imagen">
                            <h6 class="text-dark fw-bold">Administración de Documentos</h6>
                        </a>
                    </div>
                </div>
            </div><br>

        </div>

    </div>


    <!-- Footer -->
    <footer class="text-white text-center py-3 mt-5" style="background-color: #00664d;">
        <div class="container">
            <p class="mb-0">© <?php echo date("Y"); ?> IMSS. Todos los derechos reservados.</p>
        </div>
    </footer>


    <script src="../../js/bootstrap.bundle.min.js" defer></script>
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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</body>

</html>