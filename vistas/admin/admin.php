<?php
// Desactivar errores en producción, activar solo para pruebas
ini_set('display_errors', 0);
error_reporting(0);

// Mantenemos la verificación de sesión por seguridad
include('verificar_sesion.php');

// Verificación de rango: Solo permite entrada si el rol es 'admin'
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../roles/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Panel de Administración | Sistema Hospitalario</title>

    <link rel="stylesheet" href="../../css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="icon" type="image/png" href="../../logo-imss.png" />

    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }

        .navbar-custom {
            background-color: #7a123a;
        }

        .content {
            flex: 1;
            padding: 30px 15px;
        }

        .card {
            border: none;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-body i {
            font-size: 45px;
            margin-bottom: 15px;
        }

        .btn-custom {
            background-color: #7a123a;
            color: white;
            font-weight: bold;
        }

        .btn-custom:hover {
            background-color: #5a0d2b;
            color: white;
        }

        footer {
            background-color: #7a123a;
            color: white;
            text-align: center;
            font-weight: bold;
            padding: 15px 0;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">UMAE 48 - ADMIN</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link text-white fw-medium" href="admin.php">INICIO</a></li>
                    <li class="nav-item"><a class="nav-link text-white fw-medium" href="/">VENCER</a></li>
                    <li class="nav-item"><a class="nav-link text-white fw-medium" href="../normatividad/normatividad_inicio.php">NORMATIVIDAD</a></li>
                    <li class="nav-item"><a class="nav-link text-white fw-medium" href="usuariosAdmin.php">USUARIOS</a></li>
                </ul>
                
                <div class="d-flex align-items-center gap-3">
                    <span class="text-white small d-none d-md-block">Bienvenido, <strong>Admin</strong></span>
                    <a href="logout.php" class="btn btn-sm btn-outline-light">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="content">
        <div class="container">
            <div class="text-center mb-5">
                <h1 class="fw-black" style="color: #7a123a;">Panel de Control</h1>
                <p class="text-muted">Gestión de indicadores y administración del sistema</p>
            </div>

            <div class="row g-4 justify-content-center">

                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-chart-bar text-success"></i>
                            <h5 class="fw-bold">Productividad</h5>
                            <p class="small text-muted">Visualización de tableros analíticos y gráficas de atención.</p>
                            <a href="/graficos/index.html?modulo=productividad&rol=admin" class="btn btn-outline-success w-100 mt-2">Ir a Tableros</a>
                        </div>
                    </div>
                </div>

                <style>
                    .text-esmeralda {
                        color: #0d9488 !important; /* Verde azulado */
                    }
                    .btn-outline-esmeralda {
                        color: #0d9488;
                        border-color: #0d9488;
                        background-color: transparent;
                        transition: all 0.3s ease;
                    }
                    .btn-outline-esmeralda:hover {
                        background-color: #0d9488;
                        color: #ffffff;
                    }
                </style>

                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-chart-line fa-2x text-esmeralda mb-3"></i>
                            <h5 class="fw-bold">Indicadores</h5>
                            <p class="small text-muted">HOSP, Semanales, Mensual y Mensual Acumulado.</p>
                                <a href="/graficos/index.html?modulo=indicadores&rol=admin" class="btn btn-outline-esmeralda w-100 mt-2">
                                    Ir a Tableros
                                </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-shield-virus text-danger"></i>
                            <h5 class="fw-bold">VENCER</h5>
                            <p class="small text-muted">Monitoreo de eventos críticos y registros de seguridad del paciente.</p>
                            <a href="../productividad/vencer.php" class="btn btn-outline-danger w-100 mt-2">Registros Vencer</a>
                        </div>
                    </div>
                </div>

                <style>
                    .text-indigo {
                        color: #4f46e5 !important; /* Azul Índigo */
                    }
                    .btn-outline-indigo {
                        color: #4f46e5;
                        border-color: #4f46e5;
                        background-color: transparent;
                        transition: all 0.3s ease;
                    }
                    .btn-outline-indigo:hover {
                        background-color: #4f46e5;
                        color: #ffffff;
                    }
                </style>

                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-boxes fa-2x text-indigo mb-3"></i>
                            <h5 class="fw-bold">Inventario IFU</h5>
                            <p class="small text-muted">Gestión, control de existencias y seguimiento de entradas/salidas.</p>
                        <a href="/IFU/index.php?rol=<?php echo $_SESSION['rol']; ?>"> Ir a Inventario IFU</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-file-medical text-primary"></i>
                            <h5 class="fw-bold">Normatividad</h5>
                            <p class="small text-muted">Consulta de manuales, lineamientos y procedimientos vigentes.</p>
                            <a href="../normatividad/normatividad_inicio.php" class="btn btn-outline-primary w-100 mt-2">Ver Manuales</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-user-shield text-dark"></i>
                            <h5 class="fw-bold">Usuarios</h5>
                            <p class="small text-muted">Gestión de cuentas, perfiles de acceso y permisos del personal.</p>
                            <a href="/graficos/index.html?modulo=usuarios" class="btn btn-outline-dark w-100 mt-2">Administrar Cuentas</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-sitemap text-info"></i>
                            <h5 class="fw-bold">Estructura</h5>
                            <p class="small text-muted">Consulta del organigrama y jerarquía del personal de la unidad.</p>
                            <a href="./Personal/personal.php" class="btn btn-outline-info w-100 mt-2">Ver Organigrama</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <footer>
        <p class="m-0 small">UMAE 48 - Coordinación de Información Médica &copy; 2026</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>