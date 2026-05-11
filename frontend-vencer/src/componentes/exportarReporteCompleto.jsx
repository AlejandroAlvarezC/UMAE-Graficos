import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';
import html2canvas from 'html2canvas';

// Función para pausar y permitir que las animaciones de las gráficas terminen
const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

// ==========================================
// 1. FUNCIÓN AUXILIAR (TRADUCTORA)
// ==========================================
export const obtenerResumenAgregado = (datos, campoRequerido, diccionarios = {}) => {
    if (!datos || datos.length === 0) return [];
    
    const dictMedicos = diccionarios.medicos || {};
    const dictCIE10 = diccionarios.cie10 || {};
    const resumen = {};
    let tPV = 0, tSub = 0;

    datos.forEach(d => {
        let valor = 'SIN ESPECIFICAR';
        
        if (campoRequerido === 'medico') {
            // Limpieza de matrícula para asegurar el "match"
            const mat = d.matricula_medico || d.MATRICULA_MEDICO || d.medico || '';
            const matLimpia = String(mat).trim().replace('.0', '').replace(/\s/g, '');
            // Prioridad: 1. Diccionario, 2. Valor real ya traducido, 3. Matrícula cruda
            valor = dictMedicos[matLimpia] || d.medico_real || (matLimpia ? `Mat. ${matLimpia}` : 'SIN ESPECIFICAR');
        } 
        else if (campoRequerido === 'diagnostico') {
            const cod = String(d.diagnostico_principal || d.DIAGNOSTICO_PRINCIPAL || d.diagnostico || '').trim().toUpperCase();
            valor = dictCIE10[cod] || d.diagnostico_real || (cod ? `Cód. ${cod}` : 'SIN ESPECIFICAR');
        } 
        else if (campoRequerido === 'especialidad') {
            valor = d.especialidad_real || d.especialidad || d.ESPECIALIDAD || 'SIN ESPECIFICAR';
        }
        else {
            valor = d[campoRequerido] || d[campoRequerido.toUpperCase()] || 'SIN ESPECIFICAR';
        }

        if (!resumen[valor]) resumen[valor] = { pv: 0, sub: 0 };
        
        const rawPV = String(d.primera_vez || d.PRIMERA_VEZ || d.id_tipo_consulta || '').toLowerCase();
        const esPV = rawPV.includes('primera') || rawPV === '1';

        if (esPV) { resumen[valor].pv++; tPV++; }
        else { resumen[valor].sub++; tSub++; }
    });

    const filas = Object.entries(resumen).map(([nombre, conteo]) => ({
        categoria: nombre,
        pv: conteo.pv,
        sub: conteo.sub,
        indice: conteo.pv > 0 ? Number((conteo.sub / conteo.pv).toFixed(2)) : (conteo.sub > 0 ? '∞' : 0),
        total: conteo.pv + conteo.sub
    }));

    filas.sort((a, b) => b.total - a.total);
    filas.push({ 
        categoria: 'TOTAL GENERAL', pv: tPV, sub: tSub, 
        indice: tPV > 0 ? Number((tSub / tPV).toFixed(2)) : 0, total: tPV + tSub 
    });
    
    return filas;
};

// ==========================================
// 2. FUNCIÓN PARA CAPTURAR GRÁFICOS
// ==========================================
const agregarGraficoExcel = async (workbook, sheet, elementId, excelCell) => {
    const elemento = document.getElementById(elementId);
    if (!elemento) return;

    try {
        const canvas = await html2canvas(elemento, { scale: 2, logging: false, useCORS: true, backgroundColor: '#ffffff' });
        const imageId = workbook.addImage({
            base64: canvas.toDataURL('image/png'),
            extension: 'png',
        });

        sheet.addImage(imageId, {
            tl: { col: excelCell.col, row: excelCell.row },
            ext: { width: 500, height: 300 }
        });
    } catch (err) { console.error(`Error al capturar ${elementId}:`, err); }
};

// ==========================================
// 3. FUNCIÓN PRINCIPAL DE EXPORTACIÓN
// ==========================================
export const exportarReporteCompleto = async (externa, paramedicos, urgencias, diccionarios = {}) => {
    await sleep(500);
    const workbook = new ExcelJS.Workbook();

    console.log("Procesando Excel con diccionarios:", {
        medicos: Object.keys(diccionarios.medicos || {}).length,
        cie10: Object.keys(diccionarios.cie10 || {}).length
    });

    // -- MOTOR CREADOR DE BLOQUES --
    const agregarSeccion = async (sheet, tituloTabla, campoBD, elementId, datos) => {
        const startRow = sheet.rowCount > 0 ? sheet.rowCount + 2 : 1;
        let endRowTabla = startRow;

        if (campoBD && datos) {
            // ¡CORRECCIÓN AQUÍ!: Pasamos 'diccionarios' a la función de resumen
            const filas = obtenerResumenAgregado(datos, campoBD, diccionarios);
            
            const header = sheet.getRow(startRow);
            header.values = [tituloTabla, '1RA VEZ', 'SUBSEC.', 'ÍNDICE', 'TOTAL'];
            header.font = { bold: true, color: { argb: 'FFFFFF' } };
            header.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: '1E293B' } };

            filas.forEach((f, idx) => {
                const row = sheet.getRow(startRow + 1 + idx);
                row.values = [f.categoria, f.pv, f.sub, f.indice, f.total];
                if (f.categoria === 'TOTAL GENERAL') {
                    row.font = { bold: true };
                    row.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'F1F5F9' } };
                }
            });
            endRowTabla = sheet.rowCount;
        }

        if (elementId) {
            await agregarGraficoExcel(workbook, sheet, elementId, { col: 6, row: startRow - 1 });
        }

        const graficoOcupaHasta = startRow + 16;
        const filaFinalSeccion = Math.max(endRowTabla, graficoOcupaHasta);
        while (sheet.rowCount < filaFinalSeccion) { sheet.addRow([]); }
        sheet.addRow([]);
    };

    const setearColumnas = (sheet) => {
        sheet.getColumn(1).width = 45;
        sheet.getColumn(2).width = 12;
        sheet.getColumn(3).width = 12;
        sheet.getColumn(4).width = 10;
        sheet.getColumn(5).width = 15;
    };

    // --- HOJA 1: CONSULTA EXTERNA ---
    const sheetEx = workbook.addWorksheet('Consulta Externa');
    setearColumnas(sheetEx);
    await agregarSeccion(sheetEx, 'METAS', null, 'graficoE_1', externa);
    await agregarSeccion(sheetEx, 'DIVISIÓN', 'division', 'graficoE_2', externa);
    await agregarSeccion(sheetEx, 'TURNO', 'turno', 'graficoE_3', externa);
    await agregarSeccion(sheetEx, 'MÉDICO', 'medico', 'graficoE_4', externa);
    await agregarSeccion(sheetEx, 'DIAGNÓSTICO', 'diagnostico', 'graficoE_5', externa);
    await agregarSeccion(sheetEx, 'CONSULTORIO', 'consultorio', 'graficoE_6', externa);

    // --- HOJA 2: PARAMÉDICOS ---
    const sheetPa = workbook.addWorksheet('Paramédicos');
    setearColumnas(sheetPa);
    await agregarSeccion(sheetPa, 'TURNO', 'turno', 'graficoP_1', paramedicos);
    await agregarSeccion(sheetPa, 'ÁREA PARAMÉDICA', 'especialidad', 'graficoP_2', paramedicos);
    await agregarSeccion(sheetPa, 'ÁREA (DETALLE)', null, 'graficoP_3', paramedicos);
    await agregarSeccion(sheetPa, 'PERSONAL / MÉDICO', 'medico', 'graficoP_4', paramedicos);
    await agregarSeccion(sheetPa, 'DIAGNÓSTICO', 'diagnostico', 'graficoP_5', paramedicos);
    await agregarSeccion(sheetPa, 'CONSULTORIO', 'consultorio', 'graficoP_6', paramedicos);

    // --- HOJA 3: URGENCIAS ---
    const sheetUr = workbook.addWorksheet('Urgencias');
    setearColumnas(sheetUr);
    await agregarSeccion(sheetUr, 'TURNO', 'turno', 'graficoU_1', urgencias);
    await agregarSeccion(sheetUr, 'ÁREA URGENCIAS', 'especialidad', 'graficoU_2', urgencias);
    await agregarSeccion(sheetUr, 'MÉDICO', 'medico', 'graficoU_3', urgencias);
    await agregarSeccion(sheetUr, 'DIAGNÓSTICO', 'diagnostico', 'graficoU_4', urgencias);
    await agregarSeccion(sheetUr, 'CONSULTORIO', 'consultorio', 'graficoU_5', urgencias);

    const buffer = await workbook.xlsx.writeBuffer();
    saveAs(new Blob([buffer]), `Reporte_Ejecutivo_${new Date().toLocaleDateString().replace(/\//g, '-')}.xlsx`);
};