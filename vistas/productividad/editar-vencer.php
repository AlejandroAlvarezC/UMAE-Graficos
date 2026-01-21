<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['login_error'] = "Debes iniciar sesión como administrador para acceder.";
    header('Location: ../admin/login.php');
    exit();
}

require_once '../../modelos/Vencer.php';

$id = isset($_GET['id']) ? base64_decode($_GET['id']) : null;
if (!$id) {
    header('Location: index.php?error=ID no válido');
    exit;
}

$registro = Vencer::obtenerPorId($id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../css/bootstrap.min.css" defer>
    <link rel="stylesheet" href="../../css/styles.css" defer>
    <link rel="icon" type="image/png" href="../../logo-imss.png">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <title>Editor de registros | VENCER | Administrador</title>
    <style>
        th,
        td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
        }

        .info-card {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 0;
        }

        .info-card h4 {
            margin-bottom: 15px;
        }

        .info-card .icon {
            font-size: 40px;
            color: #007bff;
            margin-right: 10px;
        }

        .card-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .card-container .card .info-card {
            flex-basis: 48%;
            margin-bottom: 0;
        }

        .info-card .btn {
            position: relative;
            border: none;
            color: white;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: none;
            margin-top: 10px;
        }

        .info-card .btn:hover {
            background-color: #006666;
        }

        .bg-verde {
            background-color: #00664d;
            color: white;
            border-radius: 12px;
            padding: 10px 20px;
            text-decoration: none;
        }

        .bg-verde:hover {
            background-color: #004d3a;
            color: white;
        }

        .acciones a {
            margin-right: 10px;
            text-decoration: none;
            background-color: #7a123a;
            color: white;
            border-radius: 12px;
            padding: 8px 15px;
            display: inline-block;
        }

        .acciones a:hover {
            background-color: #520d2a;
            color: white;
        }

        .table {
            padding: 4px 4px !important;
            font-size: small;
        }

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

        /* -----------------------------------
   FOOTER
----------------------------------- */
        footer {
            background-color: #7a125cff;
            width: 100%;
            text-align: center;
            color: white;
            font-weight: bold;
            padding-top: 10px;
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



    <div class="content">

        <div class="container mt-4">
            <div class="card p-4">
                <h4 class="text-center">Editor de registros - VENCER</h4>
                <form method="POST" action="../../controladores/vencer.php">
                    <input type="hidden" name="a" value="Editar">
                    <input type="hidden" name="id" value="<?= base64_encode($registro['id']) ?>">

                    <?php
                    // Definimos los campos tipo select con sus opciones
                    $selects = [
                        'evento' => ['Adverso', 'Centinela', 'Cuasifalla'],
                        'sexo' => ['Masculino', 'Femenino'],
                        'edad' => ['Menor a 1 año', '1 a 4', '5 a 9', '10 a 14', '15 a 19', '20 a 29', '30 a 39', '40 a 49', '50 a 59', 'Mayor de 60'],
                        'turno' => ['Matutino', 'Vespertino', 'Nocturno', 'Jornada Acumulada'],
                        'estatus' => ['Aceptada', 'Rechazada', 'Pendiente']
                    ];

                    $textareas = ['diagnostico', 'proceso', 'definicion', 'descripcion'];
                    $fechas = ['fecha_evento', 'fecha_noti'];

                    $labels = [
                        'folio' => 'Folio',
                        'evento' => 'Tipo de Evento',
                        'ini_paciente' => 'Iniciales del paciente',
                        'seguridad_social' => 'Seguridad social',
                        'edad' => 'Edad (grupo)',
                        'sexo' => 'Género',
                        'diagnostico' => 'Diagnóstico',
                        'fecha_evento' => 'Fecha del evento',
                        'fecha_noti' => 'Fecha de notificación',
                        'turno' => 'Turno',
                        'servicio' => 'Servicio',
                        'categoria' => 'Categoría',
                        'proceso' => 'Proceso',
                        'definicion' => 'Definición',
                        'descripcion' => 'Descripción',
                        'estatus' => 'Estatus',
                        'anio' => 'Año'
                    ];

                    foreach ($labels as $campo => $label) {
                        echo '<div class="mb-3">';
                        echo "<label for='$campo' class='form-label mb-1'>$label:</label>";

                        // Selects personalizados
                        if (array_key_exists($campo, $selects)) {
                            echo "<select name='$campo' id='$campo' class='form-select' required>";
                            echo "<option value=''>-- Selecciona --</option>";
                            foreach ($selects[$campo] as $opcion) {
                                $selected = ($registro[$campo] == $opcion) ? 'selected' : '';
                                echo "<option value='$opcion' $selected>$opcion</option>";
                            }
                            echo "</select>";
                        }

                        // Campos de texto largo
                        elseif (in_array($campo, $textareas)) {
                            echo "<textarea name='$campo' id='$campo' class='form-control' rows='3'>" . htmlspecialchars($registro[$campo]) . "</textarea>";
                        }

                        // Fechas
                        elseif (in_array($campo, $fechas)) {
                            echo "<input type='date' name='$campo' id='$campo' class='form-control' value='" . htmlspecialchars($registro[$campo]) . "' required>";
                        }

                        // Año como número
                        elseif ($campo === 'anio') {
                            echo "<input type='number' name='anio' id='anio' class='form-control' value='" . htmlspecialchars($registro['anio']) . "' min='2000' max='2100' required>";
                        }

                        // Otros campos de texto
                        else {
                            echo "<input type='text' name='$campo' id='$campo' class='form-control' value='" . htmlspecialchars($registro[$campo]) . "' required>";
                        }

                        echo '</div>';
                    }
                    ?>

                    <div class="d-flex justify-content-center gap-2 mt-4">
                        <a href="./vencer.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-urgencia">
                            <i class="bi bi-save"></i> Guardar Cambios
                        </button>
                        <a href="./vencer.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </form>

            </div>
        </div><br>

    </div>



    <!-- Footer -->
    <footer class="text-white text-center py-3 mt-5" style="background-color: #7a123a;">
        <div class="container">
            <p class="mb-0">© <?php echo date("Y"); ?> IMSS. Todos los derechos reservados.</p>
        </div>
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


    <!-- Links de librerías para crear gráfico en pdf -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>

    <script src="../../js/scripts.js" defer></script>
    <script src="../../js/bootstrap.bundle.min.js" defer></script>

</body>

</html>