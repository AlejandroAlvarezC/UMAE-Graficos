import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';
import html2canvas from 'html2canvas';

// Función para pausar y permitir que las animaciones de las gráficas terminen
const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

// ==========================================
// 1. FUNCIÓN AUXILIAR (Datos de las Tablas)
// ==========================================
const obtenerResumenAgregado = (datos, campoRequerido) => {
    if (!datos || datos.length === 0) return [];
    
    const resumen = {};
    let tPV = 0, tSub = 0;

    datos.forEach(d => {
        // Búsqueda inteligente de la llave (soporta mayúsculas, minúsculas y alias comunes)
        let valor = 'SIN ESPECIFICAR';
        if (d[campoRequerido] !== undefined) valor = d[campoRequerido];
        else if (d[campoRequerido.toUpperCase()] !== undefined) valor = d[campoRequerido.toUpperCase()];
        else if (campoRequerido === 'diagnostico' && (d.desc_diag || d.DESC_DIAG)) valor = d.desc_diag || d.DESC_DIAG;
        else if (campoRequerido === 'medico' && (d.nombre_medico || d.NOMBRE_MEDICO)) valor = d.nombre_medico || d.NOMBRE_MEDICO;
        else if (campoRequerido === 'especialidad' && (d.area || d.AREA)) valor = d.area || d.AREA;

        if (!valor || String(valor).trim() === '') valor = 'SIN ESPECIFICAR';

        if (!resumen[valor]) resumen[valor] = { pv: 0, sub: 0 };

        const esPV = String(d.primera_vez || d.PRIMERA_VEZ || '').toLowerCase().includes('primera') || 
                     String(d.primera_vez || d.PRIMERA_VEZ) === '1';

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
        categoria: 'TOTAL GENERAL', 
        pv: tPV, 
        sub: tSub, 
        indice: tPV > 0 ? Number((tSub / tPV).toFixed(2)) : 0, 
        total: tPV + tSub 
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

        // ext: 500x300 es aproximadamente 15 filas de alto y 7 columnas de ancho en Excel
        sheet.addImage(imageId, {
            tl: { col: excelCell.col, row: excelCell.row },
            ext: { width: 500, height: 300 }
        });
    } catch (err) { console.error(`Error al capturar ${elementId}:`, err); }
};

// ==========================================
// 3. FUNCIÓN PRINCIPAL DE EXPORTACIÓN
// ==========================================
export const exportarReporteCompleto = async (externa, paramedicos, urgencias) => {
    // IMPORTANTE: Pausa para que las barras se dibujen en la pantalla antes de la "foto"
    await sleep(500);

    const workbook = new ExcelJS.Workbook();

    // -- MOTOR CREADOR DE BLOQUES (TABLA + IMAGEN) --
    const agregarSeccion = async (sheet, tituloTabla, campoBD, elementId, datos) => {
        // Encontrar la fila libre más abajo
        const startRow = sheet.rowCount > 0 ? sheet.rowCount + 2 : 1;
        let endRowTabla = startRow;

        // 1. Escribir Tabla (si aplica)
        if (campoBD && datos) {
            const filas = obtenerResumenAgregado(datos, campoBD);
            
            // Encabezado
            const header = sheet.getRow(startRow);
            header.values = [tituloTabla, '1RA VEZ', 'SUBSEC.', 'ÍNDICE', 'TOTAL'];
            header.font = { bold: true, color: { argb: 'FFFFFF' } };
            header.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: '1E293B' } };

            // Filas de Datos
            filas.forEach((f, idx) => {
                const row = sheet.getRow(startRow + 1 + idx);
                row.values = [f.categoria, f.pv, f.sub, f.indice, f.total];
                
                // Resaltar totales
                if (f.categoria === 'TOTAL GENERAL') {
                    row.font = { bold: true };
                    row.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'F1F5F9' } };
                }
            });
            endRowTabla = sheet.rowCount; // Guardamos donde acabó la tabla
        }

        // 2. Insertar Gráfico (A partir de la columna F = índice 6)
        if (elementId) {
            // ExcelJS usa base 0 para las imágenes, por lo que startRow - 1 alinea la imagen con la fila actual
            await agregarGraficoExcel(workbook, sheet, elementId, { col: 6, row: startRow - 1 });
        }

        // 3. Calcular espacio de seguridad
        // Un gráfico de 300px ocupa aprox 16-17 filas de Excel.
        const graficoOcupaHasta = startRow + 16;
        const filaFinalSeccion = Math.max(endRowTabla, graficoOcupaHasta);

        // Agregamos filas vacías para empujar el cursor (sheet.rowCount) hacia abajo
        while (sheet.rowCount < filaFinalSeccion) { sheet.addRow([]); }
        sheet.addRow([]); // Espacio en blanco extra entre secciones
    };

    // Función para setear el ancho de columnas (Col A hasta Col E)
    const setearColumnas = (sheet) => {
        sheet.getColumn(1).width = 40; // Nombre (Diagnóstico/Médico)
        sheet.getColumn(2).width = 12; // 1RA VEZ
        sheet.getColumn(3).width = 12; // SUBSEC
        sheet.getColumn(4).width = 10; // INDICE
        sheet.getColumn(5).width = 15; // TOTAL
    };

    // --- HOJA 1: CONSULTA EXTERNA ---
    const sheetEx = workbook.addWorksheet('Consulta Externa');
    setearColumnas(sheetEx);
    await agregarSeccion(sheetEx, 'METAS', null, 'graficoE_1', externa); // null = Solo inserta imagen
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
    await agregarSeccion(sheetPa, 'ÁREA (DETALLE)', null, 'graficoP_3', paramedicos); // Solo la imagen ancha
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

    // --- GUARDAR Y DESCARGAR ---
    const buffer = await workbook.xlsx.writeBuffer();
    saveAs(new Blob([buffer]), `Reporte_Ejecutivo_${new Date().toLocaleDateString().replace(/\//g, '-')}.xlsx`);
};