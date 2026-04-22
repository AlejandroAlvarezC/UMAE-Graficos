import React, { useState, useEffect } from 'react';
import axios from 'axios';
import localforage from 'localforage';
import { 
    Users, Stethoscope, MapPin, ClipboardList, 
    ChevronLeft, Menu, Pencil, Trash2, Plus, Loader2, X, UploadCloud
} from 'lucide-react';

export default function AdministradorCatalogos({ setVistaActiva }) {
    const [seccionCatalogo, setSeccionCatalogo] = useState('medicos');
    const [sidebarColapsado, setSidebarColapsado] = useState(false);

    // ==========================================
    // ESTADOS DE LOS CATÁLOGOS
    // ==========================================
    const [listaDiagnosticos, setListaDiagnosticos] = useState([]);
    const [listaEspecialidades, setListaEspecialidades] = useState([]);
    const [listaConsultorios, setListaConsultorios] = useState([]); 
    const [listaMedicos, setListaMedicos] = useState([]);
    const [cargando, setCargando] = useState(false);
    const [modalMedico, setModalMedico] = useState(false);
    const [medicoSeleccionado, setMedicoSeleccionado] = useState({ matricula: '', nombre: '' });
    const [esEdicion, setEsEdicion] = useState(false);

    useEffect(() => {
        if (seccionCatalogo === 'diagnosticos') cargarListaDiagnosticos();
        if (seccionCatalogo === 'especialidades') cargarListaEspecialidades();
        if (seccionCatalogo === 'consultorios') cargarListaConsultorios();
        if (seccionCatalogo === 'medicos') cargarListaMedicos();
    }, [seccionCatalogo]);

    const cargarListaDiagnosticos = async () => {
        setCargando(true);
        try {
            const res = await axios.get(`/api/api_cie.php?t=${new Date().getTime()}`);
            if (Array.isArray(res.data)) setListaDiagnosticos(res.data);
        } catch (error) { console.error("Error CIE-10:", error); } 
        finally { setCargando(false); }
    };

    const cargarListaEspecialidades = async () => {
        setCargando(true);
        try {
            const res = await axios.get(`/api/api_crud_especialidades.php?t=${new Date().getTime()}`);
            if (Array.isArray(res.data)) setListaEspecialidades(res.data);
        } catch (error) { console.error("Error Especialidades:", error); } 
        finally { setCargando(false); }
    };

    const cargarListaConsultorios = async () => {
        setCargando(true);
        try {
            const res = await axios.get(`/api/api_consultorios.php?t=${new Date().getTime()}`);
            if (Array.isArray(res.data)) setListaConsultorios(res.data);
        } catch (error) { console.error("Error Consultorios:", error); } 
        finally { setCargando(false); }
    };

    const cargarListaMedicos = async () => {
    setCargando(true);
    try {
        const res = await axios.get(`/api/api_medicos.php?t=${new Date().getTime()}`);
        if (Array.isArray(res.data)) {
            setListaMedicos(res.data);
        }
    } catch (error) {
        console.error("Error al cargar Médicos:", error);
    } finally {
        setCargando(false);
    }
};

    // ==========================================
    // LÓGICA DE CRUD GLOBAL
    // ==========================================
    const [modalAbierto, setModalAbierto] = useState(false);
    const [modoEdicion, setModoEdicion] = useState(false);
    const [guardando, setGuardando] = useState(false);
    
    const [formCIE, setFormCIE] = useState({ codigo: '', descripcion: '' });
    const [formEspecialidad, setFormEspecialidad] = useState({ clave: '', nombre: '', division: '' });
    const [formConsultorio, setFormConsultorio] = useState({ id: '', nombre_consultorio: '' });

    const handleAbrirNuevo = () => {
        if (seccionCatalogo === 'diagnosticos') setFormCIE({ codigo: '', descripcion: '' });
        if (seccionCatalogo === 'especialidades') setFormEspecialidad({ clave: '', nombre: '', division: '' });
        if (seccionCatalogo === 'consultorios') setFormConsultorio({ id: '', nombre_consultorio: '' });
        
        setModoEdicion(false);
        setModalAbierto(true);
    };

    const handleEditar = (item) => {
        if (seccionCatalogo === 'diagnosticos') setFormCIE({ codigo: item.codigo, descripcion: item.descripcion });
        if (seccionCatalogo === 'especialidades') setFormEspecialidad({ clave: item.clave, nombre: item.nombre, division: item.division });
        if (seccionCatalogo === 'consultorios') setFormConsultorio({ id: item.id, nombre_consultorio: item.nombre_consultorio });
        
        setModoEdicion(true);
        setModalAbierto(true);
    };

    const handleBorrar = async (identificador) => {
        if(!window.confirm(`⚠️ ¿Estás totalmente seguro de eliminar este registro?`)) return;

        try {
            let res;
            if (seccionCatalogo === 'diagnosticos') {
                res = await axios.delete(`/api/api_crud_cie.php?codigo=${identificador}`);
            } else if (seccionCatalogo === 'especialidades') {
                res = await axios.delete(`/api/api_crud_especialidades.php?clave=${identificador}`);
            } else if (seccionCatalogo === 'consultorios') {
                res = await axios.delete(`/api/api_consultorios.php?id=${identificador}`);
            }

            if (res.data.success) {
                if (seccionCatalogo === 'diagnosticos') {
                    await localforage.removeItem('cache_cie_vencer');
                    cargarListaDiagnosticos();
                } else if (seccionCatalogo === 'especialidades') {
                    cargarListaEspecialidades();
                } else if (seccionCatalogo === 'consultorios') {
                    cargarListaConsultorios();
                }
            } else { alert("Error: " + res.data.error); }
        } catch (error) { alert("Error de conexión al intentar borrar."); }
    };

    const handleGuardar = async (e) => {
        e.preventDefault();
        setGuardando(true);

        try {
            let res;
            if (seccionCatalogo === 'diagnosticos') {
                res = modoEdicion ? await axios.put('/api/api_crud_cie.php', formCIE) : await axios.post('/api/api_crud_cie.php', formCIE);
            } 
            else if (seccionCatalogo === 'especialidades') {
                res = modoEdicion ? await axios.put('/api/api_crud_especialidades.php', formEspecialidad) : await axios.post('/api/api_crud_especialidades.php', formEspecialidad);
            }
            else if (seccionCatalogo === 'consultorios') {
                if (!formConsultorio.nombre_consultorio) { alert("Llena el nombre."); setGuardando(false); return; }
                res = modoEdicion ? await axios.put('/api/api_consultorios.php', formConsultorio) : await axios.post('/api/api_consultorios.php', formConsultorio);
            }

            if (res.data.success) {
                setModalAbierto(false);
                if (seccionCatalogo === 'diagnosticos') {
                    await localforage.removeItem('cache_cie_vencer');
                    cargarListaDiagnosticos();
                } else if (seccionCatalogo === 'especialidades') {
                    cargarListaEspecialidades();
                } else if (seccionCatalogo === 'consultorios') {
                    cargarListaConsultorios();
                }
            } else { alert("Error al guardar: " + res.data.error); }
        } catch (error) { alert("Error de conexión con el servidor."); } 
        finally { setGuardando(false); }
    };

    const opciones = [
        { id: 'especialidades', nombre: 'Especialidades', icon: <Stethoscope size={20} /> },
        { id: 'medicos', nombre: 'Médicos', icon: <Users size={20} /> },
        { id: 'consultorios', nombre: 'Consultorios', icon: <MapPin size={20} /> },
        { id: 'diagnosticos', nombre: 'CIE-10 (Diagnósticos)', icon: <ClipboardList size={20} /> },
    ];
    const seccionActiva = opciones.find(o => o.id === seccionCatalogo);

        // Abrir el modal para editar
    const handleEditarMedico = (medico) => {
        // Quitamos el "Dr. " temporalmente para que el usuario edite solo el nombre
        const nombreSinPrefijo = medico.nombre.replace('Dr. ', '').replace('Lic. ', '');
        setMedicoSeleccionado({ matricula: medico.matricula, nombre: nombreSinPrefijo });
        setEsEdicion(true);
        setModalMedico(true);
    };

    // Abrir el modal para uno nuevo
    const handleAbrirNuevoMedico = () => {
        setMedicoSeleccionado({ matricula: '', nombre: '' });
        setEsEdicion(false);
        setModalMedico(true);
    };

    // Guardar (Insertar o Actualizar)
    const handleGuardarMedico = async () => {
        if (!medicoSeleccionado.matricula || !medicoSeleccionado.nombre) {
            alert("Por favor llena todos los campos");
            return;
        }

        try {
            if (esEdicion) {
                // Actualizar (PUT)
                await axios.put('/api/api_medicos.php', medicoSeleccionado);
            } else {
                // Crear (POST)
                await axios.post('/api/api_medicos.php', medicoSeleccionado);
            }
            
            setModalMedico(false);
            cargarListaMedicos(); // Refrescar la tabla
        } catch (error) {
            console.error("Error al guardar:", error);
            alert("Hubo un error al procesar la solicitud");
        }
    };

    const handleSubidaMasivaMedicos = async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        if (!window.confirm(`¿Seguro que quieres importar los datos de este archivo?`)) return;

        const formData = new FormData();
        formData.append('archivo_medicos', file);

        setCargando(true);
        try {
            const res = await axios.post('/api/api_medicos.php', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            if (res.data.success) {
                alert("¡Importación exitosa!");
                cargarListaMedicos(); // Refrescamos la tabla
            } else {
                alert("Error: " + res.data.error);
            }
        } catch (error) {
            alert("Error al subir el archivo.");
        } finally {
            setCargando(false);
        }
    };

    return (
        <div className="flex min-h-screen bg-slate-50 font-sans animate-in fade-in duration-500">
            {/* SIDEBAR */}
            <aside className={`${sidebarColapsado ? 'w-20' : 'w-72'} bg-[#ecfdf5] border-r border-emerald-100 transition-all duration-300 flex flex-col shadow-sm relative z-20`}>
                <div className="p-6 flex items-center justify-between h-20">
                    {!sidebarColapsado && <span className="text-emerald-800 font-black uppercase tracking-tighter text-lg whitespace-nowrap overflow-hidden">Catálogos</span>}
                    <button onClick={() => setSidebarColapsado(!sidebarColapsado)} className="p-2 hover:bg-emerald-100 rounded-lg text-emerald-600 transition-colors mx-auto">
                        {sidebarColapsado ? <Menu size={20} /> : <ChevronLeft size={20} />}
                    </button>
                </div>
                <nav className="flex-1 px-3 py-4 space-y-2 overflow-y-auto custom-scrollbar">
                    {opciones.map((opc) => (
                        <button key={opc.id} onClick={() => setSeccionCatalogo(opc.id)} className={`w-full flex items-center p-3 rounded-xl transition-all duration-200 ${seccionCatalogo === opc.id ? 'bg-emerald-500 text-white shadow-md shadow-emerald-200' : 'text-emerald-700 hover:bg-emerald-100 hover:text-emerald-900'}`}>
                            <span className={`${sidebarColapsado ? 'mx-auto' : 'mr-4'}`}>{opc.icon}</span>
                            {!sidebarColapsado && <span className="font-bold">{opc.nombre}</span>}
                        </button>
                    ))}
                </nav>
                <div className="p-4 border-t border-emerald-100 bg-white/50">
                    <button onClick={() => setVistaActiva('menu')} className="w-full flex items-center p-3 text-emerald-700 hover:bg-red-50 hover:text-red-600 rounded-xl transition-all font-bold group">
                        <span className={`${sidebarColapsado ? 'mx-auto' : 'mr-4'} transition-transform group-hover:-translate-x-1`}>←</span>
                        {!sidebarColapsado && <span>Menú Principal</span>}
                    </button>
                </div>
            </aside>

            <main className="flex-1 p-8 overflow-y-auto h-screen relative">
                <header className="mb-8">
                    <h2 className="text-3xl font-black text-slate-800 flex items-center gap-3">
                        <span className="text-emerald-600 bg-emerald-100 p-2 rounded-xl">{seccionActiva?.icon}</span>
                        Catálogo de {seccionActiva?.nombre}
                    </h2>
                    <p className="text-slate-500 font-medium mt-2">Gestiona la base de datos de {seccionCatalogo} de la unidad médica.</p>
                </header>

                <div className="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 min-h-[600px] flex flex-col">
                    
                {/* MÓDULO DE MÉDICOS (ACTUALIZADO CON CARGA MASIVA) */}
                {seccionCatalogo === 'medicos' && (
                    <div className="animate-in fade-in duration-300 flex-1 flex flex-col h-full">
                        <div className="flex justify-between items-center mb-6 pb-4 border-b">
                            <div>
                                <h3 className="text-xl font-bold text-slate-700">Listado de Médicos</h3>
                                <p className="text-xs text-slate-400 font-bold uppercase tracking-tighter">Gestiona el personal o importa desde CSV</p>
                            </div>
                            <div className="flex gap-2">
                                {/* Botón de Carga Masiva */}
                                <label className="cursor-pointer bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 transition-all">
                                    <UploadCloud size={16} />
                                    Subir Excel (CSV)
                                    <input type="file" accept=".csv" className="hidden" onChange={(e) => handleSubidaMasivaMedicos(e)} />
                                </label>
                                <span className="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-sm font-bold flex items-center">
                                    Total: {listaMedicos.length}
                                </span>
                            </div>
                        </div>

                        <div className="flex-1 overflow-auto border border-slate-200 rounded-2xl shadow-sm mb-6 max-h-[500px]">
                            {cargando ? (
                                <div className="h-full flex items-center justify-center text-slate-400"><Loader2 className="animate-spin mr-2" /> Cargando plantilla...</div>
                            ) : (
                                <table className="w-full text-left">
                                    <thead className="bg-slate-50 sticky top-0 z-10">
                                        <tr>
                                            <th className="p-4 text-slate-500 font-bold border-b w-32">Matrícula</th>
                                            <th className="p-4 text-slate-500 font-bold border-b">Nombre del Médico</th>
                                            <th className="p-4 text-center w-32 border-b">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {listaMedicos.map((med) => (
                                            <tr key={med.matricula} className="hover:bg-slate-50 border-b group">
                                                <td className="p-4 font-black text-slate-400">{med.matricula}</td>
                                                <td className="p-4 text-slate-700 font-black uppercase">{med.nombre}</td>
                                                <td className="p-4">
                                                    <div className="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <button onClick={() => handleEditarMedico(med)} className="p-2 text-blue-600 hover:bg-blue-100 rounded-lg"><Pencil size={18} /></button>
                                                        <button onClick={() => handleBorrar(med.matricula)} className="p-2 text-red-600 hover:bg-red-100 rounded-lg"><Trash2 size={18} /></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            )}
                        </div>
                        <button onClick={handleAbrirNuevo} className="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold flex items-center gap-2 w-fit">
                            <Plus size={20} /> Agregar Médico Manual
                        </button>
                    </div>
                )}

                    {/* 2. MÓDULO DE ESPECIALIDADES */}
                    {seccionCatalogo === 'especialidades' && (
                        <div className="animate-in fade-in duration-300 flex-1 flex flex-col h-full">
                            <div className="flex justify-between items-center mb-6 pb-4 border-b border-slate-100">
                                <h3 className="text-xl font-bold text-slate-700">Listado de Especialidades</h3>
                                <span className="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-sm font-bold">Total: {listaEspecialidades.length}</span>
                            </div>
                            <div className="flex-1 overflow-auto border border-slate-200 rounded-2xl shadow-sm mb-6 max-h-[500px]">
                                {cargando ? (
                                    <div className="h-full flex items-center justify-center text-slate-400"><Loader2 className="animate-spin mr-2" /> Cargando...</div>
                                ) : (
                                    <table className="w-full text-left border-collapse">
                                        <thead className="bg-slate-50 sticky top-0 z-10 shadow-sm">
                                            <tr>
                                                <th className="p-4 text-slate-500 font-bold border-b w-32">Clave</th>
                                                <th className="p-4 text-slate-500 font-bold border-b">Especialidad</th>
                                                <th className="p-4 text-slate-500 font-bold border-b">División</th>
                                                <th className="p-4 text-center w-32 border-b">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {listaEspecialidades.map((esp) => (
                                                <tr key={esp.clave} className="hover:bg-slate-50 border-b group transition-colors">
                                                    <td className="p-4 font-black text-slate-400">{esp.clave}</td>
                                                    <td className="p-4 text-slate-700 font-black">{esp.nombre}</td>
                                                    <td className="p-4 text-slate-500 font-medium">{esp.division}</td>
                                                    <td className="p-4 text-center">
                                                        <div className="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                            <button onClick={() => handleEditar(esp)} className="p-2 text-blue-600 hover:bg-blue-100 rounded-lg"><Pencil size={18} /></button>
                                                            <button onClick={() => handleBorrar(esp.clave)} className="p-2 text-red-600 hover:bg-red-100 rounded-lg"><Trash2 size={18} /></button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                )}
                            </div>
                            <div className="flex justify-start">
                                <button onClick={handleAbrirNuevo} className="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg flex items-center gap-2 transition-all active:scale-95"><Plus size={20} /> Agregar Especialidad</button>
                            </div>
                        </div>
                    )}

                    {/* 3. MÓDULO DE CONSULTORIOS */}
                    {seccionCatalogo === 'consultorios' && (
                        <div className="animate-in fade-in duration-300 flex-1 flex flex-col h-full">
                            <div className="flex justify-between items-center mb-6 pb-4 border-b border-slate-100">
                                <h3 className="text-xl font-bold text-slate-700">Listado de Consultorios</h3>
                                <span className="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-sm font-bold">Total: {listaConsultorios.length}</span>
                            </div>
                            <div className="flex-1 overflow-auto border border-slate-200 rounded-2xl shadow-sm mb-6 max-h-[500px]">
                                {cargando ? (
                                    <div className="h-full flex items-center justify-center text-slate-400"><Loader2 className="animate-spin mr-2" /> Cargando...</div>
                                ) : (
                                    <table className="w-full text-left border-collapse">
                                        <thead className="bg-slate-50 sticky top-0 z-10 shadow-sm">
                                            <tr>
                                                <th className="p-4 text-slate-500 font-bold border-b w-24">ID</th>
                                                <th className="p-4 text-slate-500 font-bold border-b">Nombre del Consultorio</th>
                                                <th className="p-4 text-center w-32 border-b">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {listaConsultorios.map((con) => (
                                                <tr key={con.id} className="hover:bg-slate-50 border-b group transition-colors">
                                                    <td className="p-4 font-black text-slate-400">{con.id}</td>
                                                    <td className="p-4 text-slate-700 font-black">{con.nombre_consultorio}</td>
                                                    <td className="p-4 text-center">
                                                        <div className="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                            <button onClick={() => handleEditar(con)} className="p-2 text-blue-600 hover:bg-blue-100 rounded-lg"><Pencil size={18} /></button>
                                                            <button onClick={() => handleBorrar(con.id)} className="p-2 text-red-600 hover:bg-red-100 rounded-lg"><Trash2 size={18} /></button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                )}
                            </div>
                            <div className="flex justify-start">
                                <button onClick={handleAbrirNuevo} className="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg flex items-center gap-2 transition-all active:scale-95"><Plus size={20} /> Agregar Consultorio</button>
                            </div>
                        </div>
                    )}

                    {/* 4. MÓDULO DE DIAGNÓSTICOS */}
                    {seccionCatalogo === 'diagnosticos' && (
                        <div className="animate-in fade-in duration-300 flex-1 flex flex-col h-full">
                            <div className="flex justify-between items-center mb-6 pb-4 border-b border-slate-100">
                                <h3 className="text-xl font-bold text-slate-700">Listado de Códigos CIE-10</h3>
                                <span className="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-sm font-bold">Total: {listaDiagnosticos.length}</span>
                            </div>
                            <div className="flex-1 overflow-auto border border-slate-200 rounded-2xl shadow-sm mb-6 max-h-[500px]">
                                {cargando ? (
                                    <div className="h-full flex items-center justify-center text-slate-400"><Loader2 className="animate-spin mr-2" /> Cargando...</div>
                                ) : (
                                    <table className="w-full text-left border-collapse">
                                        <thead className="bg-slate-50 sticky top-0 z-10 shadow-sm">
                                            <tr>
                                                <th className="p-4 text-slate-500 font-bold border-b w-32">Código</th>
                                                <th className="p-4 text-slate-500 font-bold border-b">Descripción</th>
                                                <th className="p-4 text-center w-32 border-b">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {listaDiagnosticos.map((diag) => (
                                                <tr key={diag.codigo} className="hover:bg-slate-50 border-b group transition-colors">
                                                    <td className="p-4 font-black text-slate-700">{diag.codigo}</td>
                                                    <td className="p-4 text-slate-600 font-medium">{diag.descripcion}</td>
                                                    <td className="p-4 text-center">
                                                        <div className="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                            <button onClick={() => handleEditar(diag)} className="p-2 text-blue-600 hover:bg-blue-100 rounded-lg"><Pencil size={18} /></button>
                                                            <button onClick={() => handleBorrar(diag.codigo)} className="p-2 text-red-600 hover:bg-red-100 rounded-lg"><Trash2 size={18} /></button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                )}
                            </div>
                            <div className="flex justify-start">
                                <button onClick={handleAbrirNuevo} className="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg flex items-center gap-2 transition-all active:scale-95"><Plus size={20} /> Agregar Diagnóstico</button>
                            </div>
                        </div>
                    )}
                </div>
            </main>

            {/* MODAL DINÁMICO */}
            {modalAbierto && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 animate-in zoom-in-95 duration-300">
                        <div className="flex justify-between items-center mb-6">
                            <h3 className="text-2xl font-black text-slate-800">{modoEdicion ? 'Editar Registro' : 'Nuevo Registro'}</h3>
                            <button onClick={() => setModalAbierto(false)} className="text-slate-400 hover:bg-slate-100 p-2 rounded-xl"><X size={24} /></button>
                        </div>

                        <form onSubmit={handleGuardar} className="space-y-5">
                            {seccionCatalogo === 'diagnosticos' && (
                                <>
                                    <div>
                                        <label className="block text-sm font-bold text-slate-600 mb-2">Código CIE-10</label>
                                        <input type="text" value={formCIE.codigo} onChange={(e) => setFormCIE({...formCIE, codigo: e.target.value})} disabled={modoEdicion} placeholder="Ej. J00X" className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-bold text-slate-700 uppercase focus:ring-2 focus:ring-emerald-500 outline-none disabled:opacity-50" />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-bold text-slate-600 mb-2">Descripción Oficial</label>
                                        <textarea value={formCIE.descripcion} onChange={(e) => setFormCIE({...formCIE, descripcion: e.target.value})} placeholder="Descripción..." rows="3" className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none resize-none" />
                                    </div>
                                </>
                            )}

                            {seccionCatalogo === 'especialidades' && (
                                <>
                                    <div>
                                        <label className="block text-sm font-bold text-slate-600 mb-2">Clave</label>
                                        <input type="text" value={formEspecialidad.clave} onChange={(e) => setFormEspecialidad({...formEspecialidad, clave: e.target.value})} disabled={modoEdicion} placeholder="Clave..." className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-black text-slate-700 uppercase focus:ring-2 focus:ring-emerald-500 outline-none disabled:opacity-50" />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-bold text-slate-600 mb-2">Nombre</label>
                                        <input type="text" value={formEspecialidad.nombre} onChange={(e) => setFormEspecialidad({...formEspecialidad, nombre: e.target.value})} placeholder="Nombre..." className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-black text-slate-700 uppercase focus:ring-2 focus:ring-emerald-500 outline-none" />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-bold text-slate-600 mb-2">División</label>
                                        <input type="text" value={formEspecialidad.division} onChange={(e) => setFormEspecialidad({...formEspecialidad, division: e.target.value})} placeholder="División..." className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-black text-slate-700 uppercase focus:ring-2 focus:ring-emerald-500 outline-none" />
                                    </div>
                                </>
                            )}

                            {seccionCatalogo === 'consultorios' && (
                                <div>
                                    <label className="block text-sm font-bold text-slate-600 mb-2">Nombre del Consultorio</label>
                                    <input type="text" required value={formConsultorio.nombre_consultorio} onChange={(e) => setFormConsultorio({...formConsultorio, nombre_consultorio: e.target.value})} placeholder="Ej. Consultorio 05" className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-bold text-slate-700 uppercase focus:ring-2 focus:ring-emerald-500 outline-none" />
                                </div>
                            )}

                            <div className="flex gap-3 pt-4">
                                <button type="button" onClick={() => setModalAbierto(false)} className="flex-1 px-4 py-3 text-slate-500 font-bold hover:bg-slate-100 rounded-xl transition-colors">Cancelar</button>
                                <button type="submit" disabled={guardando} className="flex-1 bg-emerald-500 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-emerald-200 flex justify-center items-center gap-2 transition-all active:scale-95 disabled:opacity-70">
                                    {guardando ? <Loader2 size={20} className="animate-spin" /> : (modoEdicion ? 'Actualizar' : 'Guardar')}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
            {/* MODAL DE MÉDICOS */}
            {modalMedico && (
                <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
                    <div className="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden animate-in zoom-in duration-200">
                        <div className="bg-slate-800 p-6 text-white flex justify-between items-center">
                            <h3 className="text-xl font-black">{esEdicion ? 'Editar Médico' : 'Nuevo Médico'}</h3>
                            <button onClick={() => setModalMedico(false)} className="hover:bg-white/10 p-1 rounded-full"><X size={24} /></button>
                        </div>
                        
                        <div className="p-8 space-y-6">
                            <div>
                                <label className="block text-xs font-bold text-slate-400 uppercase mb-2">Matrícula (No editable)</label>
                                <input 
                                    type="text" 
                                    disabled={esEdicion}
                                    className="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl font-black text-slate-700 outline-none focus:border-emerald-500 transition-all disabled:opacity-50"
                                    value={medicoSeleccionado.matricula}
                                    onChange={(e) => setMedicoSeleccionado({...medicoSeleccionado, matricula: e.target.value})}
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-slate-400 uppercase mb-2">Nombre Completo</label>
                                <input 
                                    type="text" 
                                    placeholder="Apellido Paterno/Materno/Nombre"
                                    className="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl font-bold text-slate-700 outline-none focus:border-emerald-500 transition-all"
                                    value={medicoSeleccionado.nombre}
                                    onChange={(e) => setMedicoSeleccionado({...medicoSeleccionado, nombre: e.target.value})}
                                />
                                <p className="text-[10px] text-slate-400 mt-2">Usa barras (/) para separar apellidos si quieres que el sistema los limpie automáticamente.</p>
                            </div>

                            <div className="flex gap-3 pt-4">
                                <button 
                                    onClick={() => setModalMedico(false)}
                                    className="flex-1 py-3 border border-slate-200 text-slate-500 font-bold rounded-xl hover:bg-slate-50 transition-all"
                                >
                                    Cancelar
                                </button>
                                <button 
                                    onClick={handleGuardarMedico}
                                    className="flex-1 py-3 bg-emerald-500 text-white font-bold rounded-xl hover:bg-emerald-600 shadow-lg shadow-emerald-200 transition-all"
                                >
                                    {esEdicion ? 'Guardar Cambios' : 'Crear Registro'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}