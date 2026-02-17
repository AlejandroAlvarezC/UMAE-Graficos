document.addEventListener('DOMContentLoaded', function() {
    console.log("🚀 VENCER: Módulo cargado - Versión Pirámide Integrada");

    // CONFIGURACIÓN DE COLUMNAS (Indices del Array)
    const COL = { 
        EVENTO: 1, EDAD: 4, SEXO: 5, 
        FECHA: 7, 
        TURNO: 9, SERVICIO: 10, DEFINICION: 13, ANIO: 16 
    };

    // 1. INICIALIZAR TABLA
    const tabla = $('#tabla-vencer').DataTable({
        pageLength: 10, order: [[0, 'asc']],
        deferRender: true, processing: true,
        language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
    });

    // 2. PREPARAR FILTROS (DOM)
    const initFiltros = () => {
        const fAnio = document.getElementById('filtroAnio');
        if(fAnio) {
            const anios = [...new Set(tabla.column(COL.ANIO).data().toArray())]
                .filter(x => x && x !== 'NULO').sort().reverse();
            fAnio.innerHTML = '<option value="todos">Todos los años</option>';
            anios.forEach(a => fAnio.append(new Option(a, a)));
        }

        const headers = ['Folio', 'Evento', 'Iniciales', 'NSS', 'Edad', 'Sexo', 'Diagnostico', 'FechaEvento', 'FechaNotificacion', 'Turno', 'Servicio', 'Categoria', 'Proceso', 'Definicion', 'Descripcion', 'Estatus', 'Anio'];
        headers.forEach((h, i) => {
            const select = document.getElementById(`filter${h}`); if (!select) return;
            const rawData = tabla.column(i).data().toArray();
            const uniqueValues = new Set();
            rawData.forEach(d => {
                let text = String(d).replace(/<[^>]*>?/gm, '').replace(/Ver más|Ver menos/g, '').trim();
                if (text && text !== 'NULO') uniqueValues.add(text);
            });
            const fragment = document.createDocumentFragment();
            const optNulo = document.createElement('option'); optNulo.value = 'NULO'; optNulo.textContent = 'NULO'; fragment.appendChild(optNulo);
            Array.from(uniqueValues).sort().forEach(v => { const o = document.createElement('option'); o.value = v; o.textContent = v; fragment.appendChild(o); });
            select.innerHTML = ''; select.appendChild(fragment);
        });

        $('.select-personalizado').not('#filtroAnio, #filtroMesInicio, #filtroMesFin').select2({ placeholder: 'Filtrar...', allowClear: true, width: '100%' });
        $('.select-personalizado').on('change', () => { tabla.draw(); generarGraficos(); });
    };

    // 3. MOTOR DE BÚSQUEDA
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'tabla-vencer') return true;
        
        const anioSel = $('#filtroAnio').val();
        const mesIni = parseInt($('#filtroMesInicio').val()) || 1;
        const mesFin = parseInt($('#filtroMesFin').val()) || 12;
        const anioRow = data[COL.ANIO] || "";
        const fechaRow = data[COL.FECHA] || "";

        let okAnio = (anioSel === 'todos' || anioRow === anioSel);
        let okMes = true;

        if (okAnio) {
            let mesRow = 0;
            if (fechaRow.includes('-')) mesRow = parseInt(fechaRow.split('-')[1]); 
            else if (fechaRow.includes('/')) mesRow = parseInt(fechaRow.split('/')[1]);
            if (mesRow < mesIni || mesRow > mesFin) okMes = false;
        }

        let okCols = true;
        const headers = ['Folio', 'Evento', 'Iniciales', 'NSS', 'Edad', 'Sexo', 'Diagnostico', 'FechaEvento', 'FechaNotificacion', 'Turno', 'Servicio', 'Categoria', 'Proceso', 'Definicion', 'Descripcion', 'Estatus', 'Anio'];
        for (let i = 0; i < headers.length; i++) {
            const val = $(`#filter${headers[i]}`).val();
            if (val && val.length > 0 && !val.includes(data[i].trim() || 'NULO')) { okCols = false; break; }
        }
        return okAnio && okMes && okCols;
    });


    // 4. MOTOR GRÁFICO MEJORADO (Multi-Drill Down + Pirámide)
    let charts = {};
    const generarGraficos = () => {
        setTimeout(() => {
            // Obtenemos SOLO los datos que pasaron los filtros
            const rows = tabla.rows({ search: 'applied' }).data().toArray();
            
            const lbl = document.getElementById('lblTotalEventos');
            if(lbl) lbl.textContent = rows.length;
            if (rows.length === 0) return;

            // Estructura de datos
            let s = { General: { Sexo:{}, Evento:{}, Turno:{}, Servicio:{}, Edad:{} }, Adverso:{Servicio:{}, Definicion:{}}, Cuasi:{Servicio:{}, Definicion:{}}, Centinela:{Servicio:{}, Definicion:{}} };
            
            // Procesamiento
            rows.forEach(r => {
                const clean = (txt) => String(txt).replace(/<[^>]*>?/gm, '').replace(/Ver más|Ver menos/g, '').trim() || 'NULO';
                let ev = clean(r[COL.EVENTO]), ed = clean(r[COL.EDAD]), sx = clean(r[COL.SEXO]), tu = clean(r[COL.TURNO]), sv = clean(r[COL.SERVICIO]), def = clean(r[COL.DEFINICION]);
                
                s.General.Sexo[sx] = (s.General.Sexo[sx] || 0) + 1; s.General.Evento[ev] = (s.General.Evento[ev] || 0) + 1; s.General.Turno[tu] = (s.General.Turno[tu] || 0) + 1; s.General.Servicio[sv] = (s.General.Servicio[sv] || 0) + 1;
                
                // Acumulamos edad para la pirámide
                if (!s.General.Edad[ed]) s.General.Edad[ed] = { H: 0, M: 0 };
                // Detección robusta de sexo
                let esHombre = sx.toUpperCase().includes('MASC') || sx.toUpperCase().startsWith('H') || sx.toUpperCase() === 'M';
                esHombre ? s.General.Edad[ed].H++ : s.General.Edad[ed].M++;
                
                let evUpper = ev.toUpperCase();
                if(evUpper.includes('ADVERSO')) { s.Adverso.Servicio[sv]=(s.Adverso.Servicio[sv]||0)+1; s.Adverso.Definicion[def]=(s.Adverso.Definicion[def]||0)+1; }
                else if(evUpper.includes('CUASI')) { s.Cuasi.Servicio[sv]=(s.Cuasi.Servicio[sv]||0)+1; s.Cuasi.Definicion[def]=(s.Cuasi.Definicion[def]||0)+1; }
                else if(evUpper.includes('CENTINELA')) { s.Centinela.Servicio[sv]=(s.Centinela.Servicio[sv]||0)+1; s.Centinela.Definicion[def]=(s.Centinela.Definicion[def]||0)+1; }
            });

            // Helpers de dibujo
            const draw = (id, t, l, d, c, opts={}) => {
                const el = document.getElementById(id); if(!el) return;
                if(charts[id]) charts[id].destroy();
                charts[id] = new Chart(el.getContext('2d'), { type: t, data: { labels: l, datasets: [{ label: 'Total', data: d, backgroundColor: c }] }, options: { responsive: true, maintainAspectRatio: false, animation: false, indexAxis: 'y', ...opts } });
            };
            const drawTable = (id, obj, t) => {
                const el = document.getElementById(id); if(!el) return;
                const ent = Object.entries(obj).sort((a,b)=>((typeof b[1]==='object'?b[1].H+b[1].M:b[1])-(typeof a[1]==='object'?a[1].H+a[1].M:a[1])));
                let sumaTotal = 0;
                let h = `<table class="table table-sm table-striped table-bordered text-center small mb-0"><thead class="table-dark"><tr><th>${t}</th><th>Total</th></tr></thead><tbody>`;
                ent.forEach(([k,v])=>{ 
                    let val = (typeof v === 'object') ? (v.H + v.M) : v;
                    sumaTotal += val;
                    h+=`<tr><td class="text-start">${k}</td><td class="fw-bold">${val}</td></tr>`; 
                });
                if(ent.length > 0) h += `<tr class="table-secondary" style="border-top: 2px solid #7a123a;"><td class="text-end fw-bold">TOTAL</td><td class="fw-bold text-danger">${sumaTotal}</td></tr>`;
                else h += `<tr><td colspan="2">Sin datos</td></tr>`;
                el.innerHTML = h+'</tbody></table>';
            };

            // --- 1. GENERALES ---
            draw('chartSexoGen', 'pie', Object.keys(s.General.Sexo), Object.values(s.General.Sexo), ['#0d6efd','#dc3545','#ffc107'], {indexAxis:'x'}); drawTable('tablaSexoGen', s.General.Sexo, 'Sexo');
            draw('chartEventosGen', 'bar', Object.keys(s.General.Evento), Object.values(s.General.Evento), '#7a123a', {indexAxis:'x'}); drawTable('tablaEventosGen', s.General.Evento, 'Evento');
            draw('chartTurnoGen', 'bar', Object.keys(s.General.Turno), Object.values(s.General.Turno), '#198754'); drawTable('tablaTurnoGen', s.General.Turno, 'Turno');
            const ts = Object.entries(s.General.Servicio).sort((a,b)=>b[1]-a[1]).slice(0,10); draw('chartTopServiciosGen', 'bar', ts.map(x=>x[0]), ts.map(x=>x[1]), '#0288d1'); drawTable('tablaTopServiciosGen', s.General.Servicio, 'Servicio');

            // --- [AQUÍ INTEGRAMOS LA PIRÁMIDE] ---
            if(document.getElementById('chartPiramide')) {
                // 1. Definimos orden estricto de rangos
                const rangosOrden = [
                    "<1 de un año", "1 a 4 años", "5 a 9", "10 a 14", "15 a 19",
                    "20 a 29", "30 a 39", "40 a 49", "50 a 59", ">60"
                ];

                // 2. Mapeamos los datos acumulados a estos rangos
                let dataH = [];
                let dataM = [];

                rangosOrden.forEach(rango => {
                    // Buscamos si existe el rango exacto o parcial en los datos acumulados
                    // s.General.Edad tiene claves como "20 a 29"
                    let key = Object.keys(s.General.Edad).find(k => k.includes(rango.split(' ')[0])); // Búsqueda flexible
                    
                    if (key && s.General.Edad[key]) {
                        // Hombres negativos para izquierda
                        dataH.push(0 - s.General.Edad[key].H);
                        dataM.push(s.General.Edad[key].M);
                    } else {
                        dataH.push(0);
                        dataM.push(0);
                    }
                });

                if(charts['chartPiramide']) charts['chartPiramide'].destroy();
                
                charts['chartPiramide'] = new Chart(document.getElementById('chartPiramide'), {
                    type: 'bar',
                    data: {
                        labels: rangosOrden,
                        datasets: [
                            { label: 'Hombres', data: dataH, backgroundColor: 'rgba(54, 162, 235, 0.7)', borderWidth: 1 },
                            { label: 'Mujeres', data: dataM, backgroundColor: 'rgba(255, 99, 132, 0.7)', borderWidth: 1 }
                        ]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { stacked: true, ticks: { callback: function(val) { return Math.abs(val); } } },
                            y: { stacked: true, grid: { display: false } }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(c) { return c.dataset.label + ': ' + Math.abs(c.raw); }
                                }
                            }
                        }
                    }
                });

                // Si quieres la tabla debajo, usa el helper existente
                drawTable('tablaEdadSexoGen', s.General.Edad, 'Edad');
            }
            // --------------------------------------

            // --- FUNCIÓN INTELIGENTE PARA DRILL DOWN MÚLTIPLE ---
            const generarMultiDrill = (tipo, colorBarra, configIds) => {
                const dataObj = s[tipo];
                const tServ = Object.entries(dataObj.Servicio).sort((a,b)=>b[1]-a[1]);
                draw(configIds.mainChart, 'bar', tServ.slice(0,10).map(x=>x[0]), tServ.slice(0,10).map(x=>x[1]), colorBarra); 
                drawTable(configIds.mainTable, dataObj.Servicio, 'Servicio');

                const tDef = Object.entries(dataObj.Definicion).sort((a,b)=>b[1]-a[1]).slice(0,10);
                draw(configIds.defChart, 'bar', tDef.map(x=>x[0]), tDef.map(x=>x[1]), colorBarra); 
                drawTable(configIds.defTable, dataObj.Definicion, 'Causa');

                [0, 1, 2].forEach(index => {
                    const chartId = configIds.drills[index].chart;
                    const tableId = configIds.drills[index].table;
                    const labelId = configIds.drills[index].lbl;
                    
                    if (tServ.length > index) {
                        const areaName = tServ[index][0]; 
                        const areaVal = tServ[index][1];
                        const elLbl = document.getElementById(labelId);
                        if(elLbl) elLbl.textContent = `${areaName} (${areaVal})`;

                        let drillCounts = {};
                        rows.forEach(r => {
                            const cl = (x)=>String(x).replace(/<[^>]*>?/gm,'').trim()||'NULO';
                            let evUpper = cl(r[COL.EVENTO]).toUpperCase();
                            let sv = cl(r[COL.SERVICIO]);
                            let coincide = (tipo === 'Adverso' && evUpper.includes('ADVERSO')) || (tipo === 'Cuasi' && evUpper.includes('CUASI'));
                            if (coincide && sv === areaName) { drillCounts[cl(r[COL.DEFINICION])] = (drillCounts[cl(r[COL.DEFINICION])] || 0) + 1; }
                        });

                        const tDrill = Object.entries(drillCounts).sort((a,b)=>b[1]-a[1]).slice(0,10);
                        draw(chartId, 'bar', tDrill.map(x=>x[0]), tDrill.map(x=>x[1]), '#212529'); 
                        drawTable(tableId, drillCounts, 'Causa Específica');
                    } else {
                        const elLbl = document.getElementById(labelId);
                        if(elLbl) elLbl.textContent = "---";
                        draw(chartId, 'bar', [], [], '#ccc');
                        document.getElementById(tableId).innerHTML = '';
                    }
                });
            };

            generarMultiDrill('Adverso', '#dc3545', {
                mainChart: 'chartServicioAdv', mainTable: 'tablaServicioAdv',
                defChart: 'chartDefinicionAdv', defTable: 'tablaDefinicionAdv',
                drills: [
                    { chart: 'chartDrillDownAdv1', table: 'tablaDrillDownAdv1', lbl: 'lblTopAreaAdv1' },
                    { chart: 'chartDrillDownAdv2', table: 'tablaDrillDownAdv2', lbl: 'lblTopAreaAdv2' },
                    { chart: 'chartDrillDownAdv3', table: 'tablaDrillDownAdv3', lbl: 'lblTopAreaAdv3' }
                ]
            });

            generarMultiDrill('Cuasi', '#ffc107', {
                mainChart: 'chartServicioCuasi', mainTable: 'tablaServicioCuasi',
                defChart: 'chartDefinicionCuasi', defTable: 'tablaDefinicionCuasi',
                drills: [
                    { chart: 'chartDrillDownCuasi1', table: 'tablaDrillDownCuasi1', lbl: 'lblTopAreaCuasi1' },
                    { chart: 'chartDrillDownCuasi2', table: 'tablaDrillDownCuasi2', lbl: 'lblTopAreaCuasi2' },
                    { chart: 'chartDrillDownCuasi3', table: 'tablaDrillDownCuasi3', lbl: 'lblTopAreaCuasi3' }
                ]
            });

            const tCentS = Object.entries(s.Centinela.Servicio).sort((a,b)=>b[1]-a[1]).slice(0,10);
            const tCentD = Object.entries(s.Centinela.Definicion).sort((a,b)=>b[1]-a[1]).slice(0,10);
            draw('chartServicioCent', 'bar', tCentS.map(x=>x[0]), tCentS.map(x=>x[1]), '#212529'); drawTable('tablaServicioCent', s.Centinela.Servicio, 'Servicio');
            draw('chartDefinicionCent', 'bar', tCentD.map(x=>x[0]), tCentD.map(x=>x[1]), '#212529'); drawTable('tablaDefinicionCent', s.Centinela.Definicion, 'Causa');
        }, 200);
    };

    // 5. EXCEL MASTER (Mantenemos tu lógica de Excel)
    $(document).on('click', '#btnDescargarExcelStats', async function() {
        // ... (Tu código de Excel original se queda igual aquí, ya funciona bien) ...
        const btn = this; const originalText = btn.innerHTML; 
        btn.innerHTML = "⏳ Generando..."; btn.disabled = true;
        
        try {
            const rows = tabla.rows({ search: 'applied' }).data().toArray();
            if (rows.length === 0) throw new Error("No hay datos");
            
            // Recálculo de datos para Excel
            let s = { General: { Sexo:{}, Evento:{}, Turno:{}, Servicio:{}, Edad:{} }, Adverso:{Servicio:{}, Definicion:{}}, Cuasi:{Servicio:{}, Definicion:{}}, Centinela:{Servicio:{}, Definicion:{}} };
            rows.forEach(r => {
                const clean = (txt) => String(txt).replace(/<[^>]*>?/gm, '').replace(/Ver más|Ver menos/g, '').trim() || 'NULO';
                let ev = clean(r[COL.EVENTO]), ed = clean(r[COL.EDAD]), sx = clean(r[COL.SEXO]), tu = clean(r[COL.TURNO]), sv = clean(r[COL.SERVICIO]), def = clean(r[COL.DEFINICION]);
                s.General.Sexo[sx] = (s.General.Sexo[sx] || 0) + 1; s.General.Evento[ev] = (s.General.Evento[ev] || 0) + 1; s.General.Turno[tu] = (s.General.Turno[tu] || 0) + 1; s.General.Servicio[sv] = (s.General.Servicio[sv] || 0) + 1;
                if (!s.General.Edad[ed]) s.General.Edad[ed] = { H: 0, M: 0 };
                (sx.toUpperCase().includes('MASC') || sx.toUpperCase().includes('HOMBRE')) ? s.General.Edad[ed].H++ : s.General.Edad[ed].M++;
                let evUpper = ev.toUpperCase();
                if(evUpper.includes('ADVERSO')) { s.Adverso.Servicio[sv]=(s.Adverso.Servicio[sv]||0)+1; s.Adverso.Definicion[def]=(s.Adverso.Definicion[def]||0)+1; }
                else if(evUpper.includes('CUASI')) { s.Cuasi.Servicio[sv]=(s.Cuasi.Servicio[sv]||0)+1; s.Cuasi.Definicion[def]=(s.Cuasi.Definicion[def]||0)+1; }
                else if(evUpper.includes('CENTINELA')) { s.Centinela.Servicio[sv]=(s.Centinela.Servicio[sv]||0)+1; s.Centinela.Definicion[def]=(s.Centinela.Definicion[def]||0)+1; }
            });

            const workbook = new ExcelJS.Workbook();
            const generarImagenFantasma = (type, labels, data, color, title) => {
                return new Promise((resolve, reject) => {
                    const canvas = document.createElement('canvas');
                    canvas.width = 800; canvas.height = 400;
                    const ctx = canvas.getContext('2d');
                    const whiteBackground = { id: 'custom_canvas_background_color', beforeDraw: (chart) => { const ctx = chart.canvas.getContext('2d'); ctx.save(); ctx.globalCompositeOperation = 'destination-over'; ctx.fillStyle = 'white'; ctx.fillRect(0, 0, chart.width, chart.height); ctx.restore(); } };
                    const tempChart = new Chart(ctx, { type: type, data: { labels: labels, datasets: [{ label: 'Total', data: data, backgroundColor: color }] }, plugins: [whiteBackground], options: { responsive: false, animation: { onComplete: function() { try { const b64 = canvas.toDataURL('image/png'); tempChart.destroy(); resolve(b64); } catch (e) { reject(e); } } }, plugins: { title: { display: true, text: title, font: {size: 18} }, legend: {display: (type==='pie')} } } });
                });
            };

            const addSection = async (ws, title, obj, chartConfig, startRow) => {
                ws.getCell(`A${startRow}`).value = title; ws.getCell(`A${startRow}`).font = {bold:true, size:14, color:{argb:'FF7A123A'}};
                ws.getCell(`A${startRow+1}`).value = "Concepto"; ws.getCell(`B${startRow+1}`).value = "Total";
                ws.getCell(`A${startRow+1}`).fill = {type:'pattern', pattern:'solid', fgColor:{argb:'FF333333'}}; ws.getCell(`A${startRow+1}`).font = {color:{argb:'FFFFFFFF'}};
                ws.getCell(`B${startRow+1}`).fill = {type:'pattern', pattern:'solid', fgColor:{argb:'FF333333'}}; ws.getCell(`B${startRow+1}`).font = {color:{argb:'FFFFFFFF'}};
                let cr = startRow + 2; let gl = [], gd = [];
                Object.entries(obj).sort((a,b) => ((typeof b[1]==='object'?b[1].H+b[1].M:b[1])-(typeof a[1]==='object'?a[1].H+a[1].M:a[1]))).forEach(([k,v]) => {
                    let val = typeof v === 'object' ? v.H+v.M : v;
                    ws.getCell(`A${cr}`).value = k; ws.getCell(`B${cr}`).value = val; cr++;
                    if(gl.length < 10) { gl.push(k); gd.push(val); }
                });
                if(gl.length > 0 && chartConfig) {
                    try { const imgB64 = await generarImagenFantasma(chartConfig.type, gl, gd, chartConfig.color, title); const imgId = workbook.addImage({ base64: imgB64, extension: 'png' }); ws.addImage(imgId, { tl: { col: 3, row: startRow }, ext: { width: 500, height: 300 } }); } catch(e) { console.warn("Error generando gráfico excel:", e); }
                }
                return Math.max(cr, startRow + 16) + 2;
            };

            const wsG = workbook.addWorksheet('Generales'); wsG.getColumn(1).width=40; let r = 1;
            r = await addSection(wsG, "SEXO", s.General.Sexo, {type:'pie',color:['#0d6efd','#dc3545','#ffc107']}, r);
            r = await addSection(wsG, "EVENTOS", s.General.Evento, {type:'bar',color:'#7a123a'}, r);
            r = await addSection(wsG, "EDAD", s.General.Edad, {type:'bar',color:'#0d6efd'}, r);
            r = await addSection(wsG, "TURNOS", s.General.Turno, {type:'bar',color:'#198754'}, r);
            r = await addSection(wsG, "SERVICIOS", s.General.Servicio, {type:'bar',color:'#0288d1'}, r);

            const addT = async (n, d, c, f) => {
                const ws = workbook.addWorksheet(n); ws.getColumn(1).width=45; let rx=1;
                rx = await addSection(ws, `TOP ÁREAS (${n})`, d.Servicio, {type:'bar',color:c}, rx);
                const st = Object.entries(d.Servicio).sort((a,b)=>b[1]-a[1]);
                if(st.length>0 && f) {
                    const top = st[0][0]; let dc={};
                    rows.forEach(rw=>{ const cl=(x)=>String(x).replace(/<[^>]*>?/gm,'').trim()||'NULO'; let eu=cl(rw[COL.EVENTO]).toUpperCase(); if(((n==='Adversos'&&eu.includes('ADVERSO'))||(n==='Cuasifallas'&&eu.includes('CUASI'))) && cl(rw[COL.SERVICIO])===top) dc[cl(rw[COL.DEFINICION])]=(dc[cl(rw[COL.DEFINICION])]||0)+1; });
                    rx = await addSection(ws, `ANÁLISIS CRÍTICO: ${top}`, dc, {type:'bar',color:'#212529'}, rx);
                }
                rx = await addSection(ws, `CAUSAS GLOBALES (${n})`, d.Definicion, {type:'bar',color:c}, rx);
            };
            await addT('Adversos', s.Adverso, '#dc3545', true);
            await addT('Cuasifallas', s.Cuasi, '#ffc107', true);
            await addT('Centinelas', s.Centinela, '#212529', false);
            const buff = await workbook.xlsx.writeBuffer();
            saveAs(new Blob([buff]), 'Reporte_VENCER_Final.xlsx');
        } catch (err) { console.error(err); alert("Error al generar Excel: " + err.message); } 
        finally { btn.innerHTML = originalText; btn.disabled = false; }
    });

    $('#filtroAnio, #filtroMesInicio, #filtroMesFin').on('change', function() { tabla.draw(); generarGraficos(); });
    $('#modalGraficos').on('shown.bs.modal', generarGraficos);
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', generarGraficos);
    $(document).on('click', '.toggle-text', function() { $(this).parent().find('.text-full, .text-short').toggleClass('d-none'); });

    initFiltros();
});