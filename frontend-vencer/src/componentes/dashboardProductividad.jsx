import React, { useState, useEffect, useMemo } from 'react';
import axios from 'axios';
import localforage from 'localforage';
import { UploadCloud, Activity, Users, CalendarCheck, Clock, ArrowLeft, BarChart2, Database, TableProperties, Stethoscope, Ambulance, Bed, Syringe, Siren, ChevronLeft, ChevronRight, Download, Filter, Menu, Award, Target, BookOpen, MapPin, ClipboardList} from 'lucide-react';
import { Bar, Doughnut, Line } from 'react-chartjs-2';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, LineElement, PointElement, Title, Tooltip, Legend, ArcElement } from 'chart.js';
import AdministradorCatalogos from './AdministradorCatalogos';
import MenuPrincipal from './MenuPrincipal';
import TableroParamedicos from './TableroParamedicos';
// Registrar componentes de Chart.js
ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, Title, Tooltip, Legend, ArcElement);

const MESES = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];

// ==========================================
// ALGORITMO DE CALENDARIO OPERATIVO (Regla 26 al 25)
// Genera semanas dinámicas cortando en Domingos
// ==========================================
const generarCalendarioIMSS = (mesSeleccionado, anioSeleccionado) => {
    // Mes va de 0 (Enero) a 11 (Diciembre)
    const anioAnterior = mesSeleccionado === 0 ? anioSeleccionado - 1 : anioSeleccionado;
    const mesAnterior = mesSeleccionado === 0 ? 11 : mesSeleccionado - 1;

    // Inicio el 26 del mes pasado, fin el 25 del actual
    const fechaInicio = new Date(anioAnterior, mesAnterior, 26);
    const fechaFin = new Date(anioSeleccionado, mesSeleccionado, 25, 23, 59, 59);

    let semanas = [];
    let fechaActual = new Date(fechaInicio);
    let numeroSemana = 1;
    let inicioSemana = new Date(fechaActual);

    while (fechaActual <= fechaFin) {
        // Si es Domingo (0) O es el último día del mes (25)
        if (fechaActual.getDay() === 0 || fechaActual.getDate() === 25) {
            
            // Creamos el fin de semana a las 23:59 hrs para que atrape todas las consultas de ese día
            const finDeSemana = new Date(fechaActual);
            finDeSemana.setHours(23, 59, 59);

            semanas.push({
                semana: numeroSemana,
                inicio: new Date(inicioSemana),
                fin: finDeSemana
            });
            
            numeroSemana++;
            inicioSemana = new Date(fechaActual);
            inicioSemana.setDate(inicioSemana.getDate() + 1); // El inicio de la siguiente es mañana (Lunes)
        }
        fechaActual.setDate(fechaActual.getDate() + 1);
    }

    // Regla de seguridad: Si matemáticamente salen 6 semanas, fusionamos la 6 con la 5
    if (semanas.length > 5) {
        semanas[4].fin = semanas[semanas.length - 1].fin;
        semanas = semanas.slice(0, 5);
    }
    
    // Si salen menos de 5, rellenamos para no romper la gráfica
    while(semanas.length < 5) {
         semanas.push({ semana: semanas.length + 1, vacia: true });
    }

    // Formatear etiquetas bonitas como "26-dic a 28-dic"
    const nombresMeses = ["ene", "feb", "mar", "abr", "may", "jun", "jul", "ago", "sep", "oct", "nov", "dic"];
    semanas.forEach(s => {
        if(!s.vacia) {
            const d1 = String(s.inicio.getDate()).padStart(2, '0');
            const m1 = nombresMeses[s.inicio.getMonth()];
            const d2 = String(s.fin.getDate()).padStart(2, '0');
            const m2 = nombresMeses[s.fin.getMonth()];
            s.label = `S${s.semana} (${d1}-${m1} al ${d2}-${m2})`;
        } else {
            s.label = `S${s.semana} (N/A)`;
        }
    });

    return semanas;
};

// ==========================================
// CACHÉ GLOBAL EN MEMORIA (Patrón Singleton)
// Mantiene los datos vivos aunque salgas del módulo
// ==========================================
let cacheDatosProductividad = [];
let cacheDiccionarioMedicos = {};
let cacheEstaCargada = false;

let cacheDiccionarioCIE = {};

// ==========================================
// SUB-COMPONENTE: Tabla de Datos (ACTUALIZADO CON ÍNDICE)
// ==========================================
// ==========================================
// SUB-COMPONENTE: Tabla de Datos (ACTUALIZADO CON COLUMNA EXTRA)
// ==========================================
const TablaDatos = ({ titulo1, titulo2, labels, data, dataPV, dataSub, tituloExtra, dataExtra, total = true }) => {
    if (!labels || !data) return null;
    
    // Si nos pasan los arreglos de PV y Sub, activamos las columnas extra
    const mostrarDesglose = dataPV && dataSub;

    // Cálculos para los totales del tfoot (Pie de tabla)
    const totalPV = mostrarDesglose ? dataPV.reduce((a, b) => a + b, 0) : 0;
    const totalSub = mostrarDesglose ? dataSub.reduce((a, b) => a + b, 0) : 0;
    const totalGeneral = data.reduce((a, b) => a + b, 0);

    return (
        <div className="mt-4 border-t border-slate-100 pt-4 animate-in fade-in slide-in-from-top-2 duration-300 h-full">
            <div className="max-h-[350px] overflow-y-auto pr-2 custom-scrollbar">
                <table className="w-full text-left text-sm text-slate-600">
                    <thead className="text-xs text-slate-400 uppercase bg-slate-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th className="py-2 px-3 font-bold rounded-l-lg">{titulo1}</th>
                            
                            {/* AQUÍ ESTÁ LA NUEVA COLUMNA EXTRA (Ej. Especialidad) */}
                            {dataExtra && <th className="py-2 px-3 font-bold">{tituloExtra}</th>}

                            {mostrarDesglose && <th className="py-2 px-3 font-bold text-center text-[#c2410c]/70">1ra Vez</th>}
                            {mostrarDesglose && <th className="py-2 px-3 font-bold text-center text-[#822626]/70">Subsec.</th>}
                            {mostrarDesglose && <th className="py-2 px-3 font-bold text-center text-slate-500" title="Índice de Subsecuencia (Subsecuentes / Primera Vez)">Índice</th>}
                            <th className="py-2 px-3 font-bold text-right rounded-r-lg">{titulo2}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {labels.map((label, index) => {
                            let indice = '0.00';
                            if (dataPV && dataPV[index] > 0) {
                                indice = (dataSub[index] / dataPV[index]).toFixed(2);
                            } else if (dataSub && dataSub[index] > 0) {
                                indice = '∞'; 
                            }

                            return (
                                <tr key={index} className="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                                    <td className="py-2 px-3">{label}</td>
                                    
                                    {/* DIBUJAMOS EL DATO EXTRA (Ej. CARDIOLOGÍA) */}
                                    {dataExtra && <td className="py-2 px-3 text-xs font-bold text-slate-400">{dataExtra[index]}</td>}

                                    {mostrarDesglose && <td className="py-2 px-3 text-center text-[#c2410c] font-medium">{dataPV[index].toLocaleString()}</td>}
                                    {mostrarDesglose && <td className="py-2 px-3 text-center text-[#822626] font-medium">{dataSub[index].toLocaleString()}</td>}
                                    {mostrarDesglose && <td className="py-2 px-3 text-center text-slate-500 font-bold bg-slate-50/50">{indice}</td>}
                                    <td className="py-2 px-3 text-right font-black text-slate-700">{data[index].toLocaleString()}</td>
                                </tr>
                            );
                        })}
                    </tbody>
                    {total && (
                        <tfoot className="bg-slate-50 font-bold sticky bottom-0 z-10 shadow-sm">
                            <tr>
                                <td className="py-2 px-3 rounded-l-lg text-slate-500 uppercase tracking-widest text-xs">Total General</td>
                                
                                {/* Espacio en blanco para cuadrar las columnas si hay dato extra */}
                                {dataExtra && <td className="py-2 px-3"></td>}

                                {mostrarDesglose && <td className="py-2 px-3 text-center text-[#c2410c] font-black">{totalPV.toLocaleString()}</td>}
                                {mostrarDesglose && <td className="py-2 px-3 text-center text-[#822626] font-black">{totalSub.toLocaleString()}</td>}
                                {mostrarDesglose && (
                                    <td className="py-2 px-3 text-center text-slate-600 font-black bg-slate-100/50">
                                        {totalPV > 0 ? (totalSub / totalPV).toFixed(2) : '0.00'}
                                    </td>
                                )}
                                <td className="py-2 px-3 text-right rounded-r-lg text-slate-800 font-black">{totalGeneral.toLocaleString()}</td>
                            </tr>
                        </tfoot>
                    )}
                </table>
            </div>
        </div>
    );
};

export default function DashboardProductividad({ isAdmin }) {
    // ESTADOS DE NAVEGACIÓN
    const [vistaActiva, setVistaActiva] = useState('dashboard');
    const [areaSidebar, setAreaSidebar] = useState('consulta_externa'); 
    const [mostrarTablas, setMostrarTablas] = useState(false);
    const [sidebarCollapsed, setSidebarCollapsed] = useState(false);

    // ESTADOS DE FILTROS GLOBALES
    const [anioSeleccionado, setAnioSeleccionado] = useState('todos');
    const [mesSeleccionado, setMesSeleccionado] = useState('todos'); 
    const [mesInicio, setMesInicio] = useState(0); 
    const [mesFin, setMesFin] = useState(11); 
    const [divisionSeleccionada, setDivisionSeleccionada] = useState('todas');
    const [especialidadSeleccionada, setEspecialidadSeleccionada] = useState('todas');

    // ESTADOS EXCLUSIVOS PARA LA GRÁFICA DE METAS
    const [mesGraficoMeta, setMesGraficoMeta] = useState(11); 
    const [anioGraficoMeta, setAnioGraficoMeta] = useState(2025);

    // ESTADOS DE DATOS
    const [archivo, setArchivo] = useState(null);
    const [mensaje, setMensaje] = useState('');
    const [cargandoSubida, setCargandoSubida] = useState(false);
    const [datos, setDatos] = useState([]);
    const [cargandoDatos, setCargandoDatos] = useState(false);
    const [error, setError] = useState(null);
    const [diccionarioMedicos, setDiccionarioMedicos] = useState({});
    // Diccionario CIE
    const [diccionarioCIE, setDiccionarioCIE] = useState({});
    // Diccionario Divisiones y Especialidades 
    const [diccionarioEspecialidades, setDiccionarioEspecialidades] = useState({});


// ==========================================
    // CARGA DE DATOS (Stale-While-Revalidate)
    // ==========================================
// ==========================================
    // CARGA DE DATOS (Stale-While-Revalidate con IndexedDB / Big Data)
    // ==========================================
    const cargarDatos = async () => {
        try {
            // 1. BUSCAMOS EN EL CAJÓN GIGANTE (IndexedDB)
            const datosLocales = await localforage.getItem('cache_productividad_vencer');
            const versionLocal = await localforage.getItem('version_productividad_vencer') || "0";

            if (datosLocales && datosLocales.length > 0) {
                // ¡BAM! Gráficas dibujadas en 0ms desde la base de datos local del navegador
                setDatos(datosLocales); 
            } else {
                // Solo si el disco duro local está totalmente vacío mostramos que está cargando
                setCargandoDatos(true); 
            }

            // 2. PREGUNTAR AL SERVIDOR "¿HAY ALGO NUEVO?" (Ping de 5ms)
            const resVersion = await axios.get('/api/api_check_update.php');
            const versionServidor = String(resVersion.data.ultima_actualizacion);

            // 3. SI EL SERVIDOR TIENE DATOS MÁS NUEVOS, DESCARGAMOS EL PESO PESADO
            if (versionServidor !== versionLocal || !datosLocales) {
                console.log("¡Hay datos nuevos en el servidor! Sincronizando en segundo plano...");
                
                const resDatos = await axios.get('/api/api_productividad.php');
                
                if (Array.isArray(resDatos.data)) {
                    const datosLimpios = resDatos.data.filter(d => {
                        const esp = (d.especialidad || '').toUpperCase();
                        const pasaFiltro1 = !esp.includes('TOCO') && !esp.includes('PRIMER CONTACTO');
                        const pasaFiltro2 = !esp.includes('6900') && !esp.includes('5001') && !esp.includes('6300') && !esp.includes('6600');
                        return pasaFiltro1 && pasaFiltro2;
                    });
                    
                    // Actualizamos las gráficas silenciosamente frente al usuario
                    setDatos(datosLimpios);
                    
                    // GUARDAMOS LA NUEVA VERSIÓN EN EL CAJÓN GRANDE
                    await localforage.setItem('cache_productividad_vencer', datosLimpios);
                    await localforage.setItem('version_productividad_vencer', versionServidor);
                    console.log("Sincronización de Big Data completada y guardada localmente.");
                }
            } else {
                console.log("El dashboard está sincronizado al 100%. No se usó ancho de banda.");
            }
        } catch (err) {
            console.error("Error en la validación o descarga de datos:", err);
            setError("Modo sin conexión. Mostrando últimos datos guardados.");
        } finally {
            setCargandoDatos(false);
        }
    };

    useEffect(() => {
            // Solo cargar si estamos en el dashboard y la memoria está VACÍA
            if (vistaActiva === 'dashboard' && datos.length === 0) {
                cargarDatos();
            }
        }, [vistaActiva]); // Eliminamos areaSidebar para que no se recargue al cambiar de pestaña

// ==========================================
    // CARGA DEL DICCIONARIO DE MÉDICOS (MODO DEBUG NUCLEAR)
    // ==========================================
    const cargarDiccionario = async () => {
        console.log("🛠️ 1. Iniciando carga de médicos (Caché ignorada por ahora)...");

        try {
            console.log("📡 2. Pidiendo datos a /api/api_medicos.php...");
            const res = await axios.get('/api/api_medicos.php'); 
            
            console.log("📥 3. Respuesta del servidor recibida:", res.data);
            
            if (Array.isArray(res.data) && res.data.length > 0) {
                const dicc = res.data.reduce((acc, medico) => {
                    // TRUCO DE INGENIERÍA: A veces Excel exporta la matrícula como "12345.0"
                    let mat = String(medico.matricula || '').trim().replace('.0', '').replace(/\s/g, '');
                    const nom = String(medico.nombre || '').trim();
                    
                    if (mat && nom) acc[mat] = nom;
                    return acc;
                }, {});
                
                console.log("✅ 4. Diccionario final construido. Total médicos:", Object.keys(dicc).length);
                console.log("🔍 Muestra del diccionario:", dicc); // Aquí veremos si las matrículas se ven bien
                
                // Guardamos los datos
                cacheDiccionarioMedicos = dicc;
                setDiccionarioMedicos(dicc);
                await localforage.setItem('cache_medicos_vencer', dicc);
                
            } else {
                console.error("❌ 3. ERROR: La API respondió, pero no es un arreglo válido o está vacío.");
            }
        } catch (err) {
            console.error("❌ ERROR CRÍTICO al conectar con api_medicos.php:", err);
        }
    };

// ==========================================
    // CARGA DEL DICCIONARIO CIE-10 (CON AUTO-ACTUALIZACIÓN)
    // ==========================================
    const cargarDiccionarioCIE = async () => {
        try {
            // 1. Destruimos la caché atascada en el disco duro del navegador
            await localforage.removeItem('cache_cie_vencer');
            cacheDiccionarioCIE = {}; // Limpiamos la memoria RAM también

            // 2. Obligamos a React a ir a PHP por los datos frescos
            const urlFiel = `/api/api_cie.php?t=${new Date().getTime()}`;
            const res = await axios.get(urlFiel);
            
            if (Array.isArray(res.data)) {
                // 3. Reconstruimos el diccionario
                const dicc = res.data.reduce((acc, item) => {
                    const cod = String(item.codigo || '').trim().toUpperCase();
                    const desc = String(item.descripcion || '').trim();
                    if (cod && desc) acc[cod] = desc;
                    return acc;
                }, {});
                
                // 4. Guardamos la nueva versión
                cacheDiccionarioCIE = dicc;
                setDiccionarioCIE(dicc);
                await localforage.setItem('cache_cie_vencer', dicc);
                console.log("✅ Diccionario CIE-10 Fresco descargado:", dicc);
            }
        } catch (err) {
            console.error("Error catálogo CIE", err);
        }
    };

    // ==========================================
    // DICCIONARIO: ESPECIALIDADES Y DIVISIONES
    // ==========================================
    const cargarDiccionarioEspecialidades = async () => {
        try {
            // LA MAGIA: Destruimos la caché vieja obligatoriamente para forzar la actualización
            await localforage.removeItem('cache_especialidades_vencer');

            // Descargamos fresco de MySQL con un truco anti-caché HTTP
            const res = await axios.get(`/api/api_crud_especialidades.php?t=${new Date().getTime()}`);
            
            if (Array.isArray(res.data)) {
                const dicc = res.data.reduce((acc, item) => {
                    const clave = String(item.clave).trim().toUpperCase();
                    if (clave) {
                        acc[clave] = {
                            nombre: item.nombre,
                            division: item.division
                        };
                    }
                    return acc;
                }, {});
                
                setDiccionarioEspecialidades(dicc);
                await localforage.setItem('cache_especialidades_vencer', dicc);
                console.log("✅ Diccionario de Especialidades ACTUALIZADO:", dicc);
            }
        } catch (error) {
            console.error("Error al cargar el diccionario de especialidades:", error);
        }
    };

    // ==========================================
    // 3. EL DISPARADOR MAESTRO (Hook de Montaje)
    // ==========================================
    useEffect(() => {
        // Le decimos a React que arranque estas tres cosas en segundo plano
        // al mismo tiempo en cuanto el usuario abra el tablero.
        cargarDatos();
        cargarDiccionario();
        
        cargarDiccionarioCIE(); 
        cargarDiccionarioEspecialidades();
        
    }, []);

    // RESETEAR ESPECIALIDAD CUANDO CAMBIA LA DIVISIÓN
    useEffect(() => {
        setEspecialidadSeleccionada('todas');
    }, [divisionSeleccionada]);

    const handleSubirArchivo = async (e) => {
        e.preventDefault();
        if (!archivo) { setMensaje('Por favor selecciona un archivo CSV.'); return; }
        setCargandoSubida(true);
        setMensaje('Procesando datos...');

        const formData = new FormData();
        formData.append('archivo_csv', archivo);

        try {
            const respuesta = await axios.post('/api/upload_productividad.php', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            if (respuesta.data.success) {
                setMensaje(`✅ ¡Éxito! ${respuesta.data.message}`);
                setArchivo(null);

                await localforage.removeItem('cache_productividad_vencer');
                
                // Obligamos a descargar la nueva foto
                cargarDatos();

            } else setMensaje(`❌ Error: ${respuesta.data.message}`);
        } catch (error) { setMensaje('❌ Error al conectar con el servidor.'); } 
        finally { setCargandoSubida(false); }
    };

    // ==========================================
    // LÓGICA INTELIGENTE DE FILTRADO
    // ==========================================
    const encontrarFecha = (obj) => {
        const keys = Object.keys(obj);
        for (let k of keys) {
            if (k.toLowerCase().includes('fecha')) return obj[k];
        }
        return null;
    };

    // ==========================================
    // CÁLCULO DE LA ÚLTIMA FECHA EN LA BD
    // ==========================================
    const ultimaFechaBD = useMemo(() => {
        if (!datos || datos.length === 0) return 'No disponible';
        
        let maxDate = new Date(2000, 0, 1); // Fecha muy antigua de inicio
        let found = false;

        datos.forEach(d => {
            let a = d.anio || d.Anio || d.ANIO || d.año || d.Año || d.AÑO;
            let m = d.mes || d.Mes || d.MES;
            let dia = 1;

            const f = encontrarFecha(d); // Tu función que detecta 'fecha_atencion'
            if (f) {
                if (f.includes('-')) {
                    const p = f.split('-');
                    if (p[0].length === 4) { a = a || p[0]; m = m || p[1]; dia = p[2]; }
                    else { a = a || p[2]; m = m || p[1]; dia = p[0]; }
                } else if (f.includes('/')) {
                    const p = f.split('/');
                    if (p[0].length === 4) { a = a || p[0]; m = m || p[1]; dia = p[2]; }
                    else { a = a || p[2]; m = m || p[1]; dia = p[0]; }
                }
            }

            if (a && m) {
                // En JavaScript los meses van de 0 a 11, por eso restamos 1 al mes
                const currentDate = new Date(parseInt(a), parseInt(m) - 1, parseInt(dia));
                if (currentDate > maxDate) {
                    maxDate = currentDate;
                    found = true;
                }
            }
        });

        if (!found) return 'No disponible';

        // Formatear a dd/mm/aaaa
        const dd = String(maxDate.getDate()).padStart(2, '0');
        const mm = String(maxDate.getMonth() + 1).padStart(2, '0');
        const yyyy = maxDate.getFullYear();

        return `${dd}/${mm}/${yyyy}`;
    }, [datos]);

    const aniosDisponibles = useMemo(() => {
        const anios = new Set();
        datos.forEach(d => {
            let a = d.anio || d.Anio || d.ANIO || d.año || d.Año || d.AÑO;
            if (a) {
                anios.add(String(a));
            } else {
                const f = encontrarFecha(d);
                if (f) {
                    if (f.includes('-')) {
                        const p = f.split('-');
                        anios.add(p[0].length === 4 ? p[0] : p[2]);
                    } else if (f.includes('/')) {
                        const p = f.split('/');
                        anios.add(p[0].length === 4 ? p[0] : p[2]);
                    }
                }
            }
        });
        return [...anios].sort().reverse();
    }, [datos]);

    const datosFiltradosFecha = useMemo(() => {
        return datos.filter(item => {
            let a = item.anio || item.Anio || item.ANIO || item.año || item.Año || item.AÑO;
            let m = item.mes || item.Mes || item.MES;

            if (!a || !m) {
                const f = encontrarFecha(item);
                if (f) {
                    if (f.includes('-')) {
                        const parts = f.split('-');
                        if (parts[0].length === 4) { a = a || parts[0]; m = m || parts[1]; }
                        else { a = a || parts[2]; m = m || parts[1]; }
                    } else if (f.includes('/')) {
                        const parts = f.split('/');
                        if (parts[0].length === 4) { a = a || parts[0]; m = m || parts[1]; }
                        else { a = a || parts[2]; m = m || parts[1]; }
                    }
                }
            }

            if (!a) return anioSeleccionado === 'todos'; 
            if (!m) m = '1'; 

            const mesIdx = parseInt(m, 10) - 1; 
            const pasaAnio = anioSeleccionado === 'todos' || String(a) === String(anioSeleccionado);
            
            let pasaMes = true;
            if (mesSeleccionado === 'rango') {
                pasaMes = mesIdx >= mesInicio && mesIdx <= mesFin;
            } else if (mesSeleccionado !== 'todos') {
                pasaMes = mesIdx === Number(mesSeleccionado);
            }

            return pasaAnio && pasaMes;
        });
    }, [datos, anioSeleccionado, mesSeleccionado, mesInicio, mesFin]);

    const rankingDivisiones = useMemo(() => {
        const conteo = {};
        datosFiltradosFecha.forEach(d => {
            const div = (d.division || 'Sin Asignar').trim();
            conteo[div] = (conteo[div] || 0) + 1;
        });
        return Object.entries(conteo).sort((a, b) => b[1] - a[1]);
    }, [datosFiltradosFecha]);

    const divisionesDisponibles = useMemo(() => rankingDivisiones.map(item => item[0]).sort(), [rankingDivisiones]);

    const infoEspecialidades = useMemo(() => {
        const conteo = {};
        const divMap = {};
        datosFiltradosFecha.forEach(d => {
            const esp = (d.especialidad || 'Desconocida').trim();
            const div = (d.division || 'Sin Asignar').trim();
            conteo[esp] = (conteo[esp] || 0) + 1;
            if (!divMap[esp]) divMap[esp] = div; 
        });
        const ranking = Object.entries(conteo).sort((a, b) => b[1] - a[1]);
        return { ranking, divMap };
    }, [datosFiltradosFecha]);

    const datosFiltradosDivision = useMemo(() => {
        return datosFiltradosFecha.filter(item => {
            if (divisionSeleccionada === 'todas') return true;
            return (item.division || 'Sin Asignar').trim() === divisionSeleccionada;
        });
    }, [datosFiltradosFecha, divisionSeleccionada]);

    const especialidadesDisponibles = useMemo(() => {
        const setEsp = new Set();
        datosFiltradosDivision.forEach(d => setEsp.add((d.especialidad || 'Desconocida').trim()));
        return [...setEsp].sort();
    }, [datosFiltradosDivision]);

    const datosFiltrados = useMemo(() => {
        return datosFiltradosDivision.filter(item => {
            if (especialidadSeleccionada === 'todas') return true;
            return (item.especialidad || 'Desconocida').trim().toUpperCase() === especialidadSeleccionada.toUpperCase();
        });
    }, [datosFiltradosDivision, especialidadSeleccionada]);

    // ==========================================
    // GRÁFICA DE METAS CON CALENDARIO DINÁMICO HISTÓRICO
    // ==========================================
    const chartMetas = useMemo(() => {
        // 1. Obtenemos el calendario exacto de las 5 semanas para el mes/año seleccionado
        const semanasOperativas = generarCalendarioIMSS(mesGraficoMeta, anioGraficoMeta);
        
        // Extraemos solo los textos para la gráfica (ej. "S1 (26-dic al 28-dic)")
        const labelsSemanas = semanasOperativas.map(s => s.label);
        const citasPorSemana = [0, 0, 0, 0, 0];

        // 2. Definimos las metas
        let metasPorSemana = [2646, 2646, 2646, 2646, 2646];
        
        // Regla directiva específica para Enero de 2026 (Semana 1 = 114)

        if (Number(mesGraficoMeta) === 0) {
            metasPorSemana[0] = 1134; // Ajustamos solo la Semana 1 de Enero
        }

        // 3. Agrupamos los datos reales en las "cubetas" (semanas) que generó el algoritmo
        datos.forEach(d => {
            const div = (d.division || 'Sin Asignar').trim();
            if (divisionSeleccionada !== 'todas' && div !== divisionSeleccionada) return;

            const esp = (d.especialidad || 'Desconocida').trim().toUpperCase();
            if (especialidadSeleccionada !== 'todas' && esp !== especialidadSeleccionada.toUpperCase()) return;

            // Extraemos la fecha
            let a = d.anio || d.Anio || d.ANIO || d.año || d.Año || d.AÑO;
            let m = d.mes || d.Mes || d.MES;
            let dia = 1;

            const f = encontrarFecha(d);
            if (f) {
                if (f.includes('-')) {
                    const p = f.split('-');
                    if (p[0].length === 4) { a = a || p[0]; m = m || p[1]; dia = p[2]; }
                    else { a = a || p[2]; m = m || p[1]; dia = p[0]; }
                } else if (f.includes('/')) {
                    const p = f.split('/');
                    if (p[0].length === 4) { a = a || p[0]; m = m || p[1]; dia = p[2]; }
                    else { a = a || p[2]; m = m || p[1]; dia = p[0]; }
                }
            }

            if (a && m && dia) {
                // Creamos la fecha del registro a las 12:00 del día (seguro contra zonas horarias)
                const fechaRegistro = new Date(parseInt(a), parseInt(m) - 1, parseInt(dia), 12, 0, 0);

                // Solo tomamos en cuenta los "Citados"
                if (d.citado === 'Citado' || d.CITADO === 'Citado' || (d.citado && String(d.citado).toLowerCase() === 'citado')) {
                    
                    // ¿En qué semana operativa cae esta fecha?
                    for (let i = 0; i < semanasOperativas.length; i++) {
                        const sem = semanasOperativas[i];
                        if (!sem.vacia && fechaRegistro >= sem.inicio && fechaRegistro <= sem.fin) {
                            citasPorSemana[i]++;
                            break; // Ya la acomodamos, salimos del ciclo de semanas
                        }
                    }
                }
            }
        });

        return {
            labels: labelsSemanas,
            datasets: [
                {
                    label: 'Citas Reales',
                    data: citasPorSemana,
                    borderColor: '#0284c7', 
                    backgroundColor: 'rgba(2, 132, 199, 0.1)', 
                    borderWidth: 3,
                    tension: 0.3, 
                    fill: true,
                    pointBackgroundColor: '#0284c7',
                    pointRadius: 5
                },
                {
                    label: 'Meta Esperada',
                    data: metasPorSemana,
                    borderColor: '#822626', 
                    backgroundColor: 'transparent',
                    borderDash: [5, 5], 
                    borderWidth: 2,
                    tension: 0, 
                    pointRadius: 0, 
                    fill: false
                }
            ]
        };
    }, [datos, divisionSeleccionada, especialidadSeleccionada, mesGraficoMeta, anioGraficoMeta]);

    const chartOptionsLine = {
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top', labels: { usePointStyle: true } },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
            x: { grid: { display: false } }
        }
    };

    // ==========================================
    // KPIs y GRÁFICAS CON DESGLOSE (PV/SUB)
    // ==========================================
    const kpis = useMemo(() => {
        let citados = 0, primeraVez = 0;
        datosFiltrados.forEach(d => {
            if (d.citado === 'Citado') citados++;
            if (d.primera_vez === 'Primera Vez') primeraVez++;
        });
        return {
            total: datosFiltrados.length,
            citados, espontaneos: datosFiltrados.length - citados,
            primeraVez, subsecuentes: datosFiltrados.length - primeraVez
        };
    }, [datosFiltrados]);

    const chartDivisiones = useMemo(() => {
        const conteo = datosFiltrados.reduce((acc, curr) => {
            const div = curr.division || 'Sin Asignar';
            if (!acc[div]) acc[div] = { total: 0, pv: 0, sub: 0 };
            
            acc[div].total++;
            if (curr.primera_vez === 'Primera Vez') acc[div].pv++; else acc[div].sub++;
            return acc;
        }, {});
        
        const ordenados = Object.entries(conteo).sort((a, b) => b[1].total - a[1].total);
        return {
            labels: ordenados.map(item => item[0]),
            datasets: [{ label: 'Consultas', data: ordenados.map(item => item[1].total), backgroundColor: ['#822626', '#D4C19C', '#475569', '#1e293b', '#b45309'], borderRadius: 4 }],
            dataPV: ordenados.map(item => item[1].pv),
            dataSub: ordenados.map(item => item[1].sub)
        };
    }, [datosFiltrados]);

    const chartTurnos = useMemo(() => {
        const conteo = datosFiltrados.reduce((acc, curr) => {
            const turno = curr.turno || 'Sin Asignar';
            if (!acc[turno]) acc[turno] = { total: 0, pv: 0, sub: 0 };
            
            acc[turno].total++;
            if (curr.primera_vez === 'Primera Vez') acc[turno].pv++; else acc[turno].sub++;
            return acc;
        }, {});
        
        const ordenados = Object.entries(conteo).sort((a, b) => b[1].total - a[1].total);
        return {
            labels: ordenados.map(item => item[0]),
            datasets: [{ data: ordenados.map(item => item[1].total), backgroundColor: ['#475569', '#822626', '#D4C19C', '#1e293b', '#b45309'], borderWidth: 0 }],
            dataPV: ordenados.map(item => item[1].pv),
            dataSub: ordenados.map(item => item[1].sub)
        };
    }, [datosFiltrados]);

const chartEspecialidades = useMemo(() => {
        if (!datosFiltrados || datosFiltrados.length === 0) return { labels: [], datasets: [], dataPV: [], dataSub: [] };

        const conteo = datosFiltrados.reduce((acc, curr) => {
            const nombreRaw = curr.especialidad || curr.ESPECIALIDAD || 'Desconocida';
            const nombreLimpio = String(nombreRaw).trim().toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace('.0', '');
            
            // LA MAGIA ESTÁ AQUÍ:
            // Buscamos el nombre bonito oficial en MySQL. Si MySQL no lo tiene (porque aún no lo agregas en el CRUD), 
            // simplemente usamos el nombre crudo que ya traía el archivo. ¡Cero errores!
            let esp = diccionarioEspecialidades[nombreLimpio]?.nombre || nombreRaw; 

            if (!acc[esp]) acc[esp] = { total: 0, pv: 0, sub: 0 };
            
            acc[esp].total++;
            if (curr.primera_vez === 'Primera Vez') acc[esp].pv++; 
            else acc[esp].sub++;
            
            return acc;
        }, {});
        
        const ordenados = Object.entries(conteo).sort((a, b) => b[1].total - a[1].total);
        return {
            labels: ordenados.map(item => item[0]),
            datasets: [{ label: 'Consultas', data: ordenados.map(item => item[1].total), backgroundColor: '#334155', borderRadius: 4 }],
            dataPV: ordenados.map(item => item[1].pv),
            dataSub: ordenados.map(item => item[1].sub)
        };
    }, [datosFiltrados, diccionarioEspecialidades]);

const chartMedicos = useMemo(() => {
        const conteo = datosFiltrados.reduce((acc, curr) => {
            // Limpiamos la matrícula
            const matriculaLimpia = String(curr.matricula_medico || 'Sin Matrícula').trim().replace('.0', '').replace(/\s/g, '');
            const nombreMedico = diccionarioMedicos[matriculaLimpia] || `Matr. ${curr.matricula_medico}`;
            
            // LA MAGIA: Traducimos la especialidad de ESTE registro usando el diccionario
            const espRaw = curr.especialidad || curr.ESPECIALIDAD || 'Desconocida';
            const espLimpia = String(espRaw).trim().toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace('.0', '');
            const nombreEspecialidad = diccionarioEspecialidades[espLimpia]?.nombre || espRaw;

            // Guardamos el médico y su especialidad
            if (!acc[nombreMedico]) {
                acc[nombreMedico] = { total: 0, pv: 0, sub: 0, especialidad: nombreEspecialidad };
            }
            
            acc[nombreMedico].total++;
            if (curr.primera_vez === 'Primera Vez') acc[nombreMedico].pv++; else acc[nombreMedico].sub++;
            return acc;
        }, {});
        
        const ordenados = Object.entries(conteo).sort((a, b) => b[1].total - a[1].total);
        const top = ordenados.slice(0, 20);
        
        return {
            labels: top.map(item => item[0]),
            datasets: [{ label: 'Consultas', data: top.map(item => item[1].total), backgroundColor: '#822626', borderRadius: 4 }],
            dataPV: top.map(item => item[1].pv),
            dataSub: top.map(item => item[1].sub),
            // Mandamos el arreglo de especialidades a la tabla
            dataExtra: top.map(item => item[1].especialidad) 
        };
    // CRÍTICO: Agregamos diccionarioEspecialidades a las dependencias
    }, [datosFiltrados, diccionarioMedicos, diccionarioEspecialidades]);

    const chartConsultorios = useMemo(() => {
        const conteo = datosFiltrados.reduce((acc, curr) => {
            const consultorio = curr.consultorio || 'Sin Asignar';
            if (!acc[consultorio]) acc[consultorio] = { total: 0, pv: 0, sub: 0 };
            
            acc[consultorio].total++;
            if (curr.primera_vez === 'Primera Vez') acc[consultorio].pv++; else acc[consultorio].sub++;
            return acc;
        }, {});
        
        const ordenados = Object.entries(conteo).sort((a, b) => b[1].total - a[1].total);
        const top = ordenados.slice(0, 20);
        return {
            labels: top.map(item => `Cons. ${item[0]}`),
            datasets: [{ label: 'Consultas', data: top.map(item => item[1].total), backgroundColor: '#b45309', borderRadius: 4 }],
            dataPV: top.map(item => item[1].pv),
            dataSub: top.map(item => item[1].sub)
        };
    }, [datosFiltrados]);

const chartDiagnosticos = useMemo(() => {
        const conteo = datosFiltrados.reduce((acc, curr) => {
            // Tomamos el código crudo del CSV (ej. "J00X")
            const codigoRaw = String(curr.diagnostico_principal || 'No Especificado').trim().toUpperCase();
            
            // TRADUCCIÓN MÁGICA: Buscamos el código en el diccionario CIE. 
            // Si no lo encuentra, muestra el código original.
            const nombreEnfermedad = diccionarioCIE[codigoRaw] || codigoRaw;
            
            if (!acc[nombreEnfermedad]) acc[nombreEnfermedad] = { total: 0, pv: 0, sub: 0 };
            
            acc[nombreEnfermedad].total++;
            if (curr.primera_vez === 'Primera Vez') acc[nombreEnfermedad].pv++; else acc[nombreEnfermedad].sub++;
            return acc;
        }, {});
        
        const ordenados = Object.entries(conteo).sort((a, b) => b[1].total - a[1].total).slice(0, 20);
        return {
            labels: ordenados.map(item => item[0]),
            datasets: [{ label: 'Frecuencia', data: ordenados.map(item => item[1].total), backgroundColor: '#1e293b', borderRadius: 4 }],
            dataPV: ordenados.map(item => item[1].pv),
            dataSub: ordenados.map(item => item[1].sub)
        };
    }, [datosFiltrados, diccionarioCIE]); // <-- No olvides agregar diccionarioCIE a las dependencias del useMemo

    const anchoDinamico = (cantidadItems) => `max(100%, ${cantidadItems * 40}px)`; 
    
    const chartOptionsVertical = {
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { maxRotation: 45, minRotation: 45, autoSkip: false } },
            y: { grid: { display: true, color: '#f1f5f9' }, beginAtZero: true }
        }
    };

if (vistaActiva === 'menu') {
    return (
        <MenuPrincipal 
            setVistaActiva={setVistaActiva} 
            isAdmin={isAdmin} 
            setMensaje={setMensaje} 
        />
    );
}
    //Vista subir CSV
    if (vistaActiva === 'subir') {
        return (
            <ModuloCarga 
                setVistaActiva={setVistaActiva} 
                setMensaje={setMensaje} 
                mensaje={mensaje} 
                cargarDatos={cargarDatos} 
            />
        );
    }

    if (vistaActiva === 'catalogos') {
        //llamar a todo el archivo separado
        return <AdministradorCatalogos setVistaActiva={setVistaActiva} />;
    }

    return (
        <div className="flex h-screen bg-slate-50 overflow-hidden font-sans">
            
            {/* PANEL LATERAL */}
            <aside className={`bg-[#822626] text-slate-100 flex flex-col hidden md:flex shrink-0 shadow-xl z-20 transition-all duration-300 ${sidebarCollapsed ? 'w-20' : 'w-64'}`}>
                <div className="h-16 flex items-center justify-center border-b border-[#6b1f1f] shrink-0">
                    <button onClick={() => setSidebarCollapsed(!sidebarCollapsed)} className="w-full h-full flex items-center justify-center hover:bg-[#6b1f1f] transition-colors text-white">
                        {sidebarCollapsed ? <ChevronRight size={24} /> : <ChevronLeft size={24} />}
                    </button>
                </div>
                <nav className="flex-1 overflow-y-auto py-4 px-3 space-y-2 overflow-x-hidden custom-scrollbar">
                    <button onClick={() => setAreaSidebar('consulta_externa')} className={`w-full flex items-center rounded-xl transition-all ${sidebarCollapsed ? 'justify-center p-3' : 'px-4 py-3 gap-3'} ${areaSidebar === 'consulta_externa' ? 'bg-[#6b1f1f] text-white font-bold shadow-md border-l-4 border-white' : 'hover:bg-[#962e2e] text-red-100 border-l-4 border-transparent'}`}>
                        <Stethoscope size={20} className="shrink-0" /> {!sidebarCollapsed && <span className="whitespace-nowrap">Consulta Externa Esp</span>}
                    </button>
                    <button onClick={() => setAreaSidebar('paramedicos')} className={`w-full flex items-center rounded-xl transition-all ${sidebarCollapsed ? 'justify-center p-3' : 'px-4 py-3 gap-3'} ${areaSidebar === 'paramedicos' ? 'bg-[#6b1f1f] text-white font-bold shadow-md border-l-4 border-white' : 'hover:bg-[#962e2e] text-red-100 border-l-4 border-transparent'}`}>
                        <Ambulance size={20} className="shrink-0" /> {!sidebarCollapsed && <span className="whitespace-nowrap">Paramédicos</span>}
                    </button>
                    <button onClick={() => setAreaSidebar('cirugia')} className={`w-full flex items-center rounded-xl transition-all ${sidebarCollapsed ? 'justify-center p-3' : 'px-4 py-3 gap-3'} ${areaSidebar === 'cirugia' ? 'bg-[#6b1f1f] text-white font-bold shadow-md border-l-4 border-white' : 'hover:bg-[#962e2e] text-red-100 border-l-4 border-transparent'}`}>
                        <Syringe size={20} className="shrink-0" /> {!sidebarCollapsed && <span className="whitespace-nowrap">Cirugía</span>}
                    </button>
                    <button onClick={() => setAreaSidebar('hospitalizacion')} className={`w-full flex items-center rounded-xl transition-all ${sidebarCollapsed ? 'justify-center p-3' : 'px-4 py-3 gap-3'} ${areaSidebar === 'hospitalizacion' ? 'bg-[#6b1f1f] text-white font-bold shadow-md border-l-4 border-white' : 'hover:bg-[#962e2e] text-red-100 border-l-4 border-transparent'}`}>
                        <Bed size={20} className="shrink-0" /> {!sidebarCollapsed && <span className="whitespace-nowrap">Hospitalización</span>}
                    </button>
                    <button onClick={() => setAreaSidebar('urgencias')} className={`w-full flex items-center rounded-xl transition-all ${sidebarCollapsed ? 'justify-center p-3' : 'px-4 py-3 gap-3'} ${areaSidebar === 'urgencias' ? 'bg-[#6b1f1f] text-white font-bold shadow-md border-l-4 border-white' : 'hover:bg-[#962e2e] text-red-100 border-l-4 border-transparent'}`}>
                        <Siren size={20} className="shrink-0" /> {!sidebarCollapsed && <span className="whitespace-nowrap">Urgencias</span>}
                    </button>
                </nav>
                <div className="p-3 border-t border-[#6b1f1f] flex flex-col gap-2 shrink-0">
                    <button onClick={() => setMostrarTablas(!mostrarTablas)} className={`w-full flex items-center rounded-xl transition-all ${sidebarCollapsed ? 'justify-center p-3' : 'px-4 py-3 gap-3'} ${mostrarTablas ? 'bg-[#5e1919] text-white shadow-inner' : 'hover:bg-[#962e2e] text-red-100'}`}>
                        <TableProperties size={20} className="shrink-0" /> {!sidebarCollapsed && <span className="whitespace-nowrap font-bold text-sm">{mostrarTablas ? 'Ocultar Tablas' : 'Mostrar Tablas'}</span>}
                    </button>
                    <button className={`w-full flex items-center rounded-xl transition-all ${sidebarCollapsed ? 'justify-center p-3 bg-emerald-600 hover:bg-emerald-500' : 'px-4 py-3 gap-3 bg-emerald-600 hover:bg-emerald-500'} text-white font-bold shadow-md`}>
                        <Download size={20} className="shrink-0 text-white" /> {!sidebarCollapsed && <span className="whitespace-nowrap text-sm">Descargar Excel</span>}
                    </button>
                    <button onClick={() => setVistaActiva('menu')} className={`w-full flex items-center bg-slate-800 hover:bg-slate-900 text-white rounded-xl transition-colors font-bold text-sm mt-2 ${sidebarCollapsed ? 'justify-center p-3' : 'justify-center gap-2 p-3'}`}>
                        <ArrowLeft size={16} className="shrink-0" /> {!sidebarCollapsed && <span className="whitespace-nowrap">Volver al Inicio</span>}
                    </button>
                </div>
            </aside>

            {/* ÁREA PRINCIPAL */}
            <div className="flex-1 flex flex-col h-screen overflow-hidden relative">
                <header className="bg-white border-b border-slate-200 shrink-0 px-4 md:px-8 py-3 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 z-10 transition-all duration-300 min-h-[70px]">
                    <div className="flex items-center gap-4">
                        <button onClick={() => setSidebarCollapsed(!sidebarCollapsed)} className="p-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition-colors hidden md:block">
                            <Menu size={20} />
                        </button>
                        <div>
                            <h1 className="text-xl md:text-2xl font-black text-slate-800 capitalize">{areaSidebar.replace('_', ' ')}</h1>                        </div>
                    </div>
                    
                    <div className="flex flex-wrap items-center gap-3 w-full xl:w-auto">
                        {areaSidebar === 'consulta_externa' && datos.length > 0 && !cargandoDatos && !error && (
                            <div className="flex items-center gap-2 bg-slate-50 rounded-lg p-1.5 border border-slate-200 shadow-inner flex-wrap w-full xl:w-auto">
                                <Filter size={14} className="text-[#822626] ml-2 hidden sm:block"/>
                                
                                <span className="font-bold text-slate-500 text-[10px] uppercase ml-1">Año:</span>
                                <select className="bg-transparent font-bold text-[#822626] text-sm outline-none cursor-pointer pr-1" value={anioSeleccionado} onChange={e=>setAnioSeleccionado(e.target.value)}>
                                    <option value="todos">Todos</option>
                                    {aniosDisponibles.map(a=><option key={a} value={a}>{a}</option>)}
                                </select>
                                <div className="w-px h-4 bg-slate-300 mx-1"></div>
                                <span className="font-bold text-slate-500 text-[10px] uppercase">Mes:</span>
                                <select className="bg-transparent font-bold text-[#822626] text-sm outline-none cursor-pointer pr-1" value={mesSeleccionado} onChange={e=>setMesSeleccionado(e.target.value)}>
                                    <option value="todos">Todos</option>
                                    {MESES.map((m, i) => <option key={i} value={i}>{m}</option>)}
                                    <option disabled>──────────</option>
                                    <option value="rango">Rango...</option>
                                </select>

                                {mesSeleccionado === 'rango' && (
                                    <>
                                        <div className="w-px h-4 bg-slate-300 mx-1"></div>
                                        <span className="font-bold text-slate-500 text-[10px] uppercase">De:</span>
                                        <select className="bg-transparent font-bold text-[#822626] text-sm outline-none cursor-pointer" value={mesInicio} onChange={e=>setMesInicio(Number(e.target.value))}>
                                            {MESES.map((m, i) => <option key={i} value={i}>{m}</option>)}
                                        </select>
                                        <span className="text-slate-400 font-bold">-</span>
                                        <span className="font-bold text-slate-500 text-[10px] uppercase">A:</span>
                                        <select className="bg-transparent font-bold text-[#822626] text-sm outline-none cursor-pointer" value={mesFin} onChange={e=>setMesFin(Number(e.target.value))}>
                                            {MESES.map((m, i) => <option key={i} value={i}>{m}</option>)}
                                        </select>
                                    </>
                                )}

                                <div className="w-px h-4 bg-slate-300 mx-1"></div>
                                <span className="font-bold text-slate-500 text-[10px] uppercase">División:</span>
                                <select className="bg-transparent font-bold text-[#822626] text-sm outline-none cursor-pointer pr-1 max-w-[100px] sm:max-w-[150px] truncate" value={divisionSeleccionada} onChange={e=>setDivisionSeleccionada(e.target.value)}>
                                    <option value="todas">Todas</option>
                                    {divisionesDisponibles.map(d=><option key={d} value={d}>{d}</option>)}
                                </select>

                                <div className="w-px h-4 bg-slate-300 mx-1"></div>
                                <span className="font-bold text-slate-500 text-[10px] uppercase">Especialidad:</span>
                                <select className="bg-transparent font-bold text-[#822626] text-sm outline-none cursor-pointer pr-1 max-w-[100px] sm:max-w-[150px] truncate" value={especialidadSeleccionada} onChange={e=>setEspecialidadSeleccionada(e.target.value)}>
                                    <option value="todas">Todas</option>
                                    {especialidadesDisponibles.map(e=><option key={e} value={e}>{e}</option>)}
                                </select>
                            </div>
                        )}
                    </div>
                </header>

                <main className="flex-1 overflow-y-auto p-4 md:p-8 custom-scrollbar bg-slate-50">
                    {areaSidebar === 'consulta_externa' && (
                        <>
                            {cargandoDatos ? (
                                <div className="flex justify-center items-center py-20 text-[#822626] font-bold"><Activity className="animate-spin mr-3"/> Calculando estadísticas...</div>
                            ) : error ? (
                                <div className="text-red-600 font-bold text-center py-20">{error}</div>
                            ) : datosFiltrados.length === 0 ? (
                                <div className="text-center p-16 border-2 border-dashed border-slate-300 rounded-2xl text-slate-400 mt-10">
                                    <Activity size={48} className="mx-auto mb-4 opacity-50" />
                                    <p className="font-bold text-lg">No hay datos para esta selección</p>
                                    <p className="text-sm">Intenta cambiando el filtro de fecha o eligiendo otra área.</p>
                                </div>
                            ) : (
                                <div className="max-w-[1600px] mx-auto w-full pb-8">

                                    <div className="flex justify-end mb-4">
                                        <p className="text-sm font-bold text-slate-500 bg-white shadow-sm px-4 py-2 rounded-lg border border-slate-200 inline-flex items-center gap-2 animate-in fade-in duration-500">
                                            <Activity size={16} className="text-[#822626]" />
                                            Actualizado hasta: <span className="text-[#822626] font-black">{ultimaFechaBD}</span>
                                        </p>
                                    </div>
                                    
                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                        <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 border-t-4 border-t-[#822626]">
                                            <div className="flex items-center gap-3 text-slate-500 mb-2"><Users size={18}/><h3 className="text-xs font-bold uppercase tracking-widest">Total Consultas</h3></div>
                                            <p className="text-4xl font-black text-[#822626]">{kpis.total.toLocaleString()}</p>
                                        </div>

                                        <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
                                            <div className="flex items-center gap-3 text-slate-500 mb-2"><CalendarCheck size={18}/><h3 className="text-xs font-bold uppercase tracking-widest">Citados</h3></div>
                                            <p className="text-4xl font-black text-slate-700">{kpis.citados.toLocaleString()}</p>
                                            <div className="flex flex-col mt-5 pt-4 border-t border-slate-100">
                                                <span className="text-xs font-bold uppercase tracking-widest text-slate-500 mb-1">Espontáneos</span>
                                                <p className="text-4xl font-black text-[#822626]">{kpis.espontaneos.toLocaleString()}</p>
                                            </div>
                                        </div>

                                        <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
                                            <div className="flex items-center gap-3 text-slate-500 mb-2"><Clock size={18}/><h3 className="text-xs font-bold uppercase tracking-widest">Primera Vez</h3></div>
                                            <p className="text-4xl font-black text-[#c2410c]">{kpis.primeraVez.toLocaleString()}</p>
                                            <div className="flex flex-col mt-5 pt-4 border-t border-slate-100">
                                                <span className="text-xs font-bold uppercase tracking-widest text-slate-500 mb-1">Subsecuentes</span>
                                                <p className="text-4xl font-black text-[#822626]">{kpis.subsecuentes.toLocaleString()}</p>
                                            </div>
                                        </div>
                                    </div>

                                    {/* GRÁFICO DE METAS SEMANALES */}
                                    <div className="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-6">
                                        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 border-b border-slate-100 pb-4">
                                            <div className="flex items-center gap-3">
                                                <div className="bg-blue-50 p-2 rounded-lg"><Target size={24} className="text-blue-700"/></div>
                                                <div>
                                                    <h3 className="font-bold text-slate-800 uppercase tracking-wide">Cumplimiento de Meta de Citas (Por Semana)</h3>
                                                    <p className="text-xs text-slate-500">Visualiza las citas otorgadas contra el objetivo directivo del mes.</p>
                                                </div>
                                            </div>

                                            <div className="flex items-center gap-2 bg-slate-50 rounded-lg p-2 border border-slate-200">
                                                <select className="bg-transparent font-bold text-slate-700 text-sm outline-none cursor-pointer" value={mesGraficoMeta} onChange={e => setMesGraficoMeta(Number(e.target.value))}>
                                                    {MESES.map((m, i) => <option key={i} value={i}>{m}</option>)}
                                                </select>
                                                <div className="w-px h-4 bg-slate-300"></div>
                                                <select className="bg-transparent font-bold text-slate-700 text-sm outline-none cursor-pointer" value={anioGraficoMeta} onChange={e => setAnioGraficoMeta(Number(e.target.value))}>
                                                    {aniosDisponibles.map(a => <option key={a} value={a}>{a}</option>)}
                                                    {!aniosDisponibles.includes('2025') && <option value="2025">2025</option>}
                                                </select>
                                            </div>
                                        </div>

                                        <div className="relative min-h-[300px] w-full">
                                            <Line data={chartMetas} options={chartOptionsLine} />
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                        <div className="bg-gradient-to-br from-slate-800 to-slate-900 p-6 rounded-xl shadow-lg border border-slate-700 flex flex-col justify-center text-white relative overflow-hidden min-h-[220px]">
                                            <div className="absolute -bottom-4 -right-4 p-4 opacity-5 transform rotate-12"><Award size={180} /></div>
                                            <div className="relative z-10 flex flex-col items-start h-full justify-between">
                                                <div className="flex items-center gap-3 text-slate-300 w-full border-b border-slate-700/50 pb-3 mb-4">
                                                    <div className="bg-amber-400/20 p-2 rounded-lg"><Award size={20} className="text-amber-400"/></div>
                                                    <h3 className="text-sm font-bold uppercase tracking-widest truncate w-full" title={divisionSeleccionada === 'todas' ? 'Panorama de Divisiones' : divisionSeleccionada}>
                                                        {divisionSeleccionada === 'todas' ? 'Panorama de Divisiones' : divisionSeleccionada}
                                                    </h3>
                                                </div>
                                                <div className="mb-4">
                                                    <p className="text-sm text-slate-400 font-bold mb-1">{divisionSeleccionada === 'todas' ? 'Divisiones Activas' : 'Total Consultas en División'}</p>
                                                    <p className="text-5xl font-black text-white drop-shadow-md">{divisionSeleccionada === 'todas' ? rankingDivisiones.length : (rankingDivisiones.find(r => r[0] === divisionSeleccionada)?.[1] || 0).toLocaleString()}</p>
                                                </div>
                                                <div className="inline-block bg-slate-950/50 border border-slate-700 px-4 py-2 rounded-xl mt-auto w-full">
                                                    <p className="text-sm font-bold text-amber-400 flex items-center gap-2">
                                                        <Activity size={16} />{divisionSeleccionada === 'todas' ? 'Mostrando todas las áreas' : `Ranking: #${rankingDivisiones.findIndex(r => r[0] === divisionSeleccionada) + 1} de ${rankingDivisiones.length} divisiones`}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="bg-gradient-to-br from-slate-800 to-slate-900 p-6 rounded-xl shadow-lg border border-slate-700 flex flex-col justify-center text-white relative overflow-hidden min-h-[220px]">
                                            <div className="absolute -bottom-4 -right-4 p-4 opacity-5 transform rotate-12"><Stethoscope size={180} /></div>
                                            <div className="relative z-10 flex flex-col items-start h-full justify-between">
                                                <div className="flex items-center gap-3 text-slate-300 w-full border-b border-slate-700/50 pb-3 mb-4">
                                                    <div className="bg-emerald-400/20 p-2 rounded-lg"><Stethoscope size={20} className="text-emerald-400"/></div>
                                                    <h3 className="text-sm font-bold uppercase tracking-widest truncate w-full" title={especialidadSeleccionada === 'todas' ? 'Panorama de Especialidades' : especialidadSeleccionada}>
                                                        {especialidadSeleccionada === 'todas' ? 'Panorama de Especialidades' : especialidadSeleccionada}
                                                    </h3>
                                                </div>
                                                <div className="mb-4">
                                                    <p className="text-sm text-slate-400 font-bold mb-1">{especialidadSeleccionada === 'todas' ? 'Especialidades en la vista' : 'Total Consultas en Especialidad'}</p>
                                                    <p className="text-5xl font-black text-white drop-shadow-md">{especialidadSeleccionada === 'todas' ? especialidadesDisponibles.length : (infoEspecialidades.ranking.find(r => r[0] === especialidadSeleccionada)?.[1] || 0).toLocaleString()}</p>
                                                </div>
                                                <div className="inline-block bg-slate-950/50 border border-slate-700 px-4 py-2 rounded-xl mt-auto w-full">
                                                    <p className="text-sm font-bold text-emerald-400 flex flex-col gap-1">
                                                        <span className="flex items-center gap-2"><Activity size={16} />{especialidadSeleccionada === 'todas' ? 'Mostrando todas las especialidades' : `Global: #${infoEspecialidades.ranking.findIndex(r => r[0] === especialidadSeleccionada) + 1} de ${infoEspecialidades.ranking.length} especialidades`}</span>
                                                        {especialidadSeleccionada !== 'todas' && (<span className="text-xs text-slate-400 font-normal">Pertenece a: {infoEspecialidades.divMap[especialidadSeleccionada]}</span>)}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* COMPONENTES DE TABLA CON NUEVOS PROPS (dataPV, dataSub) */}
                                    <div className={`grid grid-cols-1 ${divisionSeleccionada === 'todas' ? 'lg:grid-cols-2' : ''} gap-6 mb-6 items-start`}>
                                        {divisionSeleccionada === 'todas' && (
                                            <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col h-full min-h-[300px]">
                                                <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Distribución por División</h3>
                                                <div className="relative flex-1 min-h-[220px]">
                                                    <Bar data={chartDivisiones} options={{ maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true }, x: { grid: { display: false } } } }} />
                                                </div>
                                                {mostrarTablas && <TablaDatos titulo1="División" titulo2="Consultas" labels={chartDivisiones.labels} data={chartDivisiones.datasets[0].data} dataPV={chartDivisiones.dataPV} dataSub={chartDivisiones.dataSub} />}
                                            </div>
                                        )}
                                        <div className={`bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col h-full min-h-[300px] ${divisionSeleccionada !== 'todas' ? 'w-full lg:w-1/2 mx-auto' : ''}`}>
                                            <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Consultas por Turno</h3>
                                            <div className="relative flex-1 min-h-[220px]">
                                                <Doughnut data={chartTurnos} options={{ maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }} />
                                            </div>
                                            {mostrarTablas && <TablaDatos titulo1="Turno" titulo2="Consultas" labels={chartTurnos.labels} data={chartTurnos.datasets[0].data} dataPV={chartTurnos.dataPV} dataSub={chartTurnos.dataSub} />}
                                        </div>
                                    </div>

                                    <div className="flex flex-col gap-6">
                                        {especialidadSeleccionada === 'todas' && (
                                            <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col">
                                                <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Distribución por Especialidades</h3>
                                                <div className={`flex-1 grid grid-cols-1 ${mostrarTablas ? 'lg:grid-cols-5 gap-6' : 'lg:grid-cols-1'}`}>
                                                    <div className={`relative overflow-x-auto custom-scrollbar pb-4 ${mostrarTablas ? 'lg:col-span-3' : 'lg:col-span-1'}`} style={{ height: '400px' }}>
                                                        <div style={{ width: anchoDinamico(chartEspecialidades.labels.length), height: '100%' }}>
                                                            <Bar data={chartEspecialidades} options={chartOptionsVertical} />
                                                        </div>
                                                    </div>
                                                    {mostrarTablas && <div className="lg:col-span-2 h-[400px] overflow-hidden"><TablaDatos titulo1="Especialidad" titulo2="Consultas" labels={chartEspecialidades.labels} data={chartEspecialidades.datasets[0].data} dataPV={chartEspecialidades.dataPV} dataSub={chartEspecialidades.dataSub} total={true} /></div>}
                                                </div>
                                            </div>
                                        )}

                                        <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col">
                                            <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Top 20 Productividad por Médico</h3>
                                            <div className={`flex-1 grid grid-cols-1 ${mostrarTablas ? 'lg:grid-cols-5 gap-6' : 'lg:grid-cols-1'}`}>
                                                <div className={`relative overflow-x-auto custom-scrollbar pb-4 ${mostrarTablas ? 'lg:col-span-3' : 'lg:col-span-1'}`} style={{ height: '400px' }}>
                                                    <div style={{ width: anchoDinamico(chartMedicos.labels.length), height: '100%' }}>
                                                        <Bar data={chartMedicos} options={chartOptionsVertical} />
                                                    </div>
                                                </div>
                                                {/* AQUÍ ESTÁ LA NUEVA TABLA CON LA ESPECIALIDAD INCLUIDA */}
                                                {mostrarTablas && (
                                                    <div className="lg:col-span-2 h-[400px] overflow-hidden">
                                                        <TablaDatos 
                                                            titulo1="Médico" 
                                                            tituloExtra="Especialidad" 
                                                            dataExtra={chartMedicos.dataExtra} 
                                                            titulo2="Consultas" 
                                                            labels={chartMedicos.labels} 
                                                            data={chartMedicos.datasets[0].data} 
                                                            dataPV={chartMedicos.dataPV} 
                                                            dataSub={chartMedicos.dataSub} 
                                                            total={true} 
                                                        />
                                                    </div>
                                                )}
                                            </div>
                                        </div>

                                        <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col">
                                            <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Top 20 Diagnósticos Principales</h3>
                                            <div className={`flex-1 grid grid-cols-1 ${mostrarTablas ? 'lg:grid-cols-5 gap-6' : 'lg:grid-cols-1'}`}>
                                                <div className={`relative overflow-x-auto custom-scrollbar pb-4 ${mostrarTablas ? 'lg:col-span-3' : 'lg:col-span-1'}`} style={{ height: '400px' }}>
                                                    <div style={{ width: anchoDinamico(chartDiagnosticos.labels.length), height: '100%' }}>
                                                        <Bar data={chartDiagnosticos} options={chartOptionsVertical} />
                                                    </div>
                                                </div>
                                                {mostrarTablas && <div className="lg:col-span-2 h-[400px] overflow-hidden"><TablaDatos titulo1="Diagnóstico" titulo2="Frecuencia" labels={chartDiagnosticos.labels} data={chartDiagnosticos.datasets[0].data} dataPV={chartDiagnosticos.dataPV} dataSub={chartDiagnosticos.dataSub} total={false} /></div>}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </>
                    )}

                    {/* MÓDULO PARAMÉDICOS INCRUSTADO */}
                    {areaSidebar === 'paramedicos' && (
                        <div className="max-w-[1600px] mx-auto w-full pb-8">
                            <TableroParamedicos datos={datos} />
                        </div>
                    )}

                    {/* MENSAJE DE EN CONSTRUCCIÓN (OCULTO PARA PARAMÉDICOS) */}
                    {areaSidebar !== 'consulta_externa' && areaSidebar !== 'paramedicos' && (
                        <div className="flex flex-col items-center justify-center h-full text-slate-400 p-16 border-2 border-dashed border-slate-300 rounded-3xl bg-slate-100/50">
                            <Activity size={64} className="mb-6 opacity-40 text-[#822626]" />
                            <h2 className="text-2xl font-black text-slate-500 mb-2">Módulo en Construcción</h2>
                            <p className="text-center max-w-md">El área de <strong>{areaSidebar.replace('_', ' ')}</strong> está siendo preparada.</p>
                        </div>
                    )}
                </main>
            </div>
        </div>
    );
}