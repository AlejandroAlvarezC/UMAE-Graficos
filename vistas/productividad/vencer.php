<?php
session_start();

// Verificar sesión de administrador
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['login_error'] = "Debes iniciar sesión como administrador para acceder a esa sección.";
    header('Location: ../admin/login.php');
    exit();
}

// --- CORRECCIÓN VITAL ---
// Conectamos con el MODELO (Cerebro) que creaste en el Paso 1.
// Así la vista puede pedir los datos usando Vencer::listar().
require_once __DIR__ . '/../../modelos/Vencer.php'; 

// Obtenemos los datos para la tabla
$registros = [];
try {
    $registros = Vencer::listar();
} catch (Exception $e) {
    $error_msg = "Error al cargar datos: " . $e->getMessage();
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
      <?php if(isset($_GET['error']) || isset($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($_GET['error'] ?? $error_msg) ?>
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
            <button id="btnGrafico" class="btn btn-urgencia shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGraficos">
              📊 Ver Gráficos
            </button>
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
                <select id="filter<?= $c ?>" class="form-select select-personalizado form-select-sm" multiple></select>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <?php if (count($registros) > 0): ?>
        <div class="card shadow-sm mt-4 border-0">
          <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 text-secondary"><i class="bi bi-table me-2"></i>Registros Actuales</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover table-striped mb-0 text-center align-middle" id="tabla-vencer">
                <thead class="table-light">
                  <tr>
                    <th>Folio</th><th>Evento</th><th>Iniciales</th><th>NSS</th><th>Edad</th><th>Sexo</th>
                    <th>Diagnostico</th><th>Fecha Evento</th><th>Fecha Notif.</th><th>Turno</th>
                    <th>Servicio</th><th>Categoria</th><th>Proceso</th><th>Definicion</th><th>Descripcion</th>
                    <th>Estatus</th><th>Año</th><th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($registros as $r): ?>
                    <tr>
                      <td><?= htmlspecialchars($r['folio'] ?? '') ?></td>
                      <td><?= htmlspecialchars($r['evento'] ?? '') ?></td>
                      <td><?= htmlspecialchars($r['ini_paciente'] ?? '') ?></td>
                      <td><?= htmlspecialchars($r['seguridad_social'] ?? '') ?></td>
                      <td><?= htmlspecialchars($r['edad'] ?? '') ?></td>
                      <td><?= htmlspecialchars($r['sexo'] ?? '') ?></td>
                      <td><?= htmlspecialchars($r['diagnostico'] ?? '') ?></td>
                      <td><?= htmlspecialchars($r['fecha_evento'] ?? '') ?></td>
                      <td><?= htmlspecialchars($r['fecha_noti'] ?? '') ?></td>
                      <td><?= htmlspecialchars($r['turno'] ?? '') ?></td>
                      <td><?= htmlspecialchars($r['servicio'] ?? '') ?></td>
                      <td><?= htmlspecialchars($r['categoria'] ?? '') ?></td>
                      <td><?= htmlspecialchars($r['proceso'] ?? '') ?></td>
                      <td><?= htmlspecialchars($r['definicion'] ?? '') ?></td>
                      <td><?= htmlspecialchars($r['descripcion'] ?? '') ?></td>
                      <td>
                        <span class="badge bg-secondary"><?= htmlspecialchars($r['estatus'] ?? '') ?></span>
                      </td>
                      <td><?= htmlspecialchars($r['anio'] ?? '') ?></td>
                      <td>
                        <div class="d-flex gap-1 justify-content-center">
                            <a class="btn btn-outline-primary btn-sm" href="./editar-vencer.php?id=<?= base64_encode($r['id']) ?>" title="Editar"><i class="bi bi-pencil"></i></a>
                            <a class="btn btn-outline-danger btn-sm" href="../../controladores/vencer.php?a=Eliminar&id=<?= base64_encode($r['id']) ?>" onclick="return confirm('¿Estás seguro de eliminar este registro? No se puede deshacer.')" title="Eliminar"><i class="bi bi-trash"></i></a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="alert alert-info mt-4 d-flex align-items-center shadow-sm">
            <i class="bi bi-info-circle fs-4 me-3"></i>
            <div>
                <strong>No hay registros.</strong> La base de datos está vacía o no se encontraron datos.
                <br>Usa el botón "Agregar manual" o "Cargar archivo" para comenzar.
            </div>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <footer class="text-center py-3 mt-5 text-muted border-top bg-light">
    <p class="mb-0">Derechos reservados &copy; IMSS <?= date('Y') ?></p>
  </footer>

  <?php include '../../includes/modal_graficos.php'; ?>

  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<script>
      // 1. Recibimos los datos "sucios" de PHP
      const datosBrutos = <?php echo json_encode($registros, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

      // 2. Función para convertir "&lt;" de vuelta a "<"
      function decodificarTexto(html) {
          var txt = document.createElement("textarea");
          txt.innerHTML = html;
          return txt.value;
      }

      // 3. Limpiamos cada registro antes de dárselo a los gráficos
      const datosVencer = datosBrutos.map(function(item) {
          return {
              ...item, // Copiamos todo el registro
              edad: decodificarTexto(item.edad), // Corregimos la edad
              diagnostico: decodificarTexto(item.diagnostico) // Corregimos diagnóstico por si acaso
          };
      });

      console.log("Datos limpios cargados:", datosVencer.length);
  </script>
  
  <script src="../../js/vencer.js"></script>

</body>
</html>