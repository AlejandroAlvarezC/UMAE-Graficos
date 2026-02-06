document.addEventListener('DOMContentLoaded', function() {
    console.log("🚀 VENCER: Módulo cargado - Filtro Rango Fechas Activo");

    // CONFIGURACIÓN DE COLUMNAS
    const COL = { 
        EVENTO: 1, EDAD: 4, SEXO: 5, 
        FECHA: 7, // Columna fecha evento
        TURNO: 9, SERVICIO: 10, DEFINICION: 13, ANIO: 16 
    };

    // 1. INICIALIZAR TABLA
    const tabla = $('#tabla-vencer').DataTable({
        pageLength: 10, order: [[0, 'asc']],
        deferRender: true, processing: true,
        language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
    });

    // 2. PREPARAR FILTROS
    // Llenar Año automáticamente
    const fAnio = document.getElementById('filtroAnio');
    if(fAnio) {
        const anios = [...new Set(tabla.column(COL.ANIO).data().toArray())]
            .filter(x => x && x !== 'NULO').sort().reverse();
        fAnio.innerHTML = '<option value="todos">Todos los años</option>';
        anios.forEach(a => fAnio.append(new Option(a, a)));
    }

    // Llenar Columnas (Select2)
    function llenarFiltros() {
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
    }
    $('.select-personalizado').not('#filtroAnio, #filtroMesInicio, #filtroMesFin').select2({ placeholder: 'Filtrar...', allowClear: true, width: '100%' });
    $('.select-personalizado').on('change', () => { tabla.draw(); generarGraficos(); });

    // 3. MOTOR DE BÚSQUEDA (RANGO DE MESES)
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'tabla-vencer') return true;
        
        const anioSel = $('#filtroAnio').val();
        const mesIni = parseInt($('#filtroMesInicio').val()) || 1;
        const mesFin = parseInt($('#filtroMesFin').val()) || 12;

        const anioRow = data[COL.ANIO] || "";
        const fechaRow = data[COL.FECHA] || "";

        // Validar Año
        let okAnio = (anioSel === 'todos' || anioRow === anioSel);
        
        // Validar Rango Meses (Solo si coincide el año)
        let okMes = true;
        if (okAnio) {
            let mesRow = 0;
            if (fechaRow.includes('-')) mesRow = parseInt(fechaRow.split('-')[1]); 
            else if (fechaRow.includes('/')) mesRow = parseInt(fechaRow.split('/')[1]);
            
            if (mesRow < mesIni || mesRow > mesFin) okMes = false;
        }

        // Validar Columnas
        let okCols = true;
        const headers = ['Folio', 'Evento', 'Iniciales', 'NSS', 'Edad', 'Sexo', 'Diagnostico', 'FechaEvento', 'FechaNotificacion', 'Turno', 'Servicio', 'Categoria', 'Proceso', 'Definicion', 'Descripcion', 'Estatus', 'Anio'];
        for (let i = 0; i < headers.length; i++) {
            const val = $(`#filter${headers[i]}`).val();
            if (val && val.length > 0 && !val.includes(data[i].trim() || 'NULO')) { okCols = false; break; }
        }

        return okAnio && okMes && okCols;
    });

    // Listeners principales
    $('#filtroAnio, #filtroMesInicio, #filtroMesFin').on('change', function() { 
        tabla.draw(); generarGraficos(); 
    });

    llenarFiltros();
    $('#filtrosContainer').show();

    // 4. MOTOR GRÁFICO
    let charts = {};
    function generarGraficos() {
        setTimeout(() => {
            const rows = tabla.rows({ search: 'applied' }).data().toArray();
            const lbl = document.getElementById('lblTotalEventos');
            if(lbl) lbl.textContent = rows.length;
            if (rows.length === 0) return;

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

            const draw = (id, t, l, d, c, opts={}) => {
                const el = document.getElementById(id); if(!el) return;
                if(charts[id]) charts[id].destroy();
                charts[id] = new Chart(el.getContext('2d'), { type: t, data: { labels: l, datasets: [{ label: 'Total', data: d, backgroundColor: c }] }, options: { responsive: true, maintainAspectRatio: false, animation: false, indexAxis: 'y', ...opts } });
            };
            const drawTable = (id, obj, t) => {
                const el = document.getElementById(id); if(!el) return;
                const ent = Object.entries(obj).sort((a,b)=>((typeof b[1]==='object'?b[1].H+b[1].M:b[1])-(typeof a[1]==='object'?a[1].H+a[1].M:a[1])));
                let h = `<table class="table table-sm table-striped table-bordered text-center small mb-0"><thead class="table-dark"><tr><th>${t}</th><th>Total</th></tr></thead><tbody>`;
                ent.forEach(([k,v])=>{ h+=`<tr><td class="text-start">${k}</td><td class="fw-bold">${typeof v==='object'?v.H+v.M:v}</td></tr>`; });
                el.innerHTML = h+'</tbody></table>';
            };

            // Dibujar
            draw('chartSexoGen', 'pie', Object.keys(s.General.Sexo), Object.values(s.General.Sexo), ['#0d6efd','#dc3545','#ffc107'], {indexAxis:'x'}); drawTable('tablaSexoGen', s.General.Sexo, 'Sexo');
            draw('chartEventosGen', 'bar', Object.keys(s.General.Evento), Object.values(s.General.Evento), '#7a123a', {indexAxis:'x'}); drawTable('tablaEventosGen', s.General.Evento, 'Evento');
            draw('chartTurnoGen', 'bar', Object.keys(s.General.Turno), Object.values(s.General.Turno), '#198754'); drawTable('tablaTurnoGen', s.General.Turno, 'Turno');
            const ts = Object.entries(s.General.Servicio).sort((a,b)=>b[1]-a[1]).slice(0,10); draw('chartTopServiciosGen', 'bar', ts.map(x=>x[0]), ts.map(x=>x[1]), '#0288d1'); drawTable('tablaTopServiciosGen', s.General.Servicio, 'Servicio');
            if(document.getElementById('chartEdadSexoGen')) {
                const eds = Object.keys(s.General.Edad).sort((a,b)=>parseInt(a)-parseInt(b));
                if(charts['chartEdadSexoGen']) charts['chartEdadSexoGen'].destroy();
                charts['chartEdadSexoGen'] = new Chart(document.getElementById('chartEdadSexoGen'), { type: 'bar', data: { labels: eds, datasets: [{ label: 'H', data: eds.map(e=>s.General.Edad[e].H), backgroundColor: '#0d6efd'}, {label: 'M', data: eds.map(e=>s.General.Edad[e].M), backgroundColor: '#dc3545'}] }, options: { responsive: true, maintainAspectRatio: false, scales: {x:{stacked:true}, y:{stacked:true}} } });
                drawTable('tablaEdadSexoGen', s.General.Edad, 'Edad');
            }

            // Drill Downs
            const runDD = (k, c, ids) => {
                const d = s[k];
                const ts = Object.entries(d.Servicio).sort((a,b)=>b[1]-a[1]);
                draw(ids.sc, 'bar', ts.slice(0,10).map(x=>x[0]), ts.slice(0,10).map(x=>x[1]), c); drawTable(ids.st, d.Servicio, 'Servicio');
                const td = Object.entries(d.Definicion).sort((a,b)=>b[1]-a[1]).slice(0,10);
                draw(ids.dc, 'bar', td.map(x=>x[0]), td.map(x=>x[1]), c); drawTable(ids.dt, d.Definicion, 'Causa');
                if(ts.length>0) {
                    const top = ts[0][0];
                    if(document.getElementById(ids.l)) document.getElementById(ids.l).textContent = top;
                    let dc = {};
                    rows.forEach(r => {
                        const cl = (x)=>String(x).replace(/<[^>]*>?/gm,'').trim()||'NULO';
                        let evU = cl(r[COL.EVENTO]).toUpperCase();
                        if(((k==='Adverso'&&evU.includes('ADVERSO'))||(k==='Cuasi'&&evU.includes('CUASI'))) && cl(r[COL.SERVICIO])===top) dc[cl(r[COL.DEFINICION])]=(dc[cl(r[COL.DEFINICION])]||0)+1;
                    });
                    const tdd = Object.entries(dc).sort((a,b)=>b[1]-a[1]).slice(0,10);
                    draw(ids.ddc, 'bar', tdd.map(x=>x[0]), tdd.map(x=>x[1]), '#212529'); drawTable(ids.ddt, dc, 'Causa Específica');
                } else { if(document.getElementById(ids.l)) document.getElementById(ids.l).textContent="Sin Datos"; drawTable(ids.ddt, {}, 'Sin Datos'); }
            };
            runDD('Adverso', '#dc3545', {sc:'chartServicioAdv',st:'tablaServicioAdv',ddc:'chartDrillDownAdv',ddt:'tablaDrillDownAdv',dc:'chartDefinicionAdv',dt:'tablaDefinicionAdv',l:'lblTopAreaAdv'});
            runDD('Cuasi', '#ffc107', {sc:'chartServicioCuasi',st:'tablaServicioCuasi',ddc:'chartDrillDownCuasi',ddt:'tablaDrillDownCuasi',dc:'chartDefinicionCuasi',dt:'tablaDefinicionCuasi',l:'lblTopAreaCuasi'});

            const tcs = Object.entries(s.Centinela.Servicio).sort((a,b)=>b[1]-a[1]).slice(0,10);
            const tcd = Object.entries(s.Centinela.Definicion).sort((a,b)=>b[1]-a[1]).slice(0,10);
            draw('chartServicioCent', 'bar', tcs.map(x=>x[0]), tcs.map(x=>x[1]), '#212529'); drawTable('tablaServicioCent', s.Centinela.Servicio, 'Servicio');
            draw('chartDefinicionCent', 'bar', tcd.map(x=>x[0]), tcd.map(x=>x[1]), '#212529'); drawTable('tablaDefinicionCent', s.Centinela.Definicion, 'Causa');
        }, 200);
    }

    // 5. EXCEL
    $(document).on('click', '#btnDescargarExcelStats', async function() {
        const btn = this; const ot = btn.innerHTML; btn.innerHTML = "⏳ Generando..."; btn.disabled = true;
        try {
            const rows = tabla.rows({ search: 'applied' }).data().toArray();
            if (rows.length === 0) throw new Error("No hay datos");
            
            // Lógica de datos para Excel
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
            const genImg = async (type, l, d, c, t) => { const cv = document.createElement('canvas'); cv.width=800; cv.height=400; const x = cv.getContext('2d'); x.fillStyle='#fff'; x.fillRect(0,0,800,400); const ch = new Chart(x,{type:type,data:{labels:l,datasets:[{data:d,backgroundColor:c}]},options:{animation:false,plugins:{title:{display:true,text:t,font:{size:18}}}}}); const b=cv.toDataURL(); ch.destroy(); return b; };
            const addS = async (ws, t, o, c, r) => { ws.getCell(`A${r}`).value=t; ws.getCell(`A${r}`).font={bold:true,size:14,color:{argb:'FF7A123A'}}; ws.getCell(`A${r+1}`).value="Concepto"; ws.getCell(`B${r+1}`).value="Total"; ws.getCell(`A${r+1}`).fill={type:'pattern',pattern:'solid',fgColor:{argb:'FF333333'}}; ws.getCell(`A${r+1}`).font={color:{argb:'FFFFFFFF'}}; ws.getCell(`B${r+1}`).fill={type:'pattern',pattern:'solid',fgColor:{argb:'FF333333'}}; ws.getCell(`B${r+1}`).font={color:{argb:'FFFFFFFF'}}; let cr=r+2; let gl=[],gd=[]; Object.entries(o).sort((a,b)=>((typeof b[1]==='object'?b[1].H+b[1].M:b[1])-(typeof a[1]==='object'?a[1].H+a[1].M:a[1]))).forEach(([k,v])=>{ let val=typeof v==='object'?v.H+v.M:v; ws.getCell(`A${cr}`).value=k; ws.getCell(`B${cr}`).value=val; cr++; if(gl.length<10){gl.push(k);gd.push(val);} }); if(gl.length>0&&c){try{const i=await genImg(c.type,gl,gd,c.color,t);const id=workbook.addImage({base64:i,extension:'png'});ws.addImage(id,{tl:{col:3,row:r},ext:{width:500,height:300}});}catch(e){}} return Math.max(cr,r+16)+2; };

            const wsG = workbook.addWorksheet('Generales'); wsG.getColumn(1).width=40; let r=1;
            r = await addS(wsG, "SEXO", s.General.Sexo, {type:'pie',color:['#0d6efd','#dc3545','#ffc107']}, r);
            r = await addS(wsG, "EVENTOS", s.General.Evento, {type:'bar',color:'#7a123a'}, r);
            r = await addS(wsG, "EDAD", s.General.Edad, {type:'bar',color:'#0d6efd'}, r);
            r = await addS(wsG, "TURNOS", s.General.Turno, {type:'bar',color:'#198754'}, r);
            r = await addS(wsG, "SERVICIOS", s.General.Servicio, {type:'bar',color:'#0288d1'}, r);

            const addT = async (n, d, c, f) => {
                const ws = workbook.addWorksheet(n); ws.getColumn(1).width=45; let rx=1;
                rx = await addS(ws, `TOP ÁREAS (${n})`, d.Servicio, {type:'bar',color:c}, rx);
                const st = Object.entries(d.Servicio).sort((a,b)=>b[1]-a[1]);
                if(st.length>0 && f) {
                    const top = st[0][0]; let dc={};
                    rows.forEach(rw=>{ const cl=(x)=>String(x).replace(/<[^>]*>?/gm,'').trim()||'NULO'; let eu=cl(rw[COL.EVENTO]).toUpperCase(); if(((n==='Adversos'&&eu.includes('ADVERSO'))||(n==='Cuasifallas'&&eu.includes('CUASI'))) && cl(rw[COL.SERVICIO])===top) dc[cl(rw[COL.DEFINICION])]=(dc[cl(rw[COL.DEFINICION])]||0)+1; });
                    rx = await addS(ws, `ANÁLISIS CRÍTICO: ${top}`, dc, {type:'bar',color:'#212529'}, rx);
                }
                rx = await addS(ws, `CAUSAS GLOBALES (${n})`, d.Definicion, {type:'bar',color:c}, rx);
            };
            await addT('Adversos', s.Adverso, '#dc3545', true);
            await addT('Cuasifallas', s.Cuasi, '#ffc107', true);
            await addT('Centinelas', s.Centinela, '#212529', false);

            const buff = await workbook.xlsx.writeBuffer();
            saveAs(new Blob([buff]), 'Reporte_VENCER_Final.xlsx');
        } catch (err) { console.error(err); alert("Error excel."); } finally { btn.innerHTML = ot; btn.disabled = false; }
    });

    $('#modalGraficos').on('shown.bs.modal', generarGraficos);
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', generarGraficos);
    $(document).on('click', '.toggle-text', function() { $(this).parent().find('.text-full, .text-short').toggleClass('d-none'); });
});