import React, { useState, useMemo, useEffect } from 'react';
import * as XLSX from 'xlsx';
import { Line } from 'react-chartjs-2';
import { FileText, Loader2, Calendar } from 'lucide-react';
import axios from 'axios';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

const MESES = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];

const IndicadoresHosp = ({ rolUsuario = 'admin', tabActiva = 'mensual' }) => { 
    const [datosHosp, setDatosHosp] = useState([]);
    const [guardando, setGuardando] = useState(false);
    const [cargandoBD, setCargandoBD] = useState(false);
    const [indicadorActivo, setIndicadorActivo] = useState('HOSP_01');
    const [anioSeleccionado, setAnioSeleccionado] = useState(new Date().getFullYear());

    // ==========================================
    // CONFIGURACIÓN DE INDICADORES (01 AL 10)
    // ==========================================
    const configIndicadores = {
        'HOSP_01': { nombreCorto: 'Ocupación en Observación', labelNumerador: 'Horas Paciente', labelDenominador: 'Horas Cama', evaluarColor: (n) => (n <= 85 ? 'text-emerald-500' : n < 90 ? 'text-amber-500' : 'text-rose-500'), evaluarColorTarjeta: (n) => (n <= 85 ? 'text-emerald-300' : n < 90 ? 'text-amber-300' : 'text-rose-300') },
        'HOSP_02': { nombreCorto: 'Estancia Prolongada (>12h)', labelNumerador: 'Pacientes > 12h', labelDenominador: 'Total Egresados', evaluarColor: (n) => (n <= 10 ? 'text-emerald-500' : n <= 20 ? 'text-amber-500' : 'text-rose-500'), evaluarColorTarjeta: (n) => (n <= 10 ? 'text-emerald-300' : n <= 20 ? 'text-amber-300' : 'text-rose-300') },
        'HOSP_03': { nombreCorto: 'Oportunidad Consulta (20 días)', labelNumerador: 'Citas programadas < 20 días', labelDenominador: 'Pacientes Referidos', evaluarColor: (n) => (n >= 95 ? 'text-emerald-500' : n > 78 ? 'text-amber-500' : 'text-rose-500'), evaluarColorTarjeta: (n) => (n >= 95 ? 'text-emerald-300' : n > 78 ? 'text-amber-300' : 'text-rose-300') },
        'HOSP_04': { nombreCorto: 'Promedio de Consultas Diarias', labelNumerador: 'Total Consultas (C64)', labelDenominador: 'Total Consultorios (C65)', labelAuxiliar: 'Días Hábiles (C66)', evaluarColor: (n) => (n >= 20 ? 'text-emerald-500' : n >= 15.5 ? 'text-amber-500' : 'text-rose-500'), evaluarColorTarjeta: (n) => (n >= 20 ? 'text-emerald-300' : n >= 15.5 ? 'text-amber-300' : 'text-rose-300') },
        'HOSP_05': { nombreCorto: 'Ocupación Hospitalaria', labelNumerador: 'Total Días Paciente', labelDenominador: 'Total Días Cama', evaluarColor: (n) => (n >= 80 ? 'text-emerald-500' : n >= 70 ? 'text-amber-500' : 'text-rose-500'), evaluarColorTarjeta: (n) => (n >= 80 ? 'text-emerald-300' : n >= 70 ? 'text-amber-300' : 'text-rose-300') },
        'HOSP_06': { nombreCorto: 'Promedio Días Estancia', labelNumerador: 'Total Días Paciente', labelDenominador: 'Total Egresos', evaluarColor: (n) => (n <= 4 ? 'text-emerald-500' : n <= 5 ? 'text-amber-500' : 'text-rose-500'), evaluarColorTarjeta: (n) => (n <= 4 ? 'text-emerald-300' : n <= 5 ? 'text-amber-300' : 'text-rose-300') },
        'HOSP_07': { nombreCorto: 'Tasa de Mortalidad', labelNumerador: 'Egresos por Defunción', labelDenominador: 'Total de Egresos', evaluarColor: (n) => (n <= 1.5 ? 'text-emerald-500' : n <= 2.5 ? 'text-amber-500' : 'text-rose-500'), evaluarColorTarjeta: (n) => (n <= 1.5 ? 'text-emerald-300' : n <= 2.5 ? 'text-amber-300' : 'text-rose-300') },
        'HOSP_08': { nombreCorto: 'Cirugía Electiva (< 20 días)', labelNumerador: 'Cirugías < 20 días', labelDenominador: 'Total Solicitudes', evaluarColor: (n) => (n >= 80 ? 'text-emerald-500' : n >= 70 ? 'text-amber-500' : 'text-rose-500'), evaluarColorTarjeta: (n) => (n >= 80 ? 'text-emerald-300' : n >= 70 ? 'text-amber-300' : 'text-rose-300') },
        'HOSP_09': { nombreCorto: 'Ocupación de Quirófanos', labelNumerador: 'Tiempo Ocupación (hrs)', labelDenominador: 'Tiempo Disponible (hrs)', evaluarColor: (n) => (n >= 70 ? 'text-emerald-500' : n >= 60 ? 'text-amber-500' : 'text-rose-500'), evaluarColorTarjeta: (n) => (n >= 70 ? 'text-emerald-300' : n >= 60 ? 'text-amber-300' : 'text-rose-300') },
        'HOSP_10': { nombreCorto: 'Suspensión Cirugías Electivas', labelNumerador: 'Cirugías Suspendidas', labelDenominador: 'Total Programadas', evaluarColor: (n) => (n <= 5 ? 'text-emerald-500' : n <= 8 ? 'text-amber-500' : 'text-rose-500'), evaluarColorTarjeta: (n) => (n <= 5 ? 'text-emerald-300' : n <= 8 ? 'text-amber-300' : 'text-rose-300') }
    };

    const configActual = configIndicadores[indicadorActivo] || configIndicadores['HOSP_01'];

    // ==========================================
    // PETICIÓN A LA BASE DE DATOS
    // ==========================================
    const obtenerDatosDeBD = async () => {
        setCargandoBD(true);
        try {
            const respuesta = await axios.get(`/api/obtener_hosp.php?anio=${anioSeleccionado}&indicador=${indicadorActivo}`);
            if (respuesta.data && respuesta.data.success && respuesta.data.datos.length > 0) {
                setDatosHosp(respuesta.data.datos);
            } else {
                setDatosHosp([]); 
            }
        } catch (error) {
            console.error("Error al obtener datos:", error);
            setDatosHosp([]);
        } finally {
            setCargandoBD(false);
        }
    };

    useEffect(() => {
        obtenerDatosDeBD();
    }, [anioSeleccionado, indicadorActivo]);

    // ==========================================
    // LECTOR UNIVERSAL DE EXCEL
    // ==========================================
    const manejarSubidaHospExcel = (e) => {
        const archivo = e.target.files[0];
        if (!archivo) return;

        setGuardando(true);

        const lector = new FileReader();
        lector.onload = async (evt) => {
            const datosBinarios = evt.target.result;
            const libro = XLSX.read(datosBinarios, { type: 'binary' });
            
            let nombreHojaCorrecta = libro.SheetNames.find(name => name.toLowerCase().trim() === 'indicadores');
            if (!nombreHojaCorrecta) nombreHojaCorrecta = libro.SheetNames.find(name => name.toLowerCase().includes('indicador'));
            
            if (!nombreHojaCorrecta) {
                alert("Error: No se encontró la hoja 'indicadores'.");
                setGuardando(false);
                e.target.value = null;
                return;
            }

            const hoja = libro.Sheets[nombreHojaCorrecta];
            const filas = XLSX.utils.sheet_to_json(hoja, { header: 1 });

            let filasEncontradas = {
                'HOSP_01': { num: null, den: null },
                'HOSP_02': { num: null, den: null },
                'HOSP_03': { num: null, den: null },
                'HOSP_04': { f64: null, f65: null, f66: null },
                'HOSP_05': { num: null, den: null },
                'HOSP_06': { num: null, den: null },
                'HOSP_07': { num: null, den: null },
                'HOSP_08': { num: null, den: null },
                'HOSP_09': { num: null, den: null },
                'HOSP_10': { num: null, den: null }
            };

            const diccionarioBusqueda = {
                'HOSP_01': {
                    esNumerador: (t) => t.includes("HORAS PACIENTE") && t.includes("OBSERVACION"),
                    esDenominador: (t) => t.includes("HORAS CAMA") && t.includes("OBSERVACION")
                },
                'HOSP_02': {
                    esNumerador: (t) => t.includes("EGRESADOS") && t.includes("OBSERVACION") && t.includes("12 HORAS"),
                    esDenominador: (t) => t.includes("EGRESADOS") && t.includes("OBSERVACION") && !t.includes("12 HORAS")
                },
                'HOSP_03': {
                    esNumerador: (t) => t.includes("CITA PROGRAMADA") && t.includes("20 DIAS"),
                    esDenominador: (t) => t.includes("REFERIDOS") && t.includes("PROGRAMAR")
                }
            };

            let indicesMeses = { 0: 2, 1: 3, 2: 4, 3: 5, 4: 6, 5: 7, 6: 8, 7: 9, 8: 10, 9: 11, 10: 12, 11: 13 }; 
            let mesesEncontrados = false;

            filas.forEach((fila, i) => {
                if (!fila || fila.length === 0) return;
                
                const textoLimpio = fila.map(c => String(c).toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/\s+/g, " ").trim()).join(" ");
                
                if (!mesesEncontrados && textoLimpio.includes("ENE") && textoLimpio.includes("FEB")) {
                    fila.forEach((celda, colIndex) => {
                        if (!celda) return;
                        let textoCelda = String(celda).toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/\s+/g, " ").trim();
                        if (textoCelda.startsWith("ENE")) indicesMeses[0] = colIndex;
                        else if (textoCelda.startsWith("FEB")) indicesMeses[1] = colIndex;
                        else if (textoCelda.startsWith("MAR")) indicesMeses[2] = colIndex;
                        else if (textoCelda.startsWith("ABR")) indicesMeses[3] = colIndex;
                        else if (textoCelda.startsWith("MAY")) indicesMeses[4] = colIndex;
                        else if (textoCelda.startsWith("JUN")) indicesMeses[5] = colIndex;
                        else if (textoCelda.startsWith("JUL")) indicesMeses[6] = colIndex;
                        else if (textoCelda.startsWith("AGO")) indicesMeses[7] = colIndex;
                        else if (textoCelda.startsWith("SEP")) indicesMeses[8] = colIndex;
                        else if (textoCelda.startsWith("OCT")) indicesMeses[9] = colIndex;
                        else if (textoCelda.startsWith("NOV")) indicesMeses[10] = colIndex;
                        else if (textoCelda.startsWith("DIC")) indicesMeses[11] = colIndex;
                    });
                    mesesEncontrados = true;
                }

                // 1. Búsqueda por diccionario (HOSP 01, 02, 03)
                Object.keys(diccionarioBusqueda).forEach(indicadorClave => {
                    const reglas = diccionarioBusqueda[indicadorClave];
                    if (!filasEncontradas[indicadorClave].num && reglas.esNumerador(textoLimpio)) filasEncontradas[indicadorClave].num = fila;
                    if (!filasEncontradas[indicadorClave].den && reglas.esDenominador(textoLimpio)) filasEncontradas[indicadorClave].den = fila;
                });

                // 2. Candados Espaciales para HOSP 04 al 10 (Ignorando datos "Publicados" duplicados)
                if (!filasEncontradas['HOSP_04'].f64 && textoLimpio.includes("PROMEDIO DE CONSULTAS DIARIAS") && textoLimpio.includes("CONSULTORIO")) {
                    filasEncontradas['HOSP_04'].f64 = filas[i + 2] || null; 
                    filasEncontradas['HOSP_04'].f65 = filas[i + 3] || null; 
                    filasEncontradas['HOSP_04'].f66 = filas[i + 4] || null; 
                }

                if (!filasEncontradas['HOSP_05'].num && textoLimpio.includes("PORCENTAJE DE OCUPACION HOSPITALARIA") && textoLimpio.includes("TERCER NIVEL")) {
                    filasEncontradas['HOSP_05'].num = filas[i + 1] || null; 
                    filasEncontradas['HOSP_05'].den = filas[i + 2] || null; 
                }

                if (!filasEncontradas['HOSP_06'].num && textoLimpio.includes("PROMEDIO DE DIAS ESTANCIA") && textoLimpio.includes("TERCER NIVEL")) {
                    filasEncontradas['HOSP_06'].num = filas[i + 1] || null; 
                    filasEncontradas['HOSP_06'].den = filas[i + 2] || null; 
                }

                if (!filasEncontradas['HOSP_07'].num && textoLimpio.includes("TASA DE MORTALIDAD HOSPITALARIA") && textoLimpio.includes("TERCER NIVEL")) {
                    filasEncontradas['HOSP_07'].num = filas[i + 1] || null; 
                    filasEncontradas['HOSP_07'].den = filas[i + 2] || null; 
                }

                if (!filasEncontradas['HOSP_08'].num && textoLimpio.includes("CIRUGIA ELECTIVA NO CONCERTADA") && textoLimpio.includes("20 DIAS")) {
                    filasEncontradas['HOSP_08'].num = filas[i + 1] || null; 
                    filasEncontradas['HOSP_08'].den = filas[i + 2] || null; 
                }

                if (!filasEncontradas['HOSP_09'].num && textoLimpio.includes("PORCENTAJE DE OCUPACION DE LAS SALAS DE QUIROFANO") && textoLimpio.includes("DIURNO")) {
                    filasEncontradas['HOSP_09'].num = filas[i + 1] || null; 
                    filasEncontradas['HOSP_09'].den = filas[i + 2] || null; 
                }

                if (!filasEncontradas['HOSP_10'].num && textoLimpio.includes("PORCENTAJE DE SUSPENSION DE CIRUGIAS")) {
                    filasEncontradas['HOSP_10'].num = filas[i + 1] || null; 
                    filasEncontradas['HOSP_10'].den = filas[i + 2] || null; 
                }
            });

            // ==========================================
            // CÁLCULOS Y PREPARACIÓN DE BATCH
            // ==========================================
            let loteDatosGlobales = [];
            let indicadoresExitosos = [];

            Object.keys(filasEncontradas).forEach(indicadorClave => {
                const par = filasEncontradas[indicadorClave];
                
                // Procesar todos excepto HOSP 04
                if (indicadorClave !== 'HOSP_04' && par.num && par.den) {
                    indicadoresExitosos.push(indicadorClave);
                    MESES.forEach((mes, index) => {
                        const indiceColumna = indicesMeses[index];
                        let valNum = parseFloat(par.num[indiceColumna]);
                        if (isNaN(valNum)) valNum = 0;
                        let valDen = parseFloat(par.den[indiceColumna]);
                        if (isNaN(valDen) || valDen === 0) valDen = 1; 

                        // Si es HOSP_06 (Promedio) NO multiplicamos por 100. Los demás sí.
                        const porc = indicadorClave === 'HOSP_06' 
                            ? parseFloat((valNum / valDen).toFixed(1)) 
                            : parseFloat(((valNum / valDen) * 100).toFixed(1));
                        
                        loteDatosGlobales.push({ 
                            indicador: indicadorClave, 
                            mes: mes, 
                            numerador: valNum, 
                            denominador: valDen, 
                            auxiliar: 0,
                            porcentaje: porc 
                        });
                    });
                }
                
                // Procesar HOSP 04 Especial
                if (indicadorClave === 'HOSP_04' && par.f64 && par.f65 && par.f66) {
                    indicadoresExitosos.push(indicadorClave);
                    MESES.forEach((mes, index) => {
                        const indiceColumna = indicesMeses[index];
                        
                        let c64 = parseFloat(par.f64[indiceColumna]) || 0; 
                        let c65 = parseFloat(par.f65[indiceColumna]) || 1; 
                        let c66 = parseFloat(par.f66[indiceColumna]) || 1; 
                        
                        let c63 = c64 / c65; 
                        let c62 = c63 / c66; 

                        loteDatosGlobales.push({ 
                            indicador: indicadorClave, 
                            mes: mes, 
                            numerador: c64, 
                            denominador: c65, 
                            auxiliar: c66,
                            porcentaje: parseFloat(c62.toFixed(2)) 
                        });
                    });
                }
            });

            if (loteDatosGlobales.length > 0) {
                try {
                    const respuesta = await axios.post('/api/guardar_hosp.php', {
                        anio: parseInt(anioSeleccionado, 10), 
                        datosBatch: loteDatosGlobales
                    });
                    
                    if (respuesta.data && respuesta.data.success) {
                        alert(`¡Éxito! Se cargaron los datos de: ${indicadoresExitosos.join(', ')}.`);
                        obtenerDatosDeBD();
                    } else {
                        alert(`Error PHP: ${respuesta.data?.message || 'Respuesta inválida'}`);
                    }
                } catch (error) {
                    console.error("Error al guardar:", error);
                    alert("Error de conexión al guardar.");
                }
            } else {
                alert(`No se logró extraer ningún indicador. Verifica las cédulas.`);
            }
            setGuardando(false);
            e.target.value = null; 
        };
        lector.readAsBinaryString(archivo);
    };

    // ==========================================
    // LÓGICA DE ACUMULADOS EXACTA
    // ==========================================
    const datosAcumulados = useMemo(() => {
        if (datosHosp.length === 0) return [];
        let sumNum = 0; 
        let sumDen = 0; 
        let sumAux = 0; 
        let count = 0;

        return datosHosp.map(d => {
            count++;
            sumNum += d.numerador;
            sumDen += d.denominador;
            let aux = d.auxiliar || 0;
            sumAux += aux;
            
            if (indicadorActivo === 'HOSP_04') {
                let avgConsultorios = sumDen / count; 
                let c63_acum = avgConsultorios > 0 ? sumNum / avgConsultorios : 0;
                let c62_acum = sumAux > 0 ? c63_acum / sumAux : 0;
                
                return { 
                    mes: d.mes, 
                    numeradorAcum: sumNum, 
                    denominadorAcum: parseFloat(avgConsultorios.toFixed(1)), 
                    auxiliarAcum: sumAux,
                    c63Acum: parseFloat(c63_acum.toFixed(2)),
                    porcentajeAcum: parseFloat(c62_acum.toFixed(2))
                };
            } else if (indicadorActivo === 'HOSP_06') {
                const porcAcum = sumDen > 0 ? parseFloat((sumNum / sumDen).toFixed(1)) : 0;
                return { mes: d.mes, numeradorAcum: sumNum, denominadorAcum: sumDen, porcentajeAcum: porcAcum };
            } else {
                const porcAcum = sumDen > 0 ? parseFloat(((sumNum / sumDen) * 100).toFixed(1)) : 0;
                return { mes: d.mes, numeradorAcum: sumNum, denominadorAcum: sumDen, porcentajeAcum: porcAcum };
            }
        });
    }, [datosHosp, indicadorActivo]);

    const totalesAnuales = useMemo(() => {
        if (datosHosp.length === 0) return null;
        let sumNum = 0;
        let sumDen = 0;
        let sumAux = 0;
        let count = datosHosp.length;
        
        datosHosp.forEach(d => {
            sumNum += d.numerador;
            sumDen += d.denominador;
            sumAux += d.auxiliar || 0;
        });
        
        if (indicadorActivo === 'HOSP_04') {
            let avgConsultorios = sumDen / count;
            let c63_total = avgConsultorios > 0 ? sumNum / avgConsultorios : 0;
            let c62_total = sumAux > 0 ? c63_total / sumAux : 0;
            return { numerador: sumNum, denominador: sumAux, porcentaje: parseFloat(c62_total.toFixed(2)) };
        } else if (indicadorActivo === 'HOSP_06') {
            const porcentajeTotal = sumDen > 0 ? (sumNum / sumDen).toFixed(1) : 0;
            return { numerador: sumNum, denominador: sumDen, porcentaje: porcentajeTotal };
        } else {
            const porcentajeTotal = sumDen > 0 ? ((sumNum / sumDen) * 100).toFixed(1) : 0;
            return { numerador: sumNum, denominador: sumDen, porcentaje: porcentajeTotal };
        }
    }, [datosHosp, indicadorActivo]);

    // ==========================================
    // GRÁFICOS
    // ==========================================
    const chartMensual = useMemo(() => {
        if (datosHosp.length === 0) return null;
        return {
            labels: datosHosp.map(d => d.mes),
            datasets: [{
                label: indicadorActivo === 'HOSP_04' || indicadorActivo === 'HOSP_06' ? `Promedio` : `% ${configActual.nombreCorto}`,
                data: datosHosp.map(d => d.porcentaje),
                borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3, tension: 0.3, fill: true, pointBackgroundColor: '#3b82f6', pointRadius: 5
            }]
        };
    }, [datosHosp, configActual, indicadorActivo]);

    const chartAcumulado = useMemo(() => {
        if (datosAcumulados.length === 0) return null;
        return {
            labels: datosAcumulados.map(d => d.mes),
            datasets: [{
                label: indicadorActivo === 'HOSP_04' || indicadorActivo === 'HOSP_06' ? `Promedio Acumulado` : `% ${configActual.nombreCorto} YTD`,
                data: datosAcumulados.map(d => d.porcentajeAcum),
                borderColor: '#8b5cf6', backgroundColor: 'rgba(139, 92, 246, 0.1)',
                borderWidth: 3, tension: 0.3, fill: true, pointBackgroundColor: '#8b5cf6', pointRadius: 5
            }]
        };
    }, [datosAcumulados, configActual, indicadorActivo]);

    // ==========================================
    // INTERFAZ GRÁFICA (RENDER)
    // ==========================================
    return (
        <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-10">
            {rolUsuario === 'admin' && (
                <div className="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm text-center max-w-xl mx-auto relative overflow-hidden">
                    <div className="bg-blue-50 p-3 rounded-full w-14 h-14 flex items-center justify-center mx-auto mb-3">
                        <FileText size={28} className="text-blue-600" />
                    </div>
                    <h3 className="font-bold text-slate-800 text-lg mb-1">Cargar Toda la Cédula</h3>
                    <p className="text-xs text-slate-500 mb-5">Sube el archivo Excel una sola vez.</p>
                    
                    <div className="flex items-center justify-center gap-3 mb-6 bg-slate-50 p-2 rounded-xl inline-flex border border-slate-200">
                        <Calendar size={18} className="text-slate-400 ml-2" />
                        <span className="text-sm font-bold text-slate-600">Año:</span>
                        <input type="number" className="bg-white border border-slate-300 rounded-lg px-3 py-1 font-bold text-blue-700 outline-none w-24 text-center shadow-inner" value={anioSeleccionado} onChange={(e) => setAnioSeleccionado(e.target.value)} disabled={guardando} />
                    </div>
                    <br />
                    <label className={`inline-flex items-center justify-center px-8 py-3 font-bold text-sm rounded-xl shadow-md transition-all ${guardando ? 'bg-blue-400 cursor-not-allowed text-white' : 'bg-blue-600 hover:bg-blue-700 cursor-pointer text-white'}`}>
                        {guardando ? (<><Loader2 className="animate-spin mr-2" size={18} /> Procesando...</>) : (<span>Seleccionar Archivo Excel Único</span>)}
                        <input type="file" accept=".xlsx, .xls" className="hidden" onChange={manejarSubidaHospExcel} disabled={guardando} />
                    </label>
                </div>
            )}

            {cargandoBD ? (
                <div className="flex flex-col items-center justify-center py-10 text-blue-500">
                    <Loader2 className="animate-spin mb-3" size={40} />
                    <p className="font-bold">Consultando base de datos...</p>
                </div>
            ) : datosHosp.length > 0 && chartMensual && totalesAnuales ? (
                <>
                    <div className="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                            <div className="flex-1 w-full sm:w-auto">
                                <label className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">
                                    Seleccionar Indicador para Visualizar
                                </label>
                                <div className="relative">
                                    <select className="w-full bg-slate-50 border border-slate-200 text-slate-800 font-bold text-sm md:text-base lg:text-lg rounded-xl px-4 py-3 appearance-none outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer shadow-sm pr-10" value={indicadorActivo} onChange={(e) => setIndicadorActivo(e.target.value)}>
                                        <option value="HOSP_01">HOSP 01: Porcentaje de Ocupación en Observación</option>
                                        <option value="HOSP_02">HOSP 02: Estancia prolongada (&gt;12h)</option>
                                        <option value="HOSP_03">HOSP 03: Oportunidad Consulta (20 días)</option>
                                        <option value="HOSP_04">HOSP 04: Promedio de Consultas Diarias</option>
                                        <option value="HOSP_05">HOSP 05: Ocupación Hospitalaria</option>
                                        <option value="HOSP_06">HOSP 06: Promedio Días Estancia</option>
                                        <option value="HOSP_07">HOSP 07: Tasa de Mortalidad Hospitalaria</option>
                                        <option value="HOSP_08">HOSP 08: Cirugía Electiva (&lt; 20 días)</option>
                                        <option value="HOSP_09">HOSP 09: Ocupación de Quirófanos</option>
                                        <option value="HOSP_10">HOSP 10: Suspensión Cirugías Electivas</option>
                                    </select>
                                    <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                        <svg className="fill-current h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                    </div>
                                </div>
                            </div>
                            <div className="bg-slate-800 text-white px-6 py-3 rounded-2xl shadow-md min-w-[140px] flex-shrink-0">
                                <span className="text-xs uppercase tracking-widest text-slate-300 block text-center font-bold">Año Analizado</span>
                                <span className="text-3xl font-black block text-center">{anioSeleccionado}</span>
                            </div>
                        </div>
                        <div className="h-80 w-full">
                            <Line data={tabActiva === 'acumulado' ? chartAcumulado : chartMensual} options={{ responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, grid: { color: '#f8fafc' } }, x: { grid: { display: false } } } }} />
                        </div>
                    </div>

                    {tabActiva === 'mensual' ? (
                        <div className="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm animate-in fade-in duration-500">
                            <h3 className="font-bold text-slate-800 text-base mb-4">Desglose Mensual - {configActual.nombreCorto}</h3>
                            <div className="overflow-x-auto border rounded-2xl custom-scrollbar shadow-inner">
                                <table className="w-full text-left text-xs border-collapse min-w-max">
                                    <thead className="bg-slate-50 font-bold text-slate-600">
                                        <tr>
                                            <th className="p-4 border-b border-r bg-slate-100 min-w-[200px] sticky left-0 z-20 shadow-[4px_0_10px_rgba(0,0,0,0.03)] uppercase tracking-wider text-[10px]">Métrica</th>
                                            {datosHosp.map((d, i) => <th key={`head-${i}`} className="p-4 border-b text-center text-blue-700 min-w-[80px]">{d.mes}</th>)}
                                        </tr>
                                    </thead>
                                    <tbody className="text-slate-700 font-medium">
                                        {indicadorActivo === 'HOSP_04' ? (
                                            <>
                                                <tr className="hover:bg-slate-50 border-b border-slate-100 transition-colors group">
                                                    <td className="p-4 border-r font-bold bg-white group-hover:bg-slate-50 sticky left-0 z-10">{configActual.labelNumerador}</td>
                                                    {datosHosp.map((d, i) => <td key={`num-${i}`} className="p-4 text-center">{d.numerador.toLocaleString()}</td>)}
                                                </tr>
                                                <tr className="hover:bg-slate-50 border-b border-slate-100 transition-colors group">
                                                    <td className="p-4 border-r font-bold bg-white group-hover:bg-slate-50 sticky left-0 z-10">{configActual.labelDenominador}</td>
                                                    {datosHosp.map((d, i) => <td key={`den-${i}`} className="p-4 text-center">{d.denominador.toLocaleString()}</td>)}
                                                </tr>
                                                <tr className="hover:bg-slate-50 border-b border-slate-100 transition-colors group">
                                                    <td className="p-4 border-r font-bold bg-white group-hover:bg-slate-50 sticky left-0 z-10">{configActual.labelAuxiliar}</td>
                                                    {datosHosp.map((d, i) => <td key={`aux-${i}`} className="p-4 text-center">{d.auxiliar.toLocaleString()}</td>)}
                                                </tr>
                                                <tr className="hover:bg-slate-50 border-b border-slate-100 transition-colors group">
                                                    <td className="p-4 border-r font-bold text-slate-500 bg-white group-hover:bg-slate-50 sticky left-0 z-10">Prom. por Consultorio (C63)</td>
                                                    {datosHosp.map((d, i) => <td key={`c63-${i}`} className="p-4 text-center text-slate-500">{(d.numerador/d.denominador).toFixed(2)}</td>)}
                                                </tr>
                                            </>
                                        ) : (
                                            <>
                                                <tr className="hover:bg-slate-50 border-b border-slate-100 transition-colors group">
                                                    <td className="p-4 border-r font-bold bg-white group-hover:bg-slate-50 sticky left-0 z-10">{configActual.labelNumerador}</td>
                                                    {datosHosp.map((d, i) => <td key={`num-${i}`} className="p-4 text-center">{d.numerador.toLocaleString()}</td>)}
                                                </tr>
                                                <tr className="hover:bg-slate-50 border-b border-slate-100 transition-colors group">
                                                    <td className="p-4 border-r font-bold bg-white group-hover:bg-slate-50 sticky left-0 z-10">{configActual.labelDenominador}</td>
                                                    {datosHosp.map((d, i) => <td key={`den-${i}`} className="p-4 text-center">{d.denominador.toLocaleString()}</td>)}
                                                </tr>
                                            </>
                                        )}
                                        <tr className="hover:bg-blue-50/50 transition-colors bg-blue-50/30 group">
                                            <td className="p-4 border-r font-black text-blue-700 bg-blue-50 group-hover:bg-blue-100/50 sticky left-0 z-10 shadow-[4px_0_10px_rgba(0,0,0,0.03)]">Resultado Final</td>
                                            {datosHosp.map((d, i) => <td key={`porc-${i}`} className={`p-4 text-center font-black ${configActual.evaluarColor(d.porcentaje)}`}>{d.porcentaje}{indicadorActivo === 'HOSP_04' || indicadorActivo === 'HOSP_06' ? '' : '%'}</td>)}
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    ) : (
                        <div className="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm animate-in fade-in duration-500">
                            <h3 className="font-bold text-slate-800 text-base mb-4">Progreso Acumulado del Año - {configActual.nombreCorto}</h3>
                            <div className="overflow-x-auto border rounded-2xl custom-scrollbar shadow-inner">
                                <table className="w-full text-left text-xs border-collapse min-w-max">
                                    <thead className="bg-slate-50 font-bold text-slate-600">
                                        <tr>
                                            <th className="p-4 border-b border-r bg-slate-100 min-w-[200px] sticky left-0 z-20 shadow-[4px_0_10px_rgba(0,0,0,0.03)] uppercase tracking-wider text-[10px]">Métrica Acumulada</th>
                                            {datosAcumulados.map((d, i) => <th key={`head-ac-${i}`} className="p-4 border-b text-center text-purple-700 min-w-[80px]">{d.mes}</th>)}
                                        </tr>
                                    </thead>
                                    <tbody className="text-slate-700 font-medium">
                                        {indicadorActivo === 'HOSP_04' ? (
                                            <>
                                                <tr className="hover:bg-slate-50 border-b border-slate-100 transition-colors group">
                                                    <td className="p-4 border-r font-bold bg-white group-hover:bg-slate-50 sticky left-0 z-10">Total Consultas Acum.</td>
                                                    {datosAcumulados.map((d, i) => <td key={`num-ac-${i}`} className="p-4 text-center">{d.numeradorAcum.toLocaleString()}</td>)}
                                                </tr>
                                                <tr className="hover:bg-slate-50 border-b border-slate-100 transition-colors group">
                                                    <td className="p-4 border-r font-bold bg-white group-hover:bg-slate-50 sticky left-0 z-10">Prom. Consultorios Activos</td>
                                                    {datosAcumulados.map((d, i) => <td key={`den-ac-${i}`} className="p-4 text-center">{d.denominadorAcum.toLocaleString()}</td>)}
                                                </tr>
                                                <tr className="hover:bg-slate-50 border-b border-slate-100 transition-colors group">
                                                    <td className="p-4 border-r font-bold bg-white group-hover:bg-slate-50 sticky left-0 z-10">Total Días Hábiles Acum.</td>
                                                    {datosAcumulados.map((d, i) => <td key={`aux-ac-${i}`} className="p-4 text-center">{d.auxiliarAcum.toLocaleString()}</td>)}
                                                </tr>
                                            </>
                                        ) : (
                                            <>
                                                <tr className="hover:bg-slate-50 border-b border-slate-100 transition-colors group">
                                                    <td className="p-4 border-r font-bold bg-white group-hover:bg-slate-50 sticky left-0 z-10">Acum. {configActual.labelNumerador}</td>
                                                    {datosAcumulados.map((d, i) => <td key={`num-ac-${i}`} className="p-4 text-center">{d.numeradorAcum.toLocaleString()}</td>)}
                                                </tr>
                                                <tr className="hover:bg-slate-50 border-b border-slate-100 transition-colors group">
                                                    <td className="p-4 border-r font-bold bg-white group-hover:bg-slate-50 sticky left-0 z-10">Acum. {configActual.labelDenominador}</td>
                                                    {datosAcumulados.map((d, i) => <td key={`den-ac-${i}`} className="p-4 text-center">{d.denominadorAcum.toLocaleString()}</td>)}
                                                </tr>
                                            </>
                                        )}
                                        <tr className="hover:bg-purple-50/50 transition-colors bg-purple-50/30 group">
                                            <td className="p-4 border-r font-black text-purple-700 bg-purple-50 group-hover:bg-purple-100/50 sticky left-0 z-10 shadow-[4px_0_10px_rgba(0,0,0,0.03)]">Resultado Acumulado YTD</td>
                                            {datosAcumulados.map((d, i) => <td key={`porc-ac-${i}`} className={`p-4 text-center font-black ${configActual.evaluarColor(d.porcentajeAcum)}`}>{d.porcentajeAcum}{indicadorActivo === 'HOSP_04' || indicadorActivo === 'HOSP_06' ? '' : '%'}</td>)}
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    )}

                    <div className="bg-blue-600 text-white p-8 rounded-3xl shadow-lg border border-blue-500/50 flex flex-col sm:flex-row justify-around items-center gap-6 relative overflow-hidden">
                        <div className="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-blue-500 opacity-20 blur-3xl"></div>
                        <div className="text-center z-10 w-full sm:w-1/3">
                            <p className="text-blue-200 text-xs uppercase font-black tracking-widest mb-1">
                                {indicadorActivo === 'HOSP_04' ? 'Total de Consultas Anual' : 'Numerador Anual'}
                            </p>
                            <p className="text-3xl font-black">{totalesAnuales.numerador.toLocaleString()}</p>
                        </div>
                        <div className="hidden sm:block w-px h-16 bg-blue-400/30 z-10"></div>
                        <div className="text-center z-10 w-full sm:w-1/3">
                            <p className="text-blue-200 text-xs uppercase font-black tracking-widest mb-1">
                                {indicadorActivo === 'HOSP_04' ? 'Total Días Hábiles' : 'Denominador Anual'}
                            </p>
                            <p className="text-3xl font-black">{totalesAnuales.denominador.toLocaleString()}</p>
                        </div>
                        <div className="hidden sm:block w-px h-16 bg-blue-400/30 z-10"></div>
                        <div className="text-center z-10 w-full sm:w-1/3 bg-blue-700/50 p-4 rounded-2xl border border-blue-400/30 shadow-inner">
                            <p className="text-blue-100 text-xs uppercase font-black tracking-widest mb-1">Resultado Final</p>
                            <p className={`text-5xl font-black drop-shadow-md ${configActual.evaluarColorTarjeta(totalesAnuales.porcentaje)}`}>
                                {totalesAnuales.porcentaje}{indicadorActivo === 'HOSP_04' || indicadorActivo === 'HOSP_06' ? '' : '%'}
                            </p>
                            <p className="text-[10px] text-blue-200 mt-1 font-medium">{indicadorActivo === 'HOSP_04' || indicadorActivo === 'HOSP_06' ? 'Promedio General Acumulado' : '% Acumulado del Año'}</p>
                        </div>
                    </div>
                </>
            ) : (
                <div className="text-center p-10 text-slate-400 font-medium text-sm">
                    {rolUsuario === 'admin' ? `Sube el archivo Excel con toda la cédula para visualizar los datos.` : `Aún no hay datos disponibles en este año.`}
                </div>
            )}
        </div>
    );
};

export default IndicadoresHosp;