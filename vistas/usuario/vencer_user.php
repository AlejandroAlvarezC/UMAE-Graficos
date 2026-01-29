<?php
session_start();
require_once '../../modelos/vencer.php';

// --- PROCESAMIENTO DE DATOS PARA EL GRÁFICO ---
$registros = Vencer::listar();
$conteoGrafico = [];

if (count($registros) > 0) {
    foreach ($registros as $r) {
        // Usamos Sexo (índice 6) y Definición (índice 14) según la estructura estándar
        $etiqueta = ($r[6] ?? 'N/E') . " - " . ($r[14] ?? 'Sin Definir');
        $conteoGrafico[$etiqueta] = ($conteoGrafico[$etiqueta] ?? 0) + 1;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="../../css/bootstrap.min.css">
  <link rel="stylesheet" href="../../css/styles.css">
  <link rel="icon" type="image/png" href="../../logo-imss.png">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
  
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

  <title>UMAE-48 | Vencer</title>

  <style>
    /* Estilos base mantenidos de tu original */
    html, body { height: 100%; margin: 0; display: flex; flex-direction: column; }
    .content { flex: 1; }
    table.table { font-size: 0.85rem; }
    table.table thead th { background-color: #c46116; color: white; text-align: center; }
    .btn-urgencia { background-color: #d55500; color: white; padding: 10px 20px; border-radius: 6px; font-weight: 600; text-decoration: none; border:none; }
    .btn-urgencia:hover { background-color: #b34700; color: white; }
    .btn-success { background: linear-gradient(135deg, #6e3d1e, #ac5d1d); border: none; color: white !important; font-weight: 700; border-radius: 12px; }
  </style>
</head>

<body style="background-color: #fffef6">

  <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #00664d;">
    <div class="container-fluid">
      <a class="navbar-brand" href="../roles/index.php">Inicio / Vencer</a>
    </div>
  </nav>

  <div class="content">
    <div class="container py-4">
      <h1 class="text-center mb-4" style="color: #495057; font-weight:bold">Módulo Vencer</h1>

      <div class="card shadow-sm mb-4 p-3">
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <button class="btn btn-urgencia">🟩 Descargar Excel</button>
            <button id="btnDescargarGraficoPDF" class="btn btn-success">📈 Descargar PDF Reporte</button>
            <button id="btnGrafico" class="btn btn-urgencia" data-bs-toggle="modal" data-bs-target="#modalGraficos">
              📊 Ver Gráficos
            </button>
        </div>
      </div>

      <div class="modal fade" id="modalGraficos" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header bg-light">
              <h5 class="modal-title">📈 Tablero Estadístico VENCER</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="row g-4">
                <div class="col-md-10 mx-auto">
                  <div class="card shadow-sm">
                    <div class="card-body">
                      <h5 class="text-center">Distribución por Sexo y Tipo de Evento</h5>
                      <div style="height: 450px;">
                        <canvas id="chartSexoEvento"></canvas>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm mt-4">
        <div class="card-body">
          <div class="table-responsive">
            <table id="tablaVencer" class="table table-bordered table-hover">
              <thead>
                <tr>
                    <th>Folio</th><th>Evento</th><th>Sexo</th><th>Servicio</th><th>Definición</th><th>Estatus</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($registros as $r): ?>
                <tr>
                    <td><?= $r[0] ?></td><td><?= $r[1] ?></td><td><?= $r[6] ?></td><td><?= $r[11] ?></td><td><?= $r[14] ?></td><td><?= $r[16] ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>

  <footer>IMSS UMAE 48 - 2024</footer>

  <script>
    $(document).ready(function() {
      // 1. Inicializar DataTable
      $('#tablaVencer').DataTable({ language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' } });

      // 2. Crear el Gráfico de Pastel
      const ctx = document.getElementById('chartSexoEvento');
      if (ctx) {
          new Chart(ctx, {
              type: 'pie',
              data: {
                  labels: <?= json_encode(array_keys($conteoGrafico)) ?>,
                  datasets: [{
                      data: <?= json_encode(array_values($conteoGrafico)) ?>,
                      backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
                  }]
              },
              options: { responsive: true, maintainAspectRatio: false }
          });
      }

      // 3. Script PDF (CORREGIDO PARA EVITAR NULL)
      const btnPdf = document.getElementById('btnDescargarGraficoPDF');
      if (btnPdf) {
          btnPdf.addEventListener('click', async () => {
              const { jsPDF } = window.jspdf;
              const canvas = document.getElementById('chartSexoEvento');
              
              if (!canvas) {
                  alert("Por favor abre primero 'Ver Gráficos' para generar el reporte.");
                  return;
              }

              const pdf = new jsPDF('p', 'mm', 'letter');
              pdf.setFontSize(16);
              pdf.text("REPORTE ESTADÍSTICO VENCER - UMAE 48", 105, 20, { align: "center" });
              
              const imgData = canvas.toDataURL("image/png");
              pdf.addImage(imgData, 'PNG', 30, 40, 150, 100);
              
              pdf.setFontSize(10);
              pdf.text("Generado el: " + new Date().toLocaleDateString(), 105, 150, { align: "center" });
              
              pdf.save("Reporte_Vencer.pdf");
          });
      }
    });
  </script>
</body>
</html>