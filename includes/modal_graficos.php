<div class="modal fade" id="modalGraficos" tabindex="-1" aria-labelledby="modalGraficosLabel" >
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            
            <div class="modal-header" style="background-color: #7a123a; color: white;">
                <h5 class="modal-title fw-bold"><i class="fas fa-chart-line me-2"></i>VENCER</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body bg-light">

                <div class="d-flex justify-content-center align-items-center mb-4 gap-3 bg-white p-3 rounded shadow-sm border flex-wrap">
                    
                    <div class="d-flex align-items-center gap-2">
                        <label class="fw-bold text-secondary small">AÑO:</label>
                        <select id="filtroAnio" class="form-select form-select-sm w-auto fw-bold shadow-sm border-danger">
                            <option value="todos">Cargando...</option>
                        </select>
                    </div>

                    <div class="vr mx-2"></div>

                    <div class="d-flex align-items-center gap-2 border p-1 rounded bg-light">
                        <label class="fw-bold text-secondary small ms-1">MESES:</label>
                        
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-0 fw-bold text-muted" style="font-size: 0.8rem;">De:</span>
                            <select id="filtroMesInicio" class="form-select form-select-sm fw-bold shadow-sm border-secondary" style="max-width: 110px;">
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>

                        <span class="fw-bold text-muted">-</span>

                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-0 fw-bold text-muted" style="font-size: 0.8rem;">A:</span>
                            <select id="filtroMesFin" class="form-select form-select-sm fw-bold shadow-sm border-secondary" style="max-width: 110px;">
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12" selected>Diciembre</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="vr mx-2"></div>

                    <label class="small fw-bold ms-2">Servicio:</label>
                    <select id="filtroServicioGrafico" class="form-select form-select-sm shadow-sm" style="max-width: 200px;">
                        <option value="">Todos</option>
                    </select>

                    <div class="d-flex align-items-center bg-light px-3 py-1 rounded border">
                        <span class="text-muted small text-uppercase fw-bold me-2">Total:</span>
                        <span id="lblTotalEventos" class="fs-5 fw-bold text-danger">0</span>
                    </div>
                </div>

                <ul class="nav nav-pills nav-fill mb-4 gap-2 p-1 bg-white rounded shadow-sm" id="graficosTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#tab-general">📊 Panorama General</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold text-danger" data-bs-toggle="tab" data-bs-target="#tab-adverso">🚨 Eventos Adversos</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold text-warning" data-bs-toggle="tab" data-bs-target="#tab-cuasi">⚠️ Cuasifallas</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#tab-centinela">Eventos Centinela</button></li>
                </ul>
                <!-- -->
                <div class="tab-content" id="graficosTabContent">
    
                    <div class="tab-pane fade show active" id="tab-general">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6"><div class="card h-100 shadow-sm border-0"><div class="card-body"><h6 class="text-center fw-bold text-secondary mb-3">DISTRIBUCIÓN POR SEXO</h6><div style="height:250px"><canvas id="chartSexoGen"></canvas></div><div id="tablaSexoGen" class="mt-3 table-responsive" style="max-height: 150px;"></div></div></div></div>
                            <div class="col-md-6"><div class="card h-100 shadow-sm border-0"><div class="card-body"><h6 class="text-center fw-bold text-secondary mb-3">CLASIFICACIÓN DE EVENTOS</h6><div style="height:250px"><canvas id="chartEventosGen"></canvas></div><div id="tablaEventosGen" class="mt-3 table-responsive" style="max-height: 150px;"></div></div></div></div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-secondary mb-3"> Edad/Sexo</h6>
                                        
                                        <div style="height: 350px; position: relative;">
                                            <canvas id="chartPiramide"></canvas>
                                        </div>

                                        <div id="tablaEdadSexoGen" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6"><div class="card h-100 shadow-sm border-0"><div class="card-body"><h6 class="text-center fw-bold text-success mb-3">TURNOS</h6><div style="height:250px"><canvas id="chartTurnoGen"></canvas></div><div id="tablaTurnoGen" class="mt-3 table-responsive" style="max-height: 150px;"></div></div></div></div>
                        </div>
                        <div class="row g-3"><div class="col-12"><div class="card h-100 shadow-sm border-0"><div class="card-body"><h6 class="text-center fw-bold text-primary mb-3">TOP SERVICIOS</h6><div style="height:350px"><canvas id="chartTopServiciosGen"></canvas></div><div id="tablaTopServiciosGen" class="mt-3 table-responsive" style="max-height: 200px;"></div></div></div></div></div>
                    </div>

                    <div class="tab-pane fade" id="tab-adverso">
                        <div class="alert alert-danger py-2 text-center fw-bold mb-3"><i class="fas fa-exclamation-circle me-2"></i>Análisis de Eventos Adversos</div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-lg-6">
                                <div class="card h-100 shadow-sm border-danger border-opacity-25">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-danger">ÁREAS CON MAYOR INCIDENCIA (TOP 10)</h6>
                                        <div style="height: 250px;"><canvas id="chartServicioAdv"></canvas></div>
                                        <div id="tablaServicioAdv" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100 shadow-sm border-danger border-opacity-50" style="background-color: #fff5f5;">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-dark">#1 ANÁLISIS: <span id="lblTopAreaAdv1" class="text-danger">---</span></h6>
                                        <div style="height: 250px;"><canvas id="chartDrillDownAdv1"></canvas></div>
                                        <div id="tablaDrillDownAdv1" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-lg-6">
                                <div class="card h-100 shadow-sm border-danger border-opacity-50" style="background-color: #fffaf0;">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-dark">#2 ANÁLISIS: <span id="lblTopAreaAdv2" class="text-danger">---</span></h6>
                                        <div style="height: 250px;"><canvas id="chartDrillDownAdv2"></canvas></div>
                                        <div id="tablaDrillDownAdv2" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100 shadow-sm border-danger border-opacity-50" style="background-color: #f0fff4;">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-dark">#3 ANÁLISIS: <span id="lblTopAreaAdv3" class="text-danger">---</span></h6>
                                        <div style="height: 250px;"><canvas id="chartDrillDownAdv3"></canvas></div>
                                        <div id="tablaDrillDownAdv3" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="card h-100 shadow-sm border-danger border-opacity-25">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-danger">CAUSAS GLOBALES (Definición)</h6>
                                        <div style="height: 300px;"><canvas id="chartDefinicionAdv"></canvas></div>
                                        <div id="tablaDefinicionAdv" class="mt-3 table-responsive" style="max-height: 200px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-cuasi">
                        <div class="alert alert-warning py-2 text-center fw-bold mb-3 text-dark"><i class="fas fa-shield-alt me-2"></i>Análisis de Cuasifallas</div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-lg-6">
                                <div class="card h-100 shadow-sm border-warning border-opacity-25">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-warning">ÁREAS CON MAYOR INCIDENCIA (TOP 10)</h6>
                                        <div style="height: 250px;"><canvas id="chartServicioCuasi"></canvas></div>
                                        <div id="tablaServicioCuasi" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100 shadow-sm border-warning border-opacity-50" style="background-color: #fffff0;">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-dark">#1 ANÁLISIS: <span id="lblTopAreaCuasi1" class="text-warning">---</span></h6>
                                        <div style="height: 250px;"><canvas id="chartDrillDownCuasi1"></canvas></div>
                                        <div id="tablaDrillDownCuasi1" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-lg-6">
                                <div class="card h-100 shadow-sm border-warning border-opacity-50" style="background-color: #fffaf0;">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-dark">#2 ANÁLISIS: <span id="lblTopAreaCuasi2" class="text-warning">---</span></h6>
                                        <div style="height: 250px;"><canvas id="chartDrillDownCuasi2"></canvas></div>
                                        <div id="tablaDrillDownCuasi2" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100 shadow-sm border-warning border-opacity-50" style="background-color: #f0fff4;">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-dark">#3 ANÁLISIS: <span id="lblTopAreaCuasi3" class="text-warning">---</span></h6>
                                        <div style="height: 250px;"><canvas id="chartDrillDownCuasi3"></canvas></div>
                                        <div id="tablaDrillDownCuasi3" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="card h-100 shadow-sm border-warning border-opacity-25">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-warning">CAUSAS GLOBALES (Definición)</h6>
                                        <div style="height: 300px;"><canvas id="chartDefinicionCuasi"></canvas></div>
                                        <div id="tablaDefinicionCuasi" class="mt-3 table-responsive" style="max-height: 200px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="tab-pane fade" id="tab-centinela">
                        <div class="alert alert-dark py-2 text-center fw-bold mb-3">Análisis de Eventos Centinela</div>
                        <div class="row g-3">
                            <div class="col-lg-6"><div class="card h-100 shadow-sm"><div class="card-body"><h6 class="text-center fw-bold text-dark">ÁREAS CON MAYOR INCIDENCIA</h6><div style="height: 350px;"><canvas id="chartServicioCent"></canvas></div><div id="tablaServicioCent" class="mt-3 table-responsive" style="max-height: 200px;"></div></div></div></div>
                            <div class="col-lg-6"><div class="card h-100 shadow-sm"><div class="card-body"><h6 class="text-center fw-bold text-dark">CAUSAS PRINCIPALES (Definición)</h6><div style="height: 350px;"><canvas id="chartDefinicionCent"></canvas></div><div id="tablaDefinicionCent" class="mt-3 table-responsive" style="max-height: 200px;"></div></div></div></div>
                        </div>
                    </div>

                </div>
                        
                
                <!-- -->
                <div class="row mt-4 border-top pt-3">
                    <div class="col text-center">
                        <button id="btnDescargarExcelStats" class="btn btn-excel-verde fw-bold shadow px-4 py-2" style="background-color: #217346; color: white;">
                            <i class="fas fa-file-excel me-2"></i>Descargar Reporte (Excel)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>