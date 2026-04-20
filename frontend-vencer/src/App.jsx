import React from 'react';
import DashboardProductividad from './componentes/dashboardProductividad.jsx';
import AdministradorUsuarios from './componentes/AdministradorUsuarios.jsx';

const App = () => {
    const valoresURL = new URLSearchParams(window.location.search);
    const moduloActual = valoresURL.get('modulo');

    // Si la URL tiene ?modulo=usuarios, mostramos administración
    if (moduloActual === 'usuarios') {
        return <AdministradorUsuarios />;
    }

    // Por defecto, mostramos el módulo Vencer
    return <DashboardProductividad />;
};

export default App;