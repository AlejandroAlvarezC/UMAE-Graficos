import React, { useState, useEffect, useMemo } from 'react';
import axios from 'axios';
import localforage from 'localforage';
import { UploadCloud, Activity, Users, CalendarCheck, Clock, ArrowLeft, BarChart2, Database, TableProperties, Stethoscope, Ambulance, Bed, Syringe, Siren, ChevronLeft, ChevronRight, Download, Filter, Menu, Award, Target, BookOpen, MapPin, ClipboardList} from 'lucide-react';
import { Bar, Doughnut, Line } from 'react-chartjs-2';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, LineElement, PointElement, Title, Tooltip, Legend, ArcElement } from 'chart.js';
import AdministradorCatalogos from './AdministradorCatalogos';
import MenuPrincipal from './MenuPrincipal';
import ModuloCarga from './ModuloCarga'; 
import TableroParamedicos from './TableroParamedicos';
import TableroUrgencias from './TableroUrgencias';

// Registrar componentes de Chart.js
ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, Title, Tooltip, Legend, ArcElement);

const MESES = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];

// ==========================================
// ALGORITMO DE CALENDARIO OPERATIVO (Regla 26 al 25)
// ==========================================
const generarCalendarioIMSS = (mesSeleccionado, anioSeleccionado) => {
    const anioAnterior = mesSeleccionado === 0 ? anioSeleccionado - 1 : anioSeleccionado;
    const mesAnterior = mesSeleccionado === 0 ? 11 : mesSeleccionado - 1;

    const fechaInicio = new Date(anioAnterior, mesAnterior, 26);
    const fechaFin = new Date(anioSeleccionado, mesSeleccionado, 25, 23, 59, 59);

    let semanas = [];
    let fechaActual = new Date(fechaInicio);
    let numeroSemana = 1;
    let inicioSemana = new Date(fechaActual);

    while (fechaActual <= fechaFin) {
        if (fechaActual.getDay() === 0 || fechaActual.getDate() === 25) {
            const finDeSemana = new Date(fechaActual);
            finDeSemana.setHours(23, 59, 59);

            semanas.push({
                semana: numeroSemana,
                inicio: new Date(inicioSemana),
                fin: finDeSemana
            });
            
            numeroSemana++;
            inicioSemana = new Date(fechaActual);
            inicioSemana.setDate(inicioSemana.getDate() + 1); 
        }
        fechaActual.setDate(fechaActual.getDate() + 1);
    }

    if (semanas.length > 5) {
        semanas[4].fin = semanas[semanas.length - 1].fin;
        semanas = semanas.slice(0, 5);
    }
    
    while(semanas.length < 5) {
         semanas.push({ semana: semanas.length + 1, vacia: true });
    }

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
// CACHÉ GLOBAL EN MEMORIA
// ==========================================
let cacheDatosProductividad = [];
let cacheDiccionarioMedicos = {};
let cacheEstaCargada = false;
let cacheDiccionarioCIE = {};

// ==========================================
// SUB-COMPONENTE: Tabla de Datos 
// ==========================================
const TablaDatos = ({ titulo1, titulo2, labels, data, dataPV, dataSub, tituloExtra, dataExtra, total = true }) => {
    if (!labels || !data) return null;
    
    const mostrarDesglose = dataPV && dataSub;
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
                                    <td className="py-2 px-3">{String(label).replace('Dr. ', 'Lic. ')}</td>
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
    
    // DICCIONARIOS
    const [diccionarioMedicos, setDiccionarioMedicos] = useState({});
    const [diccionarioCIE, setDiccionarioCIE] = useState({});
    const [diccionarioEspecialidades, setDiccionarioEspecialidades] = useState({});

    // ==========================================
    // CARGA DE DATOS 
    // ==========================================
    const cargarDatos = async () => {
        try {
            await localforage.removeItem('cache_productividad_vencer');
            await localforage.removeItem('version_productividad_vencer');
            
            const datosLocales = await localforage.getItem('cache_productividad_vencer');
            const versionLocal = await localforage.getItem('version_productividad_vencer') || "0";

            if (datosLocales && datosLocales.length > 0) {
                setDatos(datosLocales); 
            } else {
                setCargandoDatos(true); 
            }

            const resVersion = await axios.get('/api/api_check_update.php');
            const versionServidor = String(resVersion.data.ultima_actualizacion);

            if (versionServidor !== versionLocal || !datosLocales) {
                const resDatos = await axios.get('/api/api_productividad.php');
                if (Array.isArray(resDatos.data)) {
                    setDatos(resDatos.data);
                    await localforage.setItem('cache_productividad_vencer', resDatos.data);
                    await localforage.setItem('version_productividad_vencer', versionServidor);
                }
            }
        } catch (err) {
            setError("Modo sin conexión. Mostrando últimos datos guardados.");
        } finally {
            setCargandoDatos(false);
        }
    };

    useEffect(() => {
        if (vistaActiva === 'dashboard' && datos.length === 0) {
            cargarDatos();
        }
    }, [vistaActiva]);

    const cargarDiccionario = async () => {
        try {
            const res = await axios.get('/api/api_medicos.php'); 
            if (Array.isArray(res.data) && res.data.length > 0) {
                const dicc = res.data.reduce((acc, medico) => {
                    let mat = String(medico.matricula || '').trim().replace('.0', '').replace(/\s/g, '');
                    const nom = String(medico.nombre || '').trim();
                    if (mat && nom) acc[mat] = nom;
                    return acc;
                }, {});
                cacheDiccionarioMedicos = dicc;
                setDiccionarioMedicos(dicc);
                await localforage.setItem('cache_medicos_vencer', dicc);
            }
        } catch (err) {
            console.error("Error al cargar médicos", err);
        }
    };

    const cargarDiccionarioCIE = async () => {
        try {
            await localforage.removeItem('cache_cie_vencer');
            cacheDiccionarioCIE = {}; 
            const res = await axios.get(`/api/api_cie.php?t=${new Date().getTime()}`);
            if (Array.isArray(res.data)) {
                const dicc = res.data.reduce((acc, item) => {
                    const cod = String(item.codigo || '').trim().toUpperCase();
                    const desc = String(item.descripcion || '').trim();
                    if (cod && desc) acc[cod] = desc;
                    return acc;
                }, {});
                cacheDiccionarioCIE = dicc;
                setDiccionarioCIE(dicc);
                await localforage.setItem('cache_cie_vencer', dicc);
            }
        } catch (err) {
            console.error("Error catálogo CIE", err);
        }
    };

    const cargarDiccionarioEspecialidades = async () => {
        try {
            await localforage.removeItem('cache_especialidades_vencer');
            const res = await axios.get(`/api/api_crud_especialidades.php?t=${new Date().getTime()}`);
            if (Array.isArray(res.data)) {
                const dicc = res.data.reduce((acc, item) => {
                    const clave = String(item.clave).trim().toUpperCase();
                    if (clave) {
                        acc[clave] = { nombre: item.nombre, division: item.division };
                    }
                    return acc;
                }, {});
                setDiccionarioEspecialidades(dicc);
                await localforage.setItem('cache_especialidades_vencer', dicc);
            }
        } catch (error) {
            console.error("Error especialidades", error);
        }
    };

    useEffect(() => {
        cargarDatos();
        cargarDiccionario();
        cargarDiccionarioCIE(); 
        cargarDiccionarioEspecialidades();
    }, []);

    // RESETEAR ESPECIALIDAD CUANDO CAMBIA LA DIVISIÓN O DE ÁREA
    useEffect(() => {
        setEspecialidadSeleccionada('todas');
    }, [divisionSeleccionada, areaSidebar]);


    // ==========================================
    // TRADUCTOR GLOBAL (Vacunado contra el "COD:")
    // ==========================================
    const traducirEspecialidad = (valorCrudo) => {
        if (!valorCrudo) return 'Desconocida';
        
        // 1. Lo pasamos a mayúsculas y le quitamos acentos
        let espRaw = String(valorCrudo).trim().toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        
        // 2. ¡LA VACUNA! Le arrancamos la palabra "COD:" y los ".0"
        espRaw = espRaw.replace('COD:', '').replace('COD: ', '').replace('.0', '').trim();
        
        // Seguro de vida
        const respaldoInquebrantable = {
            '6300': 'TRABAJO SOCIAL',
            '6600': 'PSICOLOGIA',
            '6900': 'NUTRICION',
            '5001': 'CONSULTAS EN PRIMER CONTACTO',
            'A600': 'URGENCIAS TOCO CIRUGIA'
        };
        
        return diccionarioEspecialidades[espRaw]?.nombre || respaldoInquebrantable[espRaw] || espRaw;
    };

    // ==========================================
    // FUNCIÓN DE NIVELACIÓN (Para comparar sin fallos)
    // ==========================================
    const nivelarTexto = (texto) => {
        return String(texto || '').trim().toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    };
    // ==========================================
    // LÓGICA DE FILTRADO BASE
    // ==========================================
    const encontrarFecha = (obj) => {
        const keys = Object.keys(obj);
        for (let k of keys) {
            if (k.toLowerCase().includes('fecha')) return obj[k];
        }
        return null;
    };

    const ultimaFechaBD = useMemo(() => {
        if (!datos || datos.length === 0) return 'No disponible';
        let maxDate = new Date(2000, 0, 1); 
        let found = false;

        datos.forEach(d => {
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

            if (a && m) {
                const currentDate = new Date(parseInt(a), parseInt(m) - 1, parseInt(dia));
                if (currentDate > maxDate) {
                    maxDate = currentDate;
                    found = true;
                }
            }
        });

        if (!found) return 'No disponible';
        const dd = String(maxDate.getDate()).padStart(2, '0');
        const mm = String(maxDate.getMonth() + 1).padStart(2, '0');
        const yyyy = maxDate.getFullYear();
        return `${dd}/${mm}/${yyyy}`;
    }, [datos]);

    // ==========================================
    // CUBETAS DE DATOS BASE (Separación Robusta)
    // ==========================================
    const datosConsultaExterna = useMemo(() => {
        if (!datos || datos.length === 0) return [];
        return datos.filter(d => {
            const espCruda = String(d.especialidad || d.ESPECIALIDAD || '');
            const espNivelada = nivelarTexto(espCruda);
            const espTraducida = nivelarTexto(traducirEspecialidad(espCruda));
            
            // Si el nombre o el código pertenece a otra área, lo ignoramos de Consulta Externa
            const ignorar = ['TOCO', 'PRIMER CONTACTO', '5001', '6300', '6600', '6900', 'NUTRICION', 'INHALOTERAPIA', 'FONIATRIA', 'TRABAJO SOCIAL', 'PSICOLOGIA', 'REHABILITACION', 'URGENCIAS', 'ADMISION CONTINUA', 'OBSERVACION', 'CHOQUE'];
            
            return !ignorar.some(ignorada => espNivelada.includes(ignorada) || espTraducida.includes(ignorada));
        });
    }, [datos, diccionarioEspecialidades]);

    const aniosDisponibles = useMemo(() => {
        const anios = new Set();
        datos.forEach(d => {
            let a = d.anio || d.Anio || d.ANIO || d.año || d.Año || d.AÑO;
            if (a) {
                anios.add(String(a));
            } else {
                const f = encontrarFecha(d);
                if (f) {
                    const p = f.includes('-') ? f.split('-') : f.split('/');
                    anios.add(p[0].length === 4 ? p[0] : p[2]);
                }
            }
        });
        return [...anios].sort().reverse();
    }, [datos]);

    // FILTRO DE FECHAS
    const aplicarFiltroFecha = (listaDatos) => {
        return listaDatos.filter(item => {
            let a = item.anio || item.Anio || item.ANIO || item.año || item.Año || item.AÑO;
            let m = item.mes || item.Mes || item.MES;

            if (!a || !m) {
                const f = encontrarFecha(item);
                if (f) {
                    const parts = f.includes('-') ? f.split('-') : f.split('/');
                    if (parts[0].length === 4) { a = a || parts[0]; m = m || parts[1]; }
                    else { a = a || parts[2]; m = m || parts[1]; }
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
    };

    const datosFiltradosFecha = useMemo(() => aplicarFiltroFecha(datosConsultaExterna), [datosConsultaExterna, anioSeleccionado, mesSeleccionado, mesInicio, mesFin]);

    const datosParamedicosFiltrados = useMemo(() => {
        const soloParamedicos = datos.filter(d => {
            const esp = String(d.especialidad || d.ESPECIALIDAD || '').toUpperCase();
            const criterios = ['6300', '6600', '6900', 'NUTRICION', 'INHALOTERAPIA', 'FONIATRIA', 'REHABILITACION'];
            return criterios.some(c => esp.includes(c));
        });
        return aplicarFiltroFecha(soloParamedicos);
    }, [datos, anioSeleccionado, mesSeleccionado, mesInicio, mesFin]);

    const datosUrgenciasFiltrados = useMemo(() => {
        if (!datos || datos.length === 0) return [];

        const soloUrgencias = datos.filter(d => {
            const espCruda = String(d.especialidad || d.ESPECIALIDAD || d.servicio || '').toUpperCase();
            const espTraducida = traducirEspecialidad(espCruda);
            
            // Criterios de búsqueda: si el nombre tiene alguna de estas palabras, entra a Urgencias
            const criterios = [
                '5001', 'A600', 'URGENCIAS TOCOCIRUGIA','CONSULTAS EN PRIMER CONTACTO'
            ];
            
            return criterios.some(c => 
                espTraducida.includes(c) || espCruda.includes(c)
            );
        });

        // Aplicamos el filtro de fecha (¡Ojo con el año seleccionado!)
        return aplicarFiltroFecha(soloUrgencias);
    }, [datos, anioSeleccionado, mesSeleccionado, mesInicio, mesFin, diccionarioEspecialidades]);

    // ==========================================
    // DIVISIONES Y ESPECIALIDADES
    // ==========================================
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

    // ==========================================
    // MOTOR DE FILTRO PRINCIPAL
    // ==========================================
    const datosFiltrados = useMemo(() => {
        return datosFiltradosDivision.filter(item => {
            if (especialidadSeleccionada === 'todas') return true;
            
            // 1. Traduce y limpia al paciente (quita el COD:)
            const espTraducida = traducirEspecialidad(item.especialidad || item.ESPECIALIDAD);
            
            // 2. Compara el paciente con lo que elegiste en el menú (ambos sin acentos)
            return nivelarTexto(espTraducida) === nivelarTexto(especialidadSeleccionada);
        });
    }, [datosFiltradosDivision, especialidadSeleccionada, diccionarioEspecialidades]);

   const especialidadesParaMostrar = useMemo(() => {
        const setEsp = new Set();
        
        // 1. Recorremos TODA tu base de datos (catálogo) en lugar de leer el Excel
        Object.values(diccionarioEspecialidades).forEach(item => {
            if (item && item.nombre) {
                // Si el usuario eligió una División, solo metemos a la lista las especialidades de esa división
                if (divisionSeleccionada !== 'todas') {
                    const divItem = String(item.division || '').trim();
                    if (divItem !== divisionSeleccionada) return; 
                }

                // Limpiamos el nombre para que el menú se vea impecable
                const nombreLimpio = String(item.nombre).trim().toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                setEsp.add(nombreLimpio);
            }
        });

        // Seguro de vida: Si la BD tarda en responder, forzamos la aparición de estas
        if (Object.keys(diccionarioEspecialidades).length === 0) {
            if (areaSidebar === 'paramedicos') {
                ['TRABAJO SOCIAL', 'PSICOLOGIA', 'NUTRICION', 'REHABILITACION'].forEach(e => setEsp.add(e));
            } else if (areaSidebar === 'urgencias') {
                ['CONSULTAS EN PRIMER CONTACTO', 'URGENCIAS TOCOCIRUGIA'].forEach(e => setEsp.add(e));
            }
        }

        let listaCompleta = [...setEsp].sort();

        // 2. Filtramos la lista visual dependiendo de en qué pestaña estemos
        if (areaSidebar === 'paramedicos') {
            const criterios = ['TRABAJO SOCIAL', 'NUTRICION', 'PSICOLOGIA'];
            return listaCompleta.filter(esp => criterios.some(c => esp.includes(c)));
        }

        if (areaSidebar === 'urgencias') {
            const criterios = ['URGENCIAS TOCOCIRUGIA', 'CONSULTAS EN PRIMER CONTACTO'];
            return listaCompleta.filter(esp => criterios.some(c => esp.includes(c)));
        }

        // Si es consulta externa, limpiamos el menú ocultando lo que es de paramédicos o urgencias
        const ignorar = ['TRABAJO SOCIAL', 'NUTRICION', 'PSICOLOGIA', 'URGENCIAS TOCOCIRUGIA','CONSULTAS EN PRIMER CONTACTO'];
        return listaCompleta.filter(esp => !ignorar.some(ignorada => esp.includes(ignorada)));

    }, [diccionarioEspecialidades, areaSidebar, divisionSeleccionada]);

    // ==========================================
    // MOTORES DE FILTRO SECUNDARIOS (USANDO EL TRADUCTOR)
    // ==========================================
    const paramedicosParaTablero = useMemo(() => {
        return datosParamedicosFiltrados.filter(item => {
            if (especialidadSeleccionada === 'todas') return true;
            const espTraducida = traducirEspecialidad(item.especialidad || item.ESPECIALIDAD);
            return nivelarTexto(espTraducida) === nivelarTexto(especialidadSeleccionada);
        });
    }, [datosParamedicosFiltrados, especialidadSeleccionada, diccionarioEspecialidades]);

    const urgenciasParaTablero = useMemo(() => {
        return datosUrgenciasFiltrados.filter(item => {
            if (especialidadSeleccionada === 'todas') return true;
            const espTraducida = traducirEspecialidad(item.especialidad || item.ESPECIALIDAD || item.servicio);
            return nivelarTexto(espTraducida) === nivelarTexto(especialidadSeleccionada);
        });
    }, [datosUrgenciasFiltrados, especialidadSeleccionada, diccionarioEspecialidades]);

// ==========================================
    // GRÁFICA DE METAS CON CALENDARIO DINÁMICO HISTÓRICO
    // ==========================================
    const chartMetas = useMemo(() => {
        const semanasOperativas = generarCalendarioIMSS(mesGraficoMeta, anioGraficoMeta);
        const labelsSemanas = semanasOperativas.map(s => s.label);
        const citasPorSemana = [0, 0, 0, 0, 0];
        let metasPorSemana = [2646, 2646, 2646, 2646, 2646];
        
        if (Number(mesGraficoMeta) === 0) metasPorSemana[0] = 1134; 

        datosConsultaExterna.forEach(d => {
            const div = (d.division || 'Sin Asignar').trim();
            if (divisionSeleccionada !== 'todas' && div !== divisionSeleccionada) return;

           // Nivelamos y comparamos para la gráfica de metas
            const espTraducida = traducirEspecialidad(d.especialidad || d.ESPECIALIDAD);
            if (especialidadSeleccionada !== 'todas' && nivelarTexto(espTraducida) !== nivelarTexto(especialidadSeleccionada)) return;

            let a = d.anio || d.Anio || d.ANIO || d.año || d.Año || d.AÑO;
            let m = d.mes || d.Mes || d.MES;
            let dia = 1;

            const f = encontrarFecha(d);
            if (f) {
                const p = f.includes('-') ? f.split('-') : f.split('/');
                if (p[0].length === 4) { a = a || p[0]; m = m || p[1]; dia = p[2]; }
                else { a = a || p[2]; m = m || p[1]; dia = p[0]; }
            }

            if (a && m && dia) {
                const fechaRegistro = new Date(parseInt(a), parseInt(m) - 1, parseInt(dia), 12, 0, 0);
                if (d.citado === 'Citado' || d.CITADO === 'Citado' || (d.citado && String(d.citado).toLowerCase() === 'citado')) {
                    for (let i = 0; i < semanasOperativas.length; i++) {
                        const sem = semanasOperativas[i];
                        if (!sem.vacia && fechaRegistro >= sem.inicio && fechaRegistro <= sem.fin) {
                            citasPorSemana[i]++;
                            break; 
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
    // ¡AQUÍ ESTÁ LA MAGIA! Agregamos diccionarioEspecialidades a las dependencias
    }, [datosConsultaExterna, divisionSeleccionada, especialidadSeleccionada, mesGraficoMeta, anioGraficoMeta, diccionarioEspecialidades]);

    const chartOptionsLine = {
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { usePointStyle: true } }, tooltip: { mode: 'index', intersect: false } },
        scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } }
    };

    // ==========================================
    // KPIs y GRÁFICAS CON DESGLOSE
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
            // USANDO EL TRADUCTOR TAMBIÉN PARA LA GRÁFICA
            const esp = traducirEspecialidad(curr.especialidad || curr.ESPECIALIDAD); 

            if (!acc[esp]) acc[esp] = { total: 0, pv: 0, sub: 0 };
            acc[esp].total++;
            if (curr.primera_vez === 'Primera Vez') acc[esp].pv++; else acc[esp].sub++;
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
            const matriculaLimpia = String(curr.matricula_medico || 'Sin Matrícula').trim().replace('.0', '').replace(/\s/g, '');
            const nombreMedico = diccionarioMedicos[matriculaLimpia] || `Matr. ${curr.matricula_medico}`;
            
            // USANDO EL TRADUCTOR
            const nombreEspecialidad = traducirEspecialidad(curr.especialidad || curr.ESPECIALIDAD);

            if (!acc[nombreMedico]) acc[nombreMedico] = { total: 0, pv: 0, sub: 0, especialidad: nombreEspecialidad };
            
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
            dataExtra: top.map(item => item[1].especialidad) 
        };
    }, [datosFiltrados, diccionarioMedicos, diccionarioEspecialidades]);

    const chartDiagnosticos = useMemo(() => {
        const conteo = datosFiltrados.reduce((acc, curr) => {
            const codigoRaw = String(curr.diagnostico_principal || 'No Especificado').trim().toUpperCase();
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
    }, [datosFiltrados, diccionarioCIE]);

    const chartConsultorios = useMemo(() => {
        // 1. Si no hay datos, devolvemos el objeto vacío con la estructura correcta
        if (!datosFiltrados || datosFiltrados.length === 0) {
            return { labels: [], datasets: [], dataPV: [], dataSub: [] };
        }

        // 2. Reducimos los datos para contar totales, PV y Sub por Consultorio
        const conteo = datosFiltrados.reduce((acc, curr) => {
            // Normalizamos el nombre del consultorio (manejamos mayúsculas/minúsculas)
            const cons = (curr.consultorio || curr.CONSULTORIO || "SIN ESPECIFICAR").toString().trim().toUpperCase();

            if (!acc[cons]) acc[cons] = { total: 0, pv: 0, sub: 0 };
            
            acc[cons].total++;
            
            // Lógica de Primera Vez vs Subsecuente
            if (curr.primera_vez === 'Primera Vez' || curr.PRIMERA_VEZ === 'Primera Vez') {
                acc[cons].pv++;
            } else {
                acc[cons].sub++;
            }
            
            return acc;
        }, {});

        // 3. Ordenamos de mayor a menor según el total de consultas
        const ordenados = Object.entries(conteo).sort((a, b) => b[1].total - a[1].total);

        // 4. Retornamos la estructura lista para el gráfico
        return {
            labels: ordenados.map(item => item[0]),
            datasets: [{ 
                label: 'Consultas por Consultorio', 
                data: ordenados.map(item => item[1].total), 
                backgroundColor: '#10b981', // Verde esmeralda para diferenciarlo de especialidades
                borderRadius: 4 
            }],
            dataPV: ordenados.map(item => item[1].pv),
            dataSub: ordenados.map(item => item[1].sub)
        };
    }, [datosFiltrados]); // Solo se recalcula si cambian los datos filtrados

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
        return <MenuPrincipal setVistaActiva={setVistaActiva} isAdmin={isAdmin} setMensaje={setMensaje} />;
    }

    if (vistaActiva === 'subir') {
        return <ModuloCarga setVistaActiva={setVistaActiva} setMensaje={setMensaje} mensaje={mensaje} cargarDatos={cargarDatos} />;
    }

    if (vistaActiva === 'catalogos') {
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
                        {(areaSidebar === 'consulta_externa' || areaSidebar === 'paramedicos' || areaSidebar === 'urgencias') && datos.length > 0 && !cargandoDatos && !error && (
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
                                    <select 
                                        className="bg-transparent font-bold text-[#822626] text-sm outline-none cursor-pointer pr-1 max-w-[100px] sm:max-w-[150px] truncate" 
                                        value={especialidadSeleccionada} 
                                        onChange={e => setEspecialidadSeleccionada(e.target.value)}
                                    >
                                        <option value="todas">Todas</option>
                                        {especialidadesParaMostrar.map(e => (
                                            <option key={e} value={e}>{e}</option>
                                        ))}
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
                                                    <p className="text-5xl font-black text-white drop-shadow-md">{especialidadSeleccionada === 'todas' ? especialidadesParaMostrar.length : (infoEspecialidades.ranking.find(r => r[0] === especialidadSeleccionada)?.[1] || 0).toLocaleString()}</p>
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
                                                                                {/* CARD DE CONSULTORIOS CON TABLA INTEGRADA */}
                                        <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col">
                                            <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">
                                                Distribución por Consultorio
                                            </h3>
                                            
                                            <div className={`flex-1 grid grid-cols-1 ${mostrarTablas ? 'lg:grid-cols-5 gap-6' : 'lg:grid-cols-1'}`}>
                                                
                                                {/* LADO A: EL GRÁFICO */}
                                                <div className={`relative overflow-x-auto custom-scrollbar pb-4 ${mostrarTablas ? 'lg:col-span-3' : 'lg:col-span-1'}`} style={{ height: '400px' }}>
                                                    <div style={{ width: anchoDinamico(chartConsultorios.labels.length), height: '100%' }}>
                                                        <Bar 
                                                            data={chartConsultorios} 
                                                            options={chartOptionsVertical} 
                                                        />
                                                    </div>
                                                </div>

                                                {/* LADO B: LA TABLA (Esta es la lógica que faltaba) */}
                                                {mostrarTablas && (
                                                    <div className="lg:col-span-2 h-[400px] overflow-hidden">
                                                        <TablaDatos 
                                                            titulo1="Consultorio" 
                                                            titulo2="Consultas" 
                                                            labels={chartConsultorios.labels} 
                                                            data={chartConsultorios.datasets[0].data} 
                                                            dataPV={chartConsultorios.dataPV} 
                                                            dataSub={chartConsultorios.dataSub} 
                                                            total={true} 
                                                        />
                                                    </div>
                                                )}
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
                            <TableroParamedicos 
                                datos={paramedicosParaTablero} 
                                diccionarioMedicos={diccionarioMedicos} 
                                diccionarioCIE={diccionarioCIE} 
                                diccionarioEspecialidades={diccionarioEspecialidades} /* <--- AQUÍ LO MANDAMOS */
                                mostrarTablas={mostrarTablas} 
                            />
                        </div>
                    )}

                    {/* MÓDULO URGENCIAS INCRUSTADO */}
                    {areaSidebar === 'urgencias' && (
                        <div className="max-w-[1600px] mx-auto w-full pb-8 animate-in fade-in duration-500">
                            <TableroUrgencias 
                                datos={urgenciasParaTablero} 
                                diccionarioMedicos={diccionarioMedicos} 
                                diccionarioCIE={diccionarioCIE}
                                diccionarioEspecialidades={diccionarioEspecialidades} 
                                mostrarTablas={mostrarTablas} 
                            />
                        </div>
                    )}

                    {/* MENSAJE DE EN CONSTRUCCIÓN (OCULTO PARA PARAMÉDICOS Y URGENCIAS) */}
                    {areaSidebar !== 'consulta_externa' && areaSidebar !== 'paramedicos' && areaSidebar !== 'urgencias' && (
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