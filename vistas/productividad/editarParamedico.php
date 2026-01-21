<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['login_error'] = "Debes iniciar sesión como administrador para acceder a esa sección.";
    header('Location: ../admin/login.php');  // O la ruta correcta de tu login
    exit();
}

require_once '../../modelos/paramedicos.php';

$id = isset($_GET['id']) ? base64_decode($_GET['id']) : null;

if (!$id) {
    header('Location: paramedicos.php?error=ID no válido');
    exit;
}

$esp = Paramedicos::obtenerPorId($id);
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
    <title>Editor de registros | Paramédicos | Administrador</title>
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
            padding: 4px px !important;
            font-size: small;
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
                                <li><a class="dropdown-item" href="./unidades_que_reportan.php">Productividad total</a></li>
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
                                <li><a class="dropdown-item" href="#">Ingresos</a></li>
                                <li><a class="dropdown-item" href="#">Egresos</a></li>
                                <li><a class="dropdown-item" href="#">Días Paciente</a></li>
                                <li><a class="dropdown-item" href="#">Días Coma</a></li>
                            </ul>
                        </li>

                        <!-- Cirugía -->
                        <li>
                            <a class="dropdown-item dropdown-submenu-toggle">Cirugía</a>
                            <ul class="dropdown-submenu">
                                <li><a class="dropdown-item" href="#">Opción 1</a></li>
                                <li><a class="dropdown-item" href="#">Opción 2</a></li>
                            </ul>
                        </li>

                        <!-- Urgencias -->
                        <li><a class="dropdown-item" href="./urgencias_inicio.php">Urgencias</a></li>

                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white" href="#">Organigrama</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white" href="#">Vencer</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white" href="../normatividad/normatividad_inicio.php">Normatividad</a>
                </li>

                <li class="nav-item"><a class="nav-link text-white" href="../admin/usuariosAdmin.php">Usuario</a>
                </li>
            </ul>

            <!-- Botones a la derecha -->
            <div class="d-flex align-items-center gap-2">
                <a class="btn btn-outline-light" onclick="anterior()" href="#" title="Atrás"><i class="bi bi-arrow-left"
                            ></i></a>
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../usuario/paramedicos_user.php"><i class="fas fa-eye me-2"></i>Ver
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


    <div class="container mt-4">
        <div class="card p-4">
            <h4 class="text-center">Editor de registros - Paramédicos</h4>
            <form method="POST" action="../../controladores/paramedicos.php">
                <input type="hidden" name="a" value="Editar">
                <input type="hidden" name="id" value="<?= base64_encode($esp[0]) ?>">

                <div class="mb-3">
                    <label class="form-label mb-1">Clave:</label>
                    <input type="text" name="clave" class="form-control" value="<?= htmlspecialchars($esp[1]) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label mb-1">Especialidad:</label>
                    <input type="text" name="especialidad" class="form-control" value="<?= htmlspecialchars($esp[2]) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label mb-1">División:</label>
                    <input type="text" name="division" class="form-control" value="<?= htmlspecialchars($esp[3]) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label mb-1">Año:</label>
                    <input type="number" name="anio" class="form-control" value="<?= htmlspecialchars($esp[16]) ?>" required>
                </div>

                <h5 class="mt-4">Datos por mes</h5>
                <div class="row">
                    <?php
                    $meses = [
                        'enero',
                        'febrero',
                        'marzo',
                        'abril',
                        'mayo',
                        'junio',
                        'julio',
                        'agosto',
                        'septiembre',
                        'octubre',
                        'noviembre',
                        'diciembre'
                    ];
                    $i = 4; // Asumiendo que $esp[4] a $esp[15] son los meses
                    foreach ($meses as $mes) {
                    ?>
                        <div class="col-6 col-md-3 mb-2">
                            <label class="form-label mb-1"><?= ucfirst($mes) ?>:</label>
                            <input type="number" name="<?= $mes ?>" class="form-control"
                                value="<?= htmlspecialchars($esp[$i++]) ?>" min="0">
                        </div>
                    <?php
                    }
                    ?>
                </div>

                <div class="d-flex justify-content-center gap-2 mt-4">
                    <a href="./paramedicos.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Guardar Cambios
                    </button>
                    <a href="./paramedicos.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div><br>


    <!-- Footer -->
    <footer class="text-white text-center py-3 mt-5" style="background-color: #7a123a;">
        <div class="container">
            <p class="mb-0">© <?php echo date("Y"); ?> IMSS. Todos los derechos reservados.</p>
        </div>
    </footer>

<!-- Links de librerías para crear gráfico en pdf -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>

<script src="../../js/scripts.js" defer></script>
<script src="../../js/bootstrap.bundle.min.js" defer></script>

</body>

</html>