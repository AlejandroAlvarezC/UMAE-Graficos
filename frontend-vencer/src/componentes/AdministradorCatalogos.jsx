import React, { useState, useEffect } from 'react';
import axios from 'axios';
import localforage from 'localforage';
import { 
    Users, Stethoscope, MapPin, ClipboardList, 
    ChevronLeft, Menu, Pencil, Trash2, Plus, Loader2, X
} from 'lucide-react';

export default function AdministradorCatalogos({ setVistaActiva }) {
    // Estados locales exclusivos de este módulo
    const [seccionCatalogo, setSeccionCatalogo] = useState('medicos');
    const [sidebarColapsado, setSidebarColapsado] = useState(false);

    // ==========================================
    // ESTADOS DEL MÓDULO DE DIAGNÓSTICOS
    // ==========================================
    const [listaDiagnosticos, setListaDiagnosticos] = useState([]);
    const [cargando, setCargando] = useState(false);

    // Efecto para descargar datos cuando el usuario entra a la pestaña de diagnósticos
    useEffect(() => {
        if (seccionCatalogo === 'diagnosticos') {
            cargarListaDiagnosticos();
        }
    }, [seccionCatalogo]);

    const cargarListaDiagnosticos = async () => {
        setCargando(true);
        try {
            const res = await axios.get('/api/api_cie.php');
            if (Array.isArray(res.data)) {
                setListaDiagnosticos(res.data);
            }
        } catch (error) {
            console.error("Error al cargar CIE-10:", error);
        } finally {
            setCargando(false);
        }
    };

    // ==========================================
    // LÓGICA DE CRUD (VENTANA MODAL Y API)
    // ==========================================
    const [modalAbierto, setModalAbierto] = useState(false);
    const [modoEdicion, setModoEdicion] = useState(false);
    const [formCIE, setFormCIE] = useState({ codigo: '', descripcion: '' });
    const [guardando, setGuardando] = useState(false);

    const handleAbrirNuevo = () => {
        setFormCIE({ codigo: '', descripcion: '' });
        setModoEdicion(false);
        setModalAbierto(true);
    };

    const handleEditar = (diag) => {
        setFormCIE({ codigo: diag.codigo, descripcion: diag.descripcion });
        setModoEdicion(true);
        setModalAbierto(true);
    };

    const handleBorrar = async (codigo) => {
        if(window.confirm(`⚠️ ¿Estás totalmente seguro de eliminar el código ${codigo}? Esta acción no se puede deshacer.`)) {
            try {
                // Le pasamos el código por la URL para el DELETE
                const res = await axios.delete(`/api/api_crud_cie.php?codigo=${codigo}`);
                if (res.data.success) {
                    cargarListaDiagnosticos(); // Recargamos la tabla automáticamente

                    await localforage.removeItem('cache_cie_vencer');
                    window.location.reload();
                } else {
                    alert("Error: " + res.data.error);
                }
            } catch (error) {
                console.error("Error al borrar:", error);
                alert("Hubo un error de conexión al intentar borrar.");
            }
        }
    };

    const handleGuardar = async (e) => {
        e.preventDefault();
        if (!formCIE.codigo || !formCIE.descripcion) {
            alert("Por favor llena ambos campos."); return;
        }

        setGuardando(true);
        try {
            let res;
            if (modoEdicion) {
                res = await axios.put('/api/api_crud_cie.php', formCIE);
            } else {
                res = await axios.post('/api/api_crud_cie.php', formCIE);
            }
            
            if (res.data.success) {

                await localforage.removeItem('cache_cie_vencer');

                setModalAbierto(false); // Cerramos la ventana

                cargarListaDiagnosticos(); // Actualizamos la tabla

            } else {
                alert("Error al guardar: " + res.data.error);
            }
        } catch (error) {
            console.error("Error al guardar:", error);
            alert("Hubo un error de conexión con el servidor.");
        } finally {
            setGuardando(false);
        }
    };

    // Configuración del menú lateral
    const opciones = [
        { id: 'especialidades', nombre: 'Especialidades', icon: <Stethoscope size={20} /> },
        { id: 'medicos', nombre: 'Médicos', icon: <Users size={20} /> },
        { id: 'consultorios', nombre: 'Consultorios', icon: <MapPin size={20} /> },
        { id: 'diagnosticos', nombre: 'CIE-10 (Diagnósticos)', icon: <ClipboardList size={20} /> },
    ];

    // Obtenemos los datos de la sección activa para el título
    const seccionActiva = opciones.find(o => o.id === seccionCatalogo);

    return (
        <div className="flex min-h-screen bg-slate-50 font-sans animate-in fade-in duration-500">
            
            {/* --- SIDEBAR VERDE PASTEL --- */}
            <aside 
                className={`${sidebarColapsado ? 'w-20' : 'w-72'} bg-[#ecfdf5] border-r border-emerald-100 transition-all duration-300 flex flex-col shadow-sm relative z-20`}
            >
                {/* Encabezado Sidebar */}
                <div className="p-6 flex items-center justify-between h-20">
                    {!sidebarColapsado && (
                        <span className="text-emerald-800 font-black uppercase tracking-tighter text-lg whitespace-nowrap overflow-hidden">
                            Catálogos
                        </span>
                    )}
                    <button 
                        onClick={() => setSidebarColapsado(!sidebarColapsado)}
                        className="p-2 hover:bg-emerald-100 rounded-lg text-emerald-600 transition-colors mx-auto"
                        title={sidebarColapsado ? "Expandir menú" : "Colapsar menú"}
                    >
                        {sidebarColapsado ? <Menu size={20} /> : <ChevronLeft size={20} />}
                    </button>
                </div>

                {/* Opciones del Menú */}
                <nav className="flex-1 px-3 py-4 space-y-2 overflow-y-auto custom-scrollbar">
                    {opciones.map((opc) => (
                        <button
                            key={opc.id}
                            onClick={() => setSeccionCatalogo(opc.id)}
                            title={sidebarColapsado ? opc.nombre : ""}
                            className={`w-full flex items-center p-3 rounded-xl transition-all duration-200 ${
                                seccionCatalogo === opc.id 
                                ? 'bg-emerald-500 text-white shadow-md shadow-emerald-200' 
                                : 'text-emerald-700 hover:bg-emerald-100 hover:text-emerald-900'
                            }`}
                        >
                            <span className={`${sidebarColapsado ? 'mx-auto' : 'mr-4'} flex-shrink-0`}>
                                {opc.icon}
                            </span>
                            {!sidebarColapsado && (
                                <span className="font-bold whitespace-nowrap overflow-hidden text-ellipsis">
                                    {opc.nombre}
                                </span>
                            )}
                        </button>
                    ))}
                </nav>

                {/* Botón Salir al Final */}
                <div className="p-4 border-t border-emerald-100 bg-white/50">
                    <button 
                        onClick={() => setVistaActiva('menu')}
                        title={sidebarColapsado ? "Volver al Menú" : ""}
                        className="w-full flex items-center p-3 text-emerald-700 hover:bg-red-50 hover:text-red-600 rounded-xl transition-all font-bold group"
                    >
                        <span className={`${sidebarColapsado ? 'mx-auto' : 'mr-4'} transition-transform group-hover:-translate-x-1`}>
                            ←
                        </span>
                        {!sidebarColapsado && <span className="whitespace-nowrap">Menú Principal</span>}
                    </button>
                </div>
            </aside>

            {/* --- CONTENIDO PRINCIPAL --- */}
            <main className="flex-1 p-8 overflow-y-auto h-screen relative">
                
                {/* Cabecera del área de trabajo */}
                <header className="mb-8 animate-in slide-in-from-top-4 duration-500">
                    <h2 className="text-3xl font-black text-slate-800 flex items-center gap-3">
                        <span className="text-emerald-600 bg-emerald-100 p-2 rounded-xl">
                            {seccionActiva?.icon}
                        </span>
                        Catálogo de {seccionActiva?.nombre}
                    </h2>
                    <p className="text-slate-500 font-medium mt-2">
                        Gestiona la base de datos de {seccionCatalogo} de la unidad médica.
                    </p>
                </header>

                {/* Zona de Renderizado de Tablas CRUD */}
                <div className="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 p-8 min-h-[600px] flex flex-col">
                    
                    {/* 1. MÓDULO DE MÉDICOS */}
                    {seccionCatalogo === 'medicos' && (
                        <div className="animate-in fade-in duration-300 flex-1 flex flex-col">
                            <div className="flex justify-between items-center mb-8 pb-4 border-b border-slate-100">
                                <h3 className="text-xl font-bold text-slate-700">Directorio Médico</h3>
                                <button className="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-emerald-200 transition-all active:scale-95 flex items-center gap-2">
                                    <span className="text-lg leading-none">+</span> Nuevo Médico
                                </button>
                            </div>
                            
                            <div className="flex-1 border-2 border-dashed border-slate-200 rounded-2xl flex items-center justify-center bg-slate-50/50">
                                <div className="text-center">
                                    <Users size={48} className="mx-auto text-slate-300 mb-4" />
                                    <p className="text-slate-500 font-medium text-lg">Área de trabajo lista para la tabla CRUD.</p>
                                    <p className="text-slate-400 text-sm mt-1">Conectando con la base de datos...</p>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* 2. MÓDULO DE DIAGNÓSTICOS (COMPLETADO) */}
                    {seccionCatalogo === 'diagnosticos' && (
                        <div className="animate-in fade-in duration-300 flex-1 flex flex-col h-full">
                            
                            {/* Cabecera de la tabla */}
                            <div className="flex justify-between items-center mb-6 pb-4 border-b border-slate-100">
                                <h3 className="text-xl font-bold text-slate-700">Listado de Códigos CIE-10</h3>
                                <span className="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-sm font-bold">
                                    Total: {listaDiagnosticos.length}
                                </span>
                            </div>

                            {/* Contenedor de la Tabla con Scroll */}
                            <div className="flex-1 overflow-auto border border-slate-200 rounded-2xl shadow-sm mb-6 max-h-[500px] custom-scrollbar">
                                {cargando ? (
                                    <div className="h-full min-h-[200px] flex flex-col items-center justify-center text-slate-400">
                                        <Loader2 className="animate-spin mb-2" size={32} />
                                        <p>Cargando catálogo...</p>
                                    </div>
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
                                                <tr>
                                                    <td colSpan="3" className="text-center p-8 text-slate-400 italic">
                                                        No hay diagnósticos registrados.
                                                    </td>
                                                </tr>
                                            ) : (
                                                listaDiagnosticos.map((diag) => (
                                                    <tr key={diag.codigo} className="hover:bg-slate-50 border-b border-slate-100 transition-colors group">
                                                        <td className="p-4 font-black text-slate-700">{diag.codigo}</td>
                                                        <td className="p-4 text-slate-600 font-medium">{diag.descripcion}</td>
                                                        <td className="p-4">
                                                            <div className="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                <button 
                                                                    onClick={() => handleEditar(diag)}
                                                                    className="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors"
                                                                    title="Editar"
                                                                >
                                                                    <Pencil size={18} />
                                                                </button>
                                                                <button 
                                                                    onClick={() => handleBorrar(diag.codigo)}
                                                                    className="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                                                                    title="Eliminar"
                                                                >
                                                                    <Trash2 size={18} />
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                ))
                                            )}
                                        </tbody>
                                    </table>
                                )}
                            </div>

                            {/* Botón de Agregar (Abajo) */}
                            <div className="flex justify-start">
                                <button 
                                    onClick={handleAbrirNuevo}
                                    className="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-emerald-200 transition-all active:scale-95 flex items-center gap-2"
                                >
                                    <Plus size={20} />
                                    Agregar Nuevo Diagnóstico
                                </button>
                            </div>

                        </div>
                    )}

                    {/* 3. PLACEHOLDER (Para los demás módulos no desarrollados aún) */}
                    {seccionCatalogo !== 'medicos' && seccionCatalogo !== 'diagnosticos' && (
                        <div className="flex-1 flex items-center justify-center animate-in fade-in duration-300">
                            <div className="text-center text-slate-400">
                                {seccionActiva?.icon && <div className="flex justify-center mb-4 opacity-50">{React.cloneElement(seccionActiva.icon, { size: 64 })}</div>}
                                <p className="text-xl font-medium">Módulo de {seccionActiva?.nombre} en desarrollo</p>
                                <p className="text-sm mt-2">Próximamente disponible en esta versión.</p>
                            </div>
                        </div>
                    )}
                    
                </div>
            </main>

            {/* ========================================== */}
            {/* VENTANA MODAL FLOTANTE (FORMULARIO)        */}
            {/* ========================================== */}
            {modalAbierto && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 animate-in zoom-in-95 duration-300">
                        
                        <div className="flex justify-between items-center mb-6">
                            <h3 className="text-2xl font-black text-slate-800">
                                {modoEdicion ? 'Editar Diagnóstico' : 'Nuevo Diagnóstico'}
                            </h3>
                            <button 
                                onClick={() => setModalAbierto(false)}
                                className="text-slate-400 hover:bg-slate-100 hover:text-slate-600 p-2 rounded-xl transition-colors"
                            >
                                <X size={24} />
                            </button>
                        </div>

                        <form onSubmit={handleGuardar} className="space-y-5">
                            <div>
                                <label className="block text-sm font-bold text-slate-600 mb-2">Código CIE-10</label>
                                <input 
                                    type="text" 
                                    value={formCIE.codigo}
                                    onChange={(e) => setFormCIE({...formCIE, codigo: e.target.value})}
                                    disabled={modoEdicion} // No dejamos editar la llave primaria
                                    placeholder="Ej. J00X"
                                    className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-bold text-slate-700 uppercase focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 disabled:opacity-50"
                                />
                                {modoEdicion && <p className="text-xs text-slate-400 mt-1">El código no se puede modificar una vez creado.</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-bold text-slate-600 mb-2">Descripción Oficial</label>
                                <textarea 
                                    value={formCIE.descripcion}
                                    onChange={(e) => setFormCIE({...formCIE, descripcion: e.target.value})}
                                    placeholder="Descripción detallada de la enfermedad..."
                                    rows="3"
                                    className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 resize-none"
                                />
                            </div>

                            <div className="flex gap-3 pt-4">
                                <button 
                                    type="button"
                                    onClick={() => setModalAbierto(false)}
                                    className="flex-1 px-4 py-3 text-slate-500 font-bold hover:bg-slate-100 rounded-xl transition-colors"
                                >
                                    Cancelar
                                </button>
                                <button 
                                    type="submit"
                                    disabled={guardando}
                                    className="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-emerald-200 transition-all flex justify-center items-center gap-2 active:scale-95 disabled:opacity-70"
                                >
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