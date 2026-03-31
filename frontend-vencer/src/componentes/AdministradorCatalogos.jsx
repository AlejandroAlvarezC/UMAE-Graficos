import React, { useState, useEffect } from 'react';
import axios from 'axios';
import localforage from 'localforage';
import { 
    Users, Stethoscope, MapPin, ClipboardList, 
    ChevronLeft, Menu, Pencil, Trash2, Plus, Loader2, X
} from 'lucide-react';

export default function AdministradorCatalogos({ setVistaActiva }) {
    const [seccionCatalogo, setSeccionCatalogo] = useState('medicos');
    const [sidebarColapsado, setSidebarColapsado] = useState(false);

    // ==========================================
    // ESTADOS DE LOS CATÁLOGOS
    // ==========================================
    const [listaDiagnosticos, setListaDiagnosticos] = useState([]);
    const [listaEspecialidades, setListaEspecialidades] = useState([]);
    const [cargando, setCargando] = useState(false);

    useEffect(() => {
        if (seccionCatalogo === 'diagnosticos') cargarListaDiagnosticos();
        if (seccionCatalogo === 'especialidades') cargarListaEspecialidades();
    }, [seccionCatalogo]);

    const cargarListaDiagnosticos = async () => {
        setCargando(true);
        try {
            const urlFiel = `/api/api_cie.php?t=${new Date().getTime()}`;
            const res = await axios.get(urlFiel);
            if (Array.isArray(res.data)) setListaDiagnosticos(res.data);
        } catch (error) {
            console.error("Error CIE-10:", error);
        } finally {
            setCargando(false);
        }
    };

    const cargarListaEspecialidades = async () => {
        setCargando(true);
        try {
            const res = await axios.get(`/api/api_crud_especialidades.php?t=${new Date().getTime()}`);
            if (Array.isArray(res.data)) setListaEspecialidades(res.data);
        } catch (error) {
            console.error("Error Especialidades:", error);
        } finally {
            setCargando(false);
        }
    };

    // ==========================================
    // LÓGICA DE CRUD GLOBAL (VENTANA MODAL)
    // ==========================================
    const [modalAbierto, setModalAbierto] = useState(false);
    const [modoEdicion, setModoEdicion] = useState(false);
    const [guardando, setGuardando] = useState(false);
    
    // Formularios independientes
    const [formCIE, setFormCIE] = useState({ codigo: '', descripcion: '' });
    const [formEspecialidad, setFormEspecialidad] = useState({ clave: '', nombre: '', division: '' });

    const handleAbrirNuevo = () => {
        if (seccionCatalogo === 'diagnosticos') setFormCIE({ codigo: '', descripcion: '' });
        if (seccionCatalogo === 'especialidades') setFormEspecialidad({ clave: '', nombre: '', division: '' });
        
        setModoEdicion(false);
        setModalAbierto(true);
    };

    const handleEditar = (item) => {
        if (seccionCatalogo === 'diagnosticos') setFormCIE({ codigo: item.codigo, descripcion: item.descripcion });
        if (seccionCatalogo === 'especialidades') setFormEspecialidad({ clave: item.clave, nombre: item.nombre, division: item.division });
        
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
            }

            if (res.data.success) {
                if (seccionCatalogo === 'diagnosticos') {
                    await localforage.removeItem('cache_cie_vencer');
                    cargarListaDiagnosticos();
                } else if (seccionCatalogo === 'especialidades') {
                    cargarListaEspecialidades();
                }
            } else {
                alert("Error: " + res.data.error);
            }
        } catch (error) {
            console.error("Error al borrar:", error);
            alert("Error de conexión al intentar borrar.");
        }
    };

    const handleGuardar = async (e) => {
        e.preventDefault();
        setGuardando(true);

        try {
            let res;
            if (seccionCatalogo === 'diagnosticos') {
                if (!formCIE.codigo || !formCIE.descripcion) { alert("Llena todos los campos."); setGuardando(false); return; }
                res = modoEdicion ? await axios.put('/api/api_crud_cie.php', formCIE) : await axios.post('/api/api_crud_cie.php', formCIE);
            } 
            else if (seccionCatalogo === 'especialidades') {
                if (!formEspecialidad.clave || !formEspecialidad.nombre || !formEspecialidad.division) { alert("Llena todos los campos."); setGuardando(false); return; }
                res = modoEdicion ? await axios.put('/api/api_crud_especialidades.php', formEspecialidad) : await axios.post('/api/api_crud_especialidades.php', formEspecialidad);
            }

            if (res.data.success) {
                setModalAbierto(false);
                if (seccionCatalogo === 'diagnosticos') {
                    await localforage.removeItem('cache_cie_vencer');
                    cargarListaDiagnosticos();
                } else if (seccionCatalogo === 'especialidades') {
                    cargarListaEspecialidades();
                }
            } else {
                alert("Error al guardar: " + res.data.error);
            }
        } catch (error) {
            console.error("Error al guardar:", error);
            alert("Error de conexión con el servidor.");
        } finally {
            setGuardando(false);
        }
    };

    const opciones = [
        { id: 'especialidades', nombre: 'Especialidades', icon: <Stethoscope size={20} /> },
        { id: 'medicos', nombre: 'Médicos', icon: <Users size={20} /> },
        { id: 'consultorios', nombre: 'Consultorios', icon: <MapPin size={20} /> },
        { id: 'diagnosticos', nombre: 'CIE-10 (Diagnósticos)', icon: <ClipboardList size={20} /> },
    ];
    const seccionActiva = opciones.find(o => o.id === seccionCatalogo);

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
                        <button key={opc.id} onClick={() => setSeccionCatalogo(opc.id)} title={sidebarColapsado ? opc.nombre : ""} className={`w-full flex items-center p-3 rounded-xl transition-all duration-200 ${seccionCatalogo === opc.id ? 'bg-emerald-500 text-white shadow-md shadow-emerald-200' : 'text-emerald-700 hover:bg-emerald-100 hover:text-emerald-900'}`}>
                            <span className={`${sidebarColapsado ? 'mx-auto' : 'mr-4'} flex-shrink-0`}>{opc.icon}</span>
                            {!sidebarColapsado && <span className="font-bold whitespace-nowrap overflow-hidden text-ellipsis">{opc.nombre}</span>}
                        </button>
                    ))}
                </nav>
                <div className="p-4 border-t border-emerald-100 bg-white/50">
                    <button onClick={() => setVistaActiva('menu')} title={sidebarColapsado ? "Volver al Menú" : ""} className="w-full flex items-center p-3 text-emerald-700 hover:bg-red-50 hover:text-red-600 rounded-xl transition-all font-bold group">
                        <span className={`${sidebarColapsado ? 'mx-auto' : 'mr-4'} transition-transform group-hover:-translate-x-1`}>←</span>
                        {!sidebarColapsado && <span className="whitespace-nowrap">Menú Principal</span>}
                    </button>
                </div>
            </aside>

            {/* CONTENIDO PRINCIPAL */}
            <main className="flex-1 p-8 overflow-y-auto h-screen relative">
                <header className="mb-8 animate-in slide-in-from-top-4 duration-500">
                    <h2 className="text-3xl font-black text-slate-800 flex items-center gap-3">
                        <span className="text-emerald-600 bg-emerald-100 p-2 rounded-xl">{seccionActiva?.icon}</span>
                        Catálogo de {seccionActiva?.nombre}
                    </h2>
                    <p className="text-slate-500 font-medium mt-2">Gestiona la base de datos de {seccionCatalogo} de la unidad médica.</p>
                </header>

                <div className="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 p-8 min-h-[600px] flex flex-col">
                    
                    {/* 1. MÓDULO DE MÉDICOS (En pausa) */}
                    {seccionCatalogo === 'medicos' && (
                        <div className="flex-1 border-2 border-dashed border-slate-200 rounded-2xl flex items-center justify-center bg-slate-50/50">
                            <div className="text-center"><Users size={48} className="mx-auto text-slate-300 mb-4" /><p className="text-slate-500 font-medium text-lg">Próximo CRUD a desarrollar.</p></div>
                        </div>
                    )}

                    {/* 2. MÓDULO DE ESPECIALIDADES (ACTUALIZADO) */}
                    {seccionCatalogo === 'especialidades' && (
                        <div className="animate-in fade-in duration-300 flex-1 flex flex-col h-full">
                            <div className="flex justify-between items-center mb-6 pb-4 border-b border-slate-100">
                                <h3 className="text-xl font-bold text-slate-700">Listado de Especialidades</h3>
                                <span className="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-sm font-bold">Total: {listaEspecialidades.length}</span>
                            </div>

                            <div className="flex-1 overflow-auto border border-slate-200 rounded-2xl shadow-sm mb-6 max-h-[500px] custom-scrollbar">
                                {cargando ? (
                                    <div className="h-full min-h-[200px] flex flex-col items-center justify-center text-slate-400"><Loader2 className="animate-spin mb-2" size={32} /><p>Cargando catálogo...</p></div>
                                ) : (
                                    <table className="w-full text-left border-collapse">
                                        <thead className="bg-slate-50 sticky top-0 z-10 shadow-sm">
                                            <tr>
                                                <th className="p-4 text-slate-500 font-bold border-b border-slate-200 w-32">Clave</th>
                                                <th className="p-4 text-slate-500 font-bold border-b border-slate-200">Especialidad</th>
                                                <th className="p-4 text-slate-500 font-bold border-b border-slate-200">División</th>
                                                <th className="p-4 text-slate-500 font-bold border-b border-slate-200 text-center w-32">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {listaEspecialidades.length === 0 ? (
                                                <tr><td colSpan="4" className="text-center p-8 text-slate-400 italic">No hay registros.</td></tr>
                                            ) : (
                                                listaEspecialidades.map((esp) => (
                                                    <tr key={esp.clave} className="hover:bg-slate-50 border-b border-slate-100 transition-colors group">
                                                        <td className="p-4 font-black text-slate-400">{esp.clave}</td>
                                                        <td className="p-4 text-slate-700 font-black">{esp.nombre}</td>
                                                        <td className="p-4 text-slate-500 font-medium">{esp.division}</td>
                                                        <td className="p-4">
                                                            <div className="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                <button onClick={() => handleEditar(esp)} className="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors"><Pencil size={18} /></button>
                                                                <button onClick={() => handleBorrar(esp.clave)} className="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"><Trash2 size={18} /></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                ))
                                            )}
                                        </tbody>
                                    </table>
                                )}
                            </div>
                            <div className="flex justify-start">
                                <button onClick={handleAbrirNuevo} className="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-emerald-200 transition-all active:scale-95 flex items-center gap-2"><Plus size={20} /> Agregar Especialidad</button>
                            </div>
                        </div>
                    )}

                    {/* 3. MÓDULO DE DIAGNÓSTICOS */}
                    {seccionCatalogo === 'diagnosticos' && (
                        <div className="animate-in fade-in duration-300 flex-1 flex flex-col h-full">
                            <div className="flex justify-between items-center mb-6 pb-4 border-b border-slate-100">
                                <h3 className="text-xl font-bold text-slate-700">Listado de Códigos CIE-10</h3>
                                <span className="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-sm font-bold">Total: {listaDiagnosticos.length}</span>
                            </div>

                            <div className="flex-1 overflow-auto border border-slate-200 rounded-2xl shadow-sm mb-6 max-h-[500px] custom-scrollbar">
                                {cargando ? (
                                    <div className="h-full min-h-[200px] flex flex-col items-center justify-center text-slate-400"><Loader2 className="animate-spin mb-2" size={32} /><p>Cargando catálogo...</p></div>
                                ) : (
                                    <table className="w-full text-left border-collapse">
                                        <thead className="bg-slate-50 sticky top-0 z-10 shadow-sm">
                                            <tr>
                                                <th className="p-4 text-slate-500 font-bold border-b border-slate-200 w-32">Código</th>
                                                <th className="p-4 text-slate-500 font-bold border-b border-slate-200">Descripción de la Enfermedad</th>
                                                <th className="p-4 text-slate-500 font-bold border-b border-slate-200 text-center w-32">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {listaDiagnosticos.length === 0 ? (
                                                <tr><td colSpan="3" className="text-center p-8 text-slate-400 italic">No hay registros.</td></tr>
                                            ) : (
                                                listaDiagnosticos.map((diag) => (
                                                    <tr key={diag.codigo} className="hover:bg-slate-50 border-b border-slate-100 transition-colors group">
                                                        <td className="p-4 font-black text-slate-700">{diag.codigo}</td>
                                                        <td className="p-4 text-slate-600 font-medium">{diag.descripcion}</td>
                                                        <td className="p-4">
                                                            <div className="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                <button onClick={() => handleEditar(diag)} className="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors"><Pencil size={18} /></button>
                                                                <button onClick={() => handleBorrar(diag.codigo)} className="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"><Trash2 size={18} /></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                ))
                                            )}
                                        </tbody>
                                    </table>
                                )}
                            </div>
                            <div className="flex justify-start">
                                <button onClick={handleAbrirNuevo} className="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-emerald-200 transition-all active:scale-95 flex items-center gap-2"><Plus size={20} /> Agregar Diagnóstico</button>
                            </div>
                        </div>
                    )}

                    {/* 4. PLACEHOLDER (Para Consultorios) */}
                    {seccionCatalogo === 'consultorios' && (
                        <div className="flex-1 flex items-center justify-center animate-in fade-in duration-300">
                            <div className="text-center text-slate-400">
                                <MapPin size={64} className="mx-auto mb-4 opacity-50" />
                                <p className="text-xl font-medium">Módulo de Consultorios en desarrollo</p>
                            </div>
                        </div>
                    )}
                </div>
            </main>

            {/* ========================================== */}
            {/* VENTANA MODAL DINÁMICA                     */}
            {/* ========================================== */}
            {modalAbierto && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 animate-in zoom-in-95 duration-300">
                        
                        <div className="flex justify-between items-center mb-6">
                            <h3 className="text-2xl font-black text-slate-800">
                                {modoEdicion ? 'Editar Registro' : 'Nuevo Registro'}
                            </h3>
                            <button onClick={() => setModalAbierto(false)} className="text-slate-400 hover:bg-slate-100 hover:text-slate-600 p-2 rounded-xl transition-colors"><X size={24} /></button>
                        </div>

                        <form onSubmit={handleGuardar} className="space-y-5">
                            
                            {/* FORMULARIO PARA CIE-10 */}
                            {seccionCatalogo === 'diagnosticos' && (
                                <>
                                    <div>
                                        <label className="block text-sm font-bold text-slate-600 mb-2">Código CIE-10</label>
                                        <input type="text" value={formCIE.codigo} onChange={(e) => setFormCIE({...formCIE, codigo: e.target.value})} disabled={modoEdicion} placeholder="Ej. J00X" className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-bold text-slate-700 uppercase focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:opacity-50" />
                                        {modoEdicion && <p className="text-xs text-slate-400 mt-1">El código no se puede modificar una vez creado.</p>}
                                    </div>
                                    <div>
                                        <label className="block text-sm font-bold text-slate-600 mb-2">Descripción Oficial</label>
                                        <textarea value={formCIE.descripcion} onChange={(e) => setFormCIE({...formCIE, descripcion: e.target.value})} placeholder="Descripción..." rows="3" className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none" />
                                    </div>
                                </>
                            )}

                            {/* FORMULARIO PARA ESPECIALIDADES (ACTUALIZADO CON 3 CAMPOS) */}
                            {seccionCatalogo === 'especialidades' && (
                                <>
                                    <div>
                                        <label className="block text-sm font-bold text-slate-600 mb-2">Clave de Especialidad</label>
                                        <input type="text" value={formEspecialidad.clave} onChange={(e) => setFormEspecialidad({...formEspecialidad, clave: e.target.value})} disabled={modoEdicion} placeholder="Ej. CAR-01" className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-black text-slate-700 uppercase focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:opacity-50" />
                                        {modoEdicion && <p className="text-xs text-slate-400 mt-1">La clave no se puede modificar una vez creada.</p>}
                                    </div>
                                    <div>
                                        <label className="block text-sm font-bold text-slate-600 mb-2">Nombre de la Especialidad</label>
                                        <input type="text" value={formEspecialidad.nombre} onChange={(e) => setFormEspecialidad({...formEspecialidad, nombre: e.target.value})} placeholder="Ej. CARDIOLOGÍA" className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-black text-slate-700 uppercase focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-bold text-slate-600 mb-2">División</label>
                                        <input type="text" value={formEspecialidad.division} onChange={(e) => setFormEspecialidad({...formEspecialidad, division: e.target.value})} placeholder="Ej. MEDICINA INTERNA" className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-black text-slate-700 uppercase focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                                    </div>
                                </>
                            )}

                            <div className="flex gap-3 pt-4">
                                <button type="button" onClick={() => setModalAbierto(false)} className="flex-1 px-4 py-3 text-slate-500 font-bold hover:bg-slate-100 rounded-xl transition-colors">Cancelar</button>
                                <button type="submit" disabled={guardando} className="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-emerald-200 transition-all flex justify-center items-center gap-2 active:scale-95 disabled:opacity-70">
                                    {guardando ? <Loader2 size={20} className="animate-spin" /> : (modoEdicion ? 'Actualizar' : 'Guardar')}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}