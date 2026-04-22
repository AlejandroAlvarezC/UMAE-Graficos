import React, { useState, useEffect } from 'react';
import axios from 'axios';

const AdministradorUsuarios = () => {
    // 1. Estados de la tabla
    const [usuarios, setUsuarios] = useState([]);
    const [cargando, setCargando] = useState(true);
    const [error, setError] = useState(null);

    const [editandoUser, setEditandoUser] = useState(null); // Usuario que se está editando
    const [formEdit, setFormEdit] = useState({ nombre: '', correo: '', rol: '', password: '' });

    // 2. Estados del Formulario
    const [mostrarModal, setMostrarModal] = useState(false);
    const [guardando, setGuardando] = useState(false);
    const [formData, setFormData] = useState({
        nombre: '',
        correo: '',
        password: '',
        rol: 'viewer', // Por defecto será solo visualización
        estado: 'activo'
    });

    // 3. Cargar usuarios al inicio
    useEffect(() => {
        cargarUsuarios();
    }, []);

    const cargarUsuarios = () => {
        setCargando(true);
        axios.get('/api/api_obtener_usuarios.php')
            .then(res => {
                if (Array.isArray(res.data)) {
                    setUsuarios(res.data);
                }
                setCargando(false);
            })
            .catch(err => {
                console.error("Error:", err);
                setCargando(false);
            });
    };

    // 4. Manejo del Formulario
    const handleInputChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const guardarUsuario = async (e) => {
        e.preventDefault(); // Evita que la página se recargue
        setGuardando(true);

        try {
            const res = await axios.post('/api/api_crear_usuario.php', formData);
            
            if (res.data.exito) {
                alert("Usuario creado exitosamente");
                setMostrarModal(false); // Cerramos el modal
                cargarUsuarios(); // Recargamos la tabla para ver al nuevo
                // Limpiamos el formulario
                setFormData({ nombre: '', correo: '', password: '', rol: 'viewer', estado: 'activo' });
            } else {
                alert(res.data.error || "Hubo un error al guardar");
            }
        } catch (err) {
            console.error("Error guardando:", err);
            alert("Error de conexión al guardar el usuario.");
        }
        setGuardando(false);
    };

    // Función para abrir el modal con los datos del usuario seleccionado
    const abrirEditar = (user) => {
        // 1. Metemos TODOS los datos al formulario (incluyendo el ID)
        setFormEdit({
            id: user.id,
            nombre: user.nombre,
            correo: user.correo,
            rol: user.rol,
            password: '' 
        });
        
        // 2. Si usas 'editandoUser' para algo más, lo guardamos
        if (typeof setEditandoUser === 'function') {
            setEditandoUser(user);
        }

        // 3. ¡IMPORTANTE! Abrimos el modal
        setMostrarModal(true); 
    };

        // Función para enviar la actualización a PHP
    const handleUpdate = async (e) => {
        e.preventDefault();
        try {
            const response = await fetch('https://vencer.infinityfree.me/api/api_editar_usuario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formEdit)
            });
            
            const data = await response.json();
            if (data.success) {
                alert("Usuario actualizado con éxito");
                setMostrarModal(false);
                fetchUsuarios(); 
            } else {
                alert("Error: " + data.error);
            }
        } catch (error) {
            console.error("Error al actualizar:", error);
        }
    };

    const handleEliminar = (id) => {
        if(window.confirm("¿Estás seguro de que deseas eliminar este usuario?")) {
            alert("Aquí conectaremos el PHP para eliminar después.");
        }
    };



    return (
        <div className="p-6 bg-slate-50 min-h-screen font-sans">
            {/* Encabezado y Botón Principal */}
            <div className="flex justify-between items-center mb-8">
                <div>
                    <h1 className="text-3xl font-bold text-slate-800">Administración de Usuarios</h1>
                    <p className="text-slate-500 mt-1">Gestiona los accesos, roles y permisos del personal.</p>
                </div>
                <button 
                    onClick={() => setMostrarModal(true)} // Abre el modal
                    className="bg-[#7a123a] hover:bg-[#5a0d2a] text-white px-5 py-2.5 rounded-lg font-medium shadow-sm transition-colors flex items-center gap-2"
                >
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Nuevo Usuario
                </button>
            </div>
 

            <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                {cargando ? (
                    <div className="p-8 text-center text-slate-500">Cargando usuarios...</div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-left border-collapse">
                            <thead>
                                <tr className="bg-slate-50 border-b border-slate-200 text-slate-600 text-sm uppercase tracking-wider">
                                    <th className="p-4 font-semibold">Nombre</th>
                                    <th className="p-4 font-semibold">Usuario/Correo</th>
                                    <th className="p-4 font-semibold">Rol</th>
                                    <th className="p-4 font-semibold">Acceso y Dispositivo</th>
                                    <th className="p-4 font-semibold text-center">Visitas</th>
                                    <th className="p-4 font-semibold">Estado</th>
                                    <th className="p-4 font-semibold text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {usuarios.map((user) => (
                                    <tr key={user.id} className="hover:bg-slate-50 transition-colors">
                                        <td className="p-4 font-medium text-slate-800">{user.nombre}</td>
                                        <td className="p-4 text-slate-600">{user.correo}</td>
                                        <td className="p-4">
                                            <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                                user.rol === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-700'
                                            }`}>
                                                {user.rol === 'admin' ? 'Administrador' : 'Visualización'}
                                            </span>
                                        </td>
                                        
                                        {/* NUEVA COLUMNA: ÚLTIMO ACCESO Y NAVEGADOR */}
                                        <td className="p-4">
                                            <div className="flex flex-col">
                                                <span className="text-sm font-bold text-slate-700">
                                                    {user.ultimo_acceso !== 'Sin registros' 
                                                        ? new Date(user.ultimo_acceso).toLocaleString() 
                                                        : 'Nunca'}
                                                </span>
                                                <span 
                                                    className="text-[10px] text-blue-500 truncate max-w-[180px] font-medium" 
                                                    title={user.navegador}
                                                >
                                                    {user.navegador || 'Desconocido'}
                                                </span>
                                            </div>
                                        </td>

                                        {/* NUEVA COLUMNA: CONTADOR DE VISITAS */}
                                        <td className="p-4 text-center">
                                            <span className="font-black text-[#822626] bg-red-50 px-3 py-1 rounded-lg">
                                                {user.visitas || 0}
                                            </span>
                                        </td>

                                        <td className="p-4">
                                            <span className="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-medium">
                                                {user.estado}
                                            </span>
                                        </td>
                                        <td className="p-4 flex gap-2 justify-end">
                                            {/* BOTÓN EDITAR */}
                                            <button 
                                                type="button"
                                                onClick={() => abrirEditar(user)} 
                                                className="text-blue-600 hover:bg-blue-50 p-2 rounded transition-colors"
                                            >
                                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button 
                                                onClick={() => handleEliminar(user.id)} 
                                                className="text-red-600 hover:bg-red-50 p-2 rounded transition-colors"
                                                title="Eliminar usuario"
                                            >
                                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>

            {/* ========================================== */}
            {/* VENTANA EMERGENTE (MODAL) DEL FORMULARIO   */}
            {/* ========================================== */}
            {mostrarModal && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                    <div className="bg-white rounded-xl shadow-lg w-full max-w-md overflow-hidden">
                        
                        <div className="p-5 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                            <h2 className="text-xl font-bold text-slate-800">Agregar Nuevo Usuario</h2>
                            <button onClick={() => setMostrarModal(false)} className="text-slate-400 hover:text-slate-600">
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <form onSubmit={guardarUsuario} className="p-5">
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-slate-700 mb-1">Nombre Completo</label>
                                    <input required type="text" name="nombre" value={formData.nombre} onChange={handleInputChange} className="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:border-[#7a123a] focus:ring-1 focus:ring-[#7a123a]" placeholder="Ej. Dr. Juan Pérez" />
                                </div>
                                
                                <div>
                                    <label className="block text-sm font-medium text-slate-700 mb-1">Usuario / Correo</label>
                                    <input required type="text" name="correo" value={formData.correo} onChange={handleInputChange} className="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:border-[#7a123a] focus:ring-1 focus:ring-[#7a123a]" placeholder="usuario@imss.gob.mx" />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-slate-700 mb-1">Contraseña</label>
                                    <input required type="password" name="password" value={formData.password} onChange={handleInputChange} className="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:border-[#7a123a] focus:ring-1 focus:ring-[#7a123a]" placeholder="••••••••" />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-slate-700 mb-1">Permisos</label>
                                    <select name="rol" value={formData.rol} onChange={handleInputChange} className="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:border-[#7a123a] focus:ring-1 focus:ring-[#7a123a] bg-white">
                                        <option value="viewer">Solo Visualización (Consultar gráficas)</option>
                                        <option value="admin">Administrador (Control total)</option>
                                    </select>
                                </div>
                            </div>

                            <div className="mt-6 flex justify-end gap-3">
                                <button type="button" onClick={() => setMostrarModal(false)} className="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                                    Cancelar
                                </button>
                                <button type="submit" disabled={guardando} className={`px-4 py-2 text-white rounded-lg transition-colors ${guardando ? 'bg-slate-400' : 'bg-[#7a123a] hover:bg-[#5a0d2a]'}`}>
                                    {guardando ? 'Guardando...' : 'Guardar Usuario'}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            )} 

            {/* MODAL DE EDICIÓN */}
            {mostrarModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-300">
                    <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-in zoom-in-95 duration-300">
                        <div className="bg-[#822626] p-6 text-white flex justify-between items-center">
                            <h3 className="text-xl font-black">Editar Usuario</h3>
                            <button onClick={() => setMostrarModal(false)} className="hover:rotate-90 transition-transform">
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        
                        <form onSubmit={handleUpdate} className="p-6 space-y-4">
                            <div>
                                <label className="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Nombre Completo</label>
                                <input 
                                    type="text" required
                                    value={formEdit.nombre}
                                    onChange={(e) => setFormEdit({...formEdit, nombre: e.target.value})}
                                    className="w-full border border-slate-200 rounded-lg p-3 text-slate-700 focus:ring-2 focus:ring-red-100 focus:border-[#822626] outline-none transition-all"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Correo Electrónico</label>
                                <input 
                                    type="email" required
                                    value={formEdit.correo}
                                    onChange={(e) => setFormEdit({...formEdit, correo: e.target.value})}
                                    className="w-full border border-slate-200 rounded-lg p-3 text-slate-700 focus:ring-2 focus:ring-red-100 focus:border-[#822626] outline-none transition-all"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Rol de Usuario</label>
                                <select 
                                    value={formEdit.rol}
                                    onChange={(e) => setFormEdit({...formEdit, rol: e.target.value})}
                                    className="w-full border border-slate-200 rounded-lg p-3 text-slate-700 focus:ring-2 focus:ring-red-100 focus:border-[#822626] outline-none transition-all appearance-none"
                                >
                                    <option value="admin">Administrador</option>
                                    <option value="viewer">Visualización</option>
                                </select>
                            </div>

                            <div className="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <label className="block text-xs font-black text-[#822626] uppercase tracking-widest mb-1">Cambiar Contraseña</label>
                                <input 
                                    type="password"
                                    placeholder="Dejar en blanco para no cambiar"
                                    value={formEdit.password}
                                    onChange={(e) => setFormEdit({...formEdit, password: e.target.value})}
                                    className="w-full border border-slate-200 rounded-lg p-3 text-slate-700 focus:ring-2 focus:ring-red-100 focus:border-[#822626] outline-none transition-all"
                                />
                                <p className="text-[10px] text-slate-400 mt-2 italic font-medium">
                                    *Solo escribe si deseas asignar una nueva contraseña.
                                </p>
                            </div>

                            <div className="flex gap-3 pt-4">
                                <button 
                                    type="button" 
                                    onClick={() => setMostrarModal(false)}
                                    className="flex-1 px-4 py-3 border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors"
                                >
                                    Cancelar
                                </button>
                                <button 
                                    type="submit"
                                    className="flex-1 px-4 py-3 bg-[#822626] text-white font-bold rounded-xl shadow-lg shadow-red-100 hover:scale-105 active:scale-95 transition-all"
                                >
                                    Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
            
        </div>
    );
};

export default AdministradorUsuarios;