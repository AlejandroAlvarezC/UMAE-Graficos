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
    // CONFIGURACIÓN UI (Para mostrar en pantalla)
    // ==========================================
    const configIndicadores = {
        'HOSP_01': {
            nombreCorto: 'Ocupación en Observación',
            labelNumerador: 'Horas Paciente',
            labelDenominador: 'Horas Cama',
            evaluarColor: (n) => (n <= 85 ? 'text-emerald-500' : n < 90 ? 'text-amber-500' : 'text-rose-500'),
            evaluarColorTarjeta: (n) => (n <= 85 ? 'text-emerald-300' : n < 90 ? 'text-amber-300' : 'text-rose-300')
        },
        'HOSP_02': {
            nombreCorto: 'Estancia Prolongada (>12h)',
            labelNumerador: 'Pacientes > 12h',
            labelDenominador: 'Total Egresados',
            evaluarColor: (n) => (n <= 10 ? 'text-emerald-500' : n <= 20 ? 'text-amber-500' : 'text-rose-500'),
            evaluarColorTarjeta: (n) => (n <= 10 ? 'text-emerald-300' : n <= 20 ? 'text-amber-300' : 'text-rose-300')
        },
        'HOSP_03': {
            nombreCorto: 'Indicador HOSP 03', // Cambia este título
            labelNumerador: 'Numerador 3',
            labelDenominador: 'Denominador 3',
            evaluarColor: (n) => 'text-blue-500', // Configura tus colores aquí
            evaluarColorTarjeta: (n) => 'text-blue-300'
        },
        // Añade aquí la configuración visual hasta el HOSP_10
    };

    const configActual = configIndicadores[indicadorActivo] || configIndicadores['HOSP_01'];

    // Función para obtener los datos de la BD del indicador seleccionado
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
    // LECTOR MULTIHILO (Sube todos a la vez)
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
                alert("Error: No se encontró la hoja 'indicadores' en el archivo.");
                setGuardando(false);
                e.target.value = null;
                return;
            }

            const hoja = libro.Sheets[nombreHojaCorrecta];
            const filas = XLSX.utils.sheet_to_json(hoja, { header: 1 });

            // Objeto para guardar las filas encontradas de TODOS los indicadores
            let filasEncontradas = {
                'HOSP_01': { num: null, den: null },
                'HOSP_02': { num: null, den: null },
                'HOSP_03': { num: null, den: null },
                'HOSP_04': { num: null, den: null },
                'HOSP_05': { num: null, den: null },
                'HOSP_06': { num: null, den: null },
                'HOSP_07': { num: null, den: null },
                'HOSP_08': { num: null, den: null },
                'HOSP_09': { num: null, den: null },
                'HOSP_10': { num: null, den: null }
            };

            // DICCIONARIO: Aquí le dices al código exactamente qué buscar para cada indicador.
            // Es la clave para que NUNCA vuelva a fallar ni a confundir filas.
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
                    // EJEMPLO: Reemplaza estos textos con los que vengan en la cédula del HOSP 03
                    esNumerador: (t) => t.includes("TEXTO_DEL_NUMERADOR_3_AQUI"),
                    esDenominador: (t) => t.includes("TEXTO_DEL_DENOMINADOR_3_AQUI")
                },
                'HOSP_04': {
                    esNumerador: (t) => t.includes("TEXTO_DEL_NUMERADOR_4_AQUI"),
                    esDenominador: (t) => t.includes("TEXTO_DEL_DENOMINADOR_4_AQUI")
                },
                // Repite el patrón hasta el HOSP_10...
            };

            let indicesMeses = { 0: 2, 1: 3, 2: 4, 3: 5, 4: 6, 5: 7, 6: 8, 7: 9, 8: 10, 9: 11, 10: 12, 11: 13 }; 
            let mesesEncontrados = false;

            filas.forEach((fila) => {
                if (!fila || fila.length === 0) return;
                
                const textoLimpio = fila.map(c => 
                    String(c).toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/\s+/g, " ").trim()
                ).join(" ");
                
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

                // Analizamos la fila actual contra TODOS los indicadores del diccionario
                Object.keys(diccionarioBusqueda).forEach(indicadorClave => {
                    const reglas = diccionarioBusqueda[indicadorClave];
                    
                    // Si la regla existe y aún no hemos encontrado la fila correspondiente a ese indicador
                    if (reglas) {
                        if (!filasEncontradas[indicadorClave].num && reglas.esNumerador(textoLimpio)) {
                            filasEncontradas[indicadorClave].num = fila;
                        }
                        if (!filasEncontradas[indicadorClave].den && reglas.esDenominador(textoLimpio)) {
                            filasEncontradas[indicadorClave].den = fila;
                        }
                    }
                });
            });

            // Recopilar todos los datos que logramos encontrar
            let loteDatosGlobales = [];
            let indicadoresExitosos = [];

            Object.keys(filasEncontradas).forEach(indicadorClave => {
                const par = filasEncontradas[indicadorClave];
                
                // Si encontramos ambas filas para un indicador, calculamos sus 12 meses
                if (par.num && par.den) {
                    indicadoresExitosos.push(indicadorClave);
                    
                    MESES.forEach((mes, index) => {
                        const indiceColumna = indicesMeses[index];
                        let valNum = parseFloat(par.num[indiceColumna]);
                        if (isNaN(valNum)) valNum = 0;

                        let valDen = parseFloat(par.den[indiceColumna]);
                        if (isNaN(valDen) || valDen === 0) valDen = 1; 

                        const porc = parseFloat(((valNum / valDen) * 100).toFixed(1));
                        
                        // Guardamos el objeto especificando a qué indicador pertenece
                        loteDatosGlobales.push({
                            indicador: indicadorClave,
                            mes: mes,
                            numerador: valNum,
                            denominador: valDen,
                            porcentaje: porc
                        });
                    });
                }
            });

            if (loteDatosGlobales.length > 0) {
                try {
                    // Enviamos TODO el lote gigante a PHP
                    const respuesta = await axios.post('/api/guardar_hosp.php', {
                        anio: parseInt(anioSeleccionado, 10), 
                        datosBatch: loteDatosGlobales
                    });
                    
                    if (respuesta.data && respuesta.data.success) {
                        alert(`¡Éxito! Se cargaron los datos de: ${indicadoresExitosos.join(', ')} para el año ${anioSeleccionado}.`);
                        // Recargar los datos del indicador que esté seleccionado en la pantalla
                        obtenerDatosDeBD();
                    } else {
                        alert(`Error en el Servidor PHP: ${respuesta.data?.message || 'Respuesta inválida'}`);
                    }
                } catch (error) {
                    console.error("Error de red/conexión:", error);
                    alert("Error de conexión al guardar.");
                }
            } else {
                alert(`No se logró extraer ningún indicador completo. Revisa el texto en el diccionario de búsqueda.`);
            }
            setGuardando(false);
            e.target.value = null; 
        };
        lector.readAsBinaryString(archivo);
    };

    const datosAcumulados = useMemo(() => {
        if (datosHosp.length === 0) return [];
        let sumNum = 0;
        let sumDen = 0;
        return datosHosp.map(d => {
            sumNum += d.numerador;
            if(d.numerador > 0 || d.denominador > 1) sumDen += d.denominador;
            const porcAcum = sumDen > 0 ? parseFloat(((sumNum / sumDen) * 100).toFixed(1)) : 0;
            return { mes: d.mes, numeradorAcum: sumNum, denominadorAcum: sumDen, porcentajeAcum: porcAcum };
        });
    }, [datosHosp]);

    const totalesAnuales = useMemo(() => {
        if (datosHosp.length === 0) return null;
        let sumNum = 0;
        let sumDen = 0;
        datosHosp.forEach(d => {
            sumNum += d.numerador;
            if(d.numerador > 0 || d.denominador > 1) sumDen += d.denominador;
        });
        const porcentajeTotal = sumDen > 0 ? ((sumNum / sumDen) * 100).toFixed(1) : 0;
        return { numerador: sumNum, denominador: sumDen, porcentaje: porcentajeTotal };
    }, [datosHosp]);

    const chartMensual = useMemo(() => {
        if (datosHosp.length === 0) return null;
        return {
            labels: datosHosp.map(d => d.mes),
            datasets: [{
                label: `% ${configActual.nombreCorto} Mensual`,
                data: datosHosp.map(d => d.porcentaje),
                borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3, tension: 0.3, fill: true, pointBackgroundColor: '#3b82f6', pointRadius: 5
            }]
        };
    }, [datosHosp, configActual]);

    const chartAcumulado = useMemo(() => {
        if (datosAcumulados.length === 0) return null;
        return {
            labels: datosAcumulados.map(d => d.mes),
            datasets: [{
                label: `% ${configActual.nombreCorto} Acumulado YTD`,
                data: datosAcumulados.map(d => d.porcentajeAcum),
                borderColor: '#8b5cf6', backgroundColor: 'rgba(139, 92, 246, 0.1)',
                borderWidth: 3, tension: 0.3, fill: true, pointBackgroundColor: '#8b5cf6', pointRadius: 5
            }]
        };
    }, [datosAcumulados, configActual]);

    return (
        <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-10">
            
            {rolUsuario === 'admin' && (
                <div className="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm text-center max-w-xl mx-auto relative overflow-hidden">
                    <div className="bg-blue-50 p-3 rounded-full w-14 h-14 flex items-center justify-center mx-auto mb-3">
                        <FileText size={28} className="text-blue-600" />
                    </div>
                    <h3 className="font-bold text-slate-800 text-lg mb-1">Cargar Toda la Cédula (HOSP 01 al 10)</h3>
                    <p className="text-xs text-slate-500 mb-5">Sube el archivo Excel una sola vez. El sistema extraerá automáticamente todos los indicadores disponibles.</p>
                    
                    <div className="flex items-center justify-center gap-3 mb-6 bg-slate-50 p-2 rounded-xl inline-flex border border-slate-200">
                        <Calendar size={18} className="text-slate-400 ml-2" />
                        <span className="text-sm font-bold text-slate-600">Año del Reporte:</span>
                        <input 
                            type="number" 
                            className="bg-white border border-slate-300 rounded-lg px-3 py-1 font-bold text-blue-700 outline-none w-24 text-center shadow-inner"
                            value={anioSeleccionado}
                            onChange={(e) => setAnioSeleccionado(e.target.value)}
                            disabled={guardando}
                        />
                    </div>
                    <br />
                    <label className={`inline-flex items-center justify-center px-8 py-3 font-bold text-sm rounded-xl shadow-md transition-all ${guardando ? 'bg-blue-400 cursor-not-allowed text-white' : 'bg-blue-600 hover:bg-blue-700 cursor-pointer text-white'}`}>
                        {guardando ? (
                            <><Loader2 className="animate-spin mr-2" size={18} /> Procesando Todo...</>
                        ) : (
                            <span>Seleccionar Archivo Excel Único</span>
                        )}
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
                                    <select 
                                        className="w-full bg-slate-50 border border-slate-200 text-slate-800 font-bold text-sm md:text-base lg:text-lg rounded-xl px-4 py-3 appearance-none outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all cursor-pointer shadow-sm pr-10 whitespace-normal"
                                        value={indicadorActivo}
                                        onChange={(e) => setIndicadorActivo(e.target.value)}
                                    >
                                        <option value="HOSP_01">HOSP 01: Porcentaje de Ocupación en Observación</option>
                                        <option value="HOSP_02">HOSP 02: Porcentaje de pacientes con estancia prolongada</option>
                                        <option value="HOSP_03">HOSP 03: Indicador 3</option>
                                        <option value="HOSP_04">HOSP 04: Indicador 4</option>
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
                                        <tr className="hover:bg-slate-50 border-b border-slate-100 transition-colors group">
                                            <td className="p-4 border-r font-bold bg-white group-hover:bg-slate-50 sticky left-0 z-10 shadow-[4px_0_10px_rgba(0,0,0,0.03)]">{configActual.labelNumerador} <span className="text-[9px] text-slate-400 block font-normal">(Numerador)</span></td>
                                            {datosHosp.map((d, i) => <td key={`num-${i}`} className="p-4 text-center">{d.numerador.toLocaleString()}</td>)}
                                        </tr>
                                        <tr className="hover:bg-slate-50 border-b border-slate-100 transition-colors group">
                                            <td className="p-4 border-r font-bold bg-white group-hover:bg-slate-50 sticky left-0 z-10 shadow-[4px_0_10px_rgba(0,0,0,0.03)]">{configActual.labelDenominador} <span className="text-[9px] text-slate-400 block font-normal">(Denominador)</span></td>
                                            {datosHosp.map((d, i) => <td key={`den-${i}`} className="p-4 text-center">{d.denominador.toLocaleString()}</td>)}
                                        </tr>
                                        <tr className="hover:bg-blue-50/50 transition-colors bg-blue-50/30 group">
                                            <td className="p-4 border-r font-black text-blue-700 bg-blue-50 group-hover:bg-blue-100/50 sticky left-0 z-10 shadow-[4px_0_10px_rgba(0,0,0,0.03)]">Porcentaje Mensual</td>
                                            {datosHosp.map((d, i) => <td key={`porc-${i}`} className={`p-4 text-center font-black ${configActual.evaluarColor(d.porcentaje)}`}>{d.porcentaje}%</td>)}
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
                                        <tr className="hover:bg-slate-50 border-b border-slate-100 transition-colors group">
                                            <td className="p-4 border-r font-bold bg-white group-hover:bg-slate-50 sticky left-0 z-10 shadow-[4px_0_10px_rgba(0,0,0,0.03)]">Acum. {configActual.labelNumerador}</td>
                                            {datosAcumulados.map((d, i) => <td key={`num-ac-${i}`} className="p-4 text-center">{d.numeradorAcum.toLocaleString()}</td>)}
                                        </tr>
                                        <tr className="hover:bg-slate-50 border-b border-slate-100 transition-colors group">
                                            <td className="p-4 border-r font-bold bg-white group-hover:bg-slate-50 sticky left-0 z-10 shadow-[4px_0_10px_rgba(0,0,0,0.03)]">Acum. {configActual.labelDenominador}</td>
                                            {datosAcumulados.map((d, i) => <td key={`den-ac-${i}`} className="p-4 text-center">{d.denominadorAcum.toLocaleString()}</td>)}
                                        </tr>
                                        <tr className="hover:bg-purple-50/50 transition-colors bg-purple-50/30 group">
                                            <td className="p-4 border-r font-black text-purple-700 bg-purple-50 group-hover:bg-purple-100/50 sticky left-0 z-10 shadow-[4px_0_10px_rgba(0,0,0,0.03)]">Porcentaje Acumulado YTD</td>
                                            {datosAcumulados.map((d, i) => <td key={`porc-ac-${i}`} className={`p-4 text-center font-black ${configActual.evaluarColor(d.porcentajeAcum)}`}>{d.porcentajeAcum}%</td>)}
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    )}

                    <div className="bg-blue-600 text-white p-8 rounded-3xl shadow-lg border border-blue-500/50 flex flex-col sm:flex-row justify-around items-center gap-6 relative overflow-hidden">
                        <div className="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-blue-500 opacity-20 blur-3xl"></div>
                        <div className="text-center z-10 w-full sm:w-1/3">
                            <p className="text-blue-200 text-xs uppercase font-black tracking-widest mb-1">Numerador Anual</p>
                            <p className="text-3xl font-black">{totalesAnuales.numerador.toLocaleString()}</p>
                            <p className="text-[10px] text-blue-300 mt-1">Total {configActual.labelNumerador}</p>
                        </div>
                        <div className="hidden sm:block w-px h-16 bg-blue-400/30 z-10"></div>
                        <div className="text-center z-10 w-full sm:w-1/3">
                            <p className="text-blue-200 text-xs uppercase font-black tracking-widest mb-1">Denominador Anual</p>
                            <p className="text-3xl font-black">{totalesAnuales.denominador.toLocaleString()}</p>
                            <p className="text-[10px] text-blue-300 mt-1">Total {configActual.labelDenominador}</p>
                        </div>
                        <div className="hidden sm:block w-px h-16 bg-blue-400/30 z-10"></div>
                        <div className="text-center z-10 w-full sm:w-1/3 bg-blue-700/50 p-4 rounded-2xl border border-blue-400/30 shadow-inner">
                            <p className="text-blue-100 text-xs uppercase font-black tracking-widest mb-1">Resultado Final</p>
                            <p className={`text-5xl font-black drop-shadow-md ${configActual.evaluarColorTarjeta(totalesAnuales.porcentaje)}`}>{totalesAnuales.porcentaje}%</p>
                            <p className="text-[10px] text-blue-200 mt-1 font-medium">% Acumulado del Año</p>
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