<?php
session_start();

// 1. SOLO SEGURIDAD (Nada de modelos, nada de bases de datos aquí)
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['login_error'] = "Debes iniciar sesión como administrador.";
    header('Location: ../admin/login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VENCER | Administrador</title>
  <link rel="icon" type="image/png" href="../../logo-imss.png">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="../../css/bootstrap.min.css">
  <link rel="stylesheet" href="../../css/styles.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../../css/vencer.css">
</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container-fluid">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="menuPrincipal">
        <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link text-white" href="../admin/admin.php">INICIO</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="./vencer.php">Vencer</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="content">
    <div class="container py-4">
      
      <div class="section-header">
        <h1 class="h2">VENCER</h1>
        <small class="text-muted">Listado de registros.</small>
      </div>

      <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <div class="card shadow-sm mb-4 p-3 border-0">
        <div class="row gy-2">
          <div class="col-md-auto d-flex gap-2 flex-wrap align-items-center">
            <a href="./ingresar-vencer.php" class="btn btn-urgencia shadow-sm">➕ Agregar manual</a>
            <a href="./agregar-vencer.php" class="btn btn-urgencia shadow-sm">📥 Cargar archivo</a>
            <a href="./actualizar-vencer.php" class="btn btn-urgencia shadow-sm" onclick="return confirm('¿Seguro que deseas actualizar?')">🔁 Actualizar datos</a>
          </div>

          <div class="col-md d-flex justify-content-md-end align-items-center gap-2 flex-wrap">
              <a href="/graficos/index.html?modulo=vencer" 
                id="btnNuevoDashboard" 
                class="btn btn-urgencia shadow-sm d-flex align-items-center">
                  📊 Ver Dashboard
              </a>
          </div>

        </div>
      </div>

      <div id="filtrosContainer" class="card card-body shadow-sm mb-4 border-0" style="display: none;">
        <div class="row gy-3">
          <h5 class="text-secondary">Filtros Avanzados</h5>
          <hr>
          <?php 
            $campos = ['Folio', 'Evento', 'Iniciales', 'NSS', 'Edad', 'Sexo', 'Diagnostico', 'FechaEvento', 'FechaNotificacion', 'Turno', 'Servicio', 'Categoria', 'Proceso', 'Definicion', 'Descripcion', 'Estatus', 'Anio'];
            foreach($campos as $c): 
          ?>
            <div class="col-md-3">
                <label for="filter<?= $c ?>" class="form-label small fw-bold text-muted"><?= $c ?>:</label>
                <select id="filter<?= $c ?>" name="filter<?= $c ?>" class="form-select select-personalizado form-select-sm" multiple></select>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="card shadow-sm mt-4 border-0">
          <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 text-secondary"><i class="bi bi-table me-2"></i>Registros Actuales</h5>
          </div>
          <div class="card-body p-0">
            
            <div id="loaderTabla" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
                <h5 class="mt-3 text-muted">Cargando registros...</h5>
            </div>

            <div class="table-responsive" id="contenedorTabla" style="display: none;">
              <table class="table table-hover table-striped mb-0 text-center align-middle" id="tabla-vencer" style="width:100%">
                <thead class="table-light">
                  <tr>
                    <th>Folio</th><th>Evento</th><th>Iniciales</th><th>NSS</th><th>Edad</th><th>Sexo</th>
                    <th>Diagnostico</th><th>Fecha Evento</th><th>Fecha Notif.</th><th>Turno</th>
                    <th>Servicio</th><th>Categoria</th><th>Proceso</th><th>Definicion</th><th>Descripcion</th>
                    <th>Estatus</th><th>Año</th><th>Acciones</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>

          </div>
      </div>

    </div>
  </div>

  <footer class="text-center py-3 mt-5 text-muted border-top bg-light">
    <p class="mb-0">Derechos reservados &copy; IMSS <?= date('Y') ?></p>
  </footer>

  <div class="modal fade" id="modalGraficos" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-fullscreen">
          <div class="modal-content border-0 bg-transparent">
              
              <div class="modal-body p-0" style="overflow: hidden; height: 100vh; background-color: #f8fafc;">
                  <iframe src="/graficos/index.html" style="width: 100%; height: 100%; border: none; display: block;"></iframe>
              </div>
              
          </div>
      </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

  <script type="module" src="/js/vencer.js"></script>

</body>
</html>