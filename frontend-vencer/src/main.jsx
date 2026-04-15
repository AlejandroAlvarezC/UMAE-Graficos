import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './index.css'

// 1. Importas tu App principal (que carga el Vencer y luego el Dashboard)
import App from './App.jsx'

// 2. Importas tu nueva vista para el PHP
import AdministradorUsuarios from './componentes/AdministradorUsuarios.jsx'

// ==========================================
// RUTA 1: Si estamos en la página de tu tablero principal
// (Busca el id "root" que normalmente tiene el index.html principal)
// ==========================================
const rootDashboard = document.getElementById('root');
if (rootDashboard) {
    createRoot(rootDashboard).render(
        <StrictMode>
            <App /> 
        </StrictMode>
    );
}

// ==========================================
// RUTA 2: Si estamos en usuariosAdmin.php
// ==========================================
console.log("🚀 React ha llegado a la página de usuarios.");

const rootUsuarios = document.getElementById('react-admin-usuarios');
console.log("🔍 Buscando el contenedor en el HTML...", rootUsuarios);

if (rootUsuarios) {
    console.log("✅ ¡Contenedor encontrado! Dibujando AdministradorUsuarios...");
    createRoot(rootUsuarios).render(
        <StrictMode>
            <AdministradorUsuarios />
        </StrictMode>
    );
} else {
    console.warn("❌ React llegó, pero NO encontró el <div id='react-admin-usuarios'>. Revisa que el script esté al fondo del body en tu PHP.");
}