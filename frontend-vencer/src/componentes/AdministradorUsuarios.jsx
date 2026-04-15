import React, { useState, useEffect } from 'react';
import { Users, Shield, UserPlus, Edit, Trash2, X, CheckCircle2, Loader2 } from 'lucide-react';

export default function AdministradorUsuarios() {
    const [usuarios, setUsuarios] = useState([]);
    const [cargando, setCargando] = useState(true); // Para mostrar una ruedita de carga
    const [error, setError] = useState(null);

    const [modalAbierto, setModalAbierto] = useState(false);
    const [usuarioActual, setUsuarioActual] = useState({ nombre: '', correo: '', password: '', rol: 'viewer' });

    // ==========================================
    // 📡 CONEXIÓN CON TU BASE DE DATOS (LA MAGIA)
    // ==========================================
    useEffect(() => {
        // Asegúrate de poner la ruta exacta hacia tu nuevo archivo PHP
        fetch('https://vencer.infinityfree.me/api/api_obtener_usuarios.php')
            .then(respuesta => respuesta.json())
            .then(datos => {
                if (datos.error) {
                    setError(datos.error);
                } else {
                    setUsuarios(datos);
                }
                setCargando(false);
            })
            .catch(error => {
                console.error("Error conectando con PHP:", error);
                setError("Hubo un problema al conectar con el servidor.");
                setCargando(false);
            });
    }, []); // Los corchetes vacíos significan: "Haz esto solo una vez cuando se abra la pantalla"

    return (
        <div className="w-full bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            {/* ENCABEZADO */}
            <div className="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h2 className="text-3xl font-black text-slate-800 flex items-center gap-3">
                    <span className="text-indigo-600 bg-indigo-100 p-2 rounded-xl"><Shield size={28} /></span>
                    Control de Accesos
                </h2>
                <button onClick={() => setModalAbierto(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg font-bold flex items-center gap-2 transition-colors shadow-sm">
                    <UserPlus size={18} /> Nuevo Usuario
                </button>
            </div>

            {/* ESTADOS DE CARGA Y ERROR */}
            {cargando && (
                <div className="flex flex-col items-center justify-center p-12 text-indigo-600">
                    <Loader2 size={40} className="animate-spin mb-4" />
                    <p className="font-bold text-slate-500">Conectando con la base de datos...</p>
                </div>
            )}

            {error && (
                <div className="bg-red-50 text-red-600 p-4 rounded-xl border border-red-200 font-bold text-center mb-6">
                    {error}
                </div>
            )}

            {/* TABLA DE USUARIOS (Solo se muestra si ya cargó y no hay error) */}
            {!cargando && !error && (
                <div className="overflow-x-auto border border-slate-200 rounded-xl">
                    <table className="w-full text-left text-sm text-slate-600">
                        <thead className="text-xs text-slate-400 uppercase bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th className="py-4 px-6 font-bold">ID</th>
                                <th className="py-4 px-6 font-bold">Nombre</th>
                                <th className="py-4 px-6 font-bold">Correo</th>
                                <th className="py-4 px-6 font-bold">Nivel</th>
                                <th className="py-4 px-6 font-bold text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {usuarios.length === 0 ? (
                                <tr>
                                    <td colSpan="5" className="py-8 text-center text-slate-500">No hay usuarios registrados.</td>
                                </tr>
                            ) : (
                                usuarios.map((u) => (
                                    <tr key={u.id} className="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                                        <td className="py-4 px-6 font-bold text-slate-400">#{u.id}</td>
                                        <td className="py-4 px-6 font-bold text-slate-700">{u.nombre}</td>
                                        <td className="py-4 px-6">{u.correo}</td>
                                        <td className="py-4 px-6">
                                            {u.rol === 'admin' ? 
                                                <span className="bg-purple-100 text-purple-700 px-2 py-1 rounded-full text-xs font-bold">Admin</span> : 
                                                <span className="bg-slate-100 text-slate-600 px-2 py-1 rounded-full text-xs font-bold">Visualizador</span>
                                            }
                                        </td>
                                        <td className="py-4 px-6 text-right">
                                            <button className="p-2 text-slate-400 hover:text-indigo-600"><Edit size={16} /></button>
                                            <button className="p-2 text-slate-400 hover:text-red-600"><Trash2 size={16} /></button>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            )}

            {/* MODAL (Se queda igual por ahora) */}
            {modalAbierto && (
                <div className="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                    <div className="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                        <div className="flex justify-between items-center mb-4">
                            <h3 className="font-bold text-lg">Nuevo Usuario</h3>
                            <button onClick={() => setModalAbierto(false)}><X size={20} className="text-slate-400 hover:text-red-500"/></button>
                        </div>
                        <p className="text-slate-500 text-sm mb-4">La función de guardar hacia la base de datos será el siguiente paso.</p>
                        <button onClick={() => setModalAbierto(false)} className="w-full bg-slate-100 text-slate-700 font-bold py-2 rounded-lg">Cerrar</button>
                    </div>
                </div>
            )}
        </div>
    );
}