let usuarioActual = { rol: 'consulta' }; // Por defecto asumimos vista de solo consulta
let relaciones = {};
let datosTransformados = [];
let ultimoResultado = [];
let syncTimer = null;

const MAX_FILE_SIZE = 5 * 1024 * 1024;

const API = {
    buscar: 'buscar.php',
    subirInventario: 'subir_inventario.php',
    subirMetodologia: 'subir_metodologia.php'
};

document.addEventListener('DOMContentLoaded', () => {
    // Solo dejamos los elementos que SÍ existen en tu nuevo HTML
    document.getElementById('fileUpload').addEventListener('change', procesarArchivo);
    document.getElementById('fileMetodologia').addEventListener('change', cargarMetodologia);
    document.getElementById('searchBtn').addEventListener('click', realizarBusqueda);
    document.getElementById('downloadBtn').addEventListener('click', descargarExcel);
    document.getElementById('filtroTabla').addEventListener('input', filtrarTabla);
    document.getElementById('filterZero').addEventListener('change', filtrarTabla);
    
    // Si borraste el botón de "Borrar datos guardados" del HTML, también borra esta línea de abajo:
    document.getElementById('clearStorageBtn').addEventListener('click', limpiarDatos);

    // Iniciamos la app directamente leyendo la URL
    inicializarApp();
});

async function inicializarApp() {
    // Leemos el parámetro de la URL (ej: ?rol=admin o ?rol=usuario)
    const queryParams = new URLSearchParams(window.location.search);
    const rolURL = queryParams.get('rol');

    if (rolURL === 'admin') {
        usuarioActual = { rol: 'admin', nombre: 'Administrador' };
    } else {
        usuarioActual = { rol: 'consulta', nombre: 'Usuario' };
    }

    mostrarApp();
    await cargarDatos();
    iniciarActualizacionTiempoReal();
}

function mostrarApp() {
    document.getElementById('appView').classList.remove('hidden');
    document.getElementById('sessionName').textContent = usuarioActual.nombre;
    document.getElementById('sessionRole').textContent = usuarioActual.rol;

    const esAdmin = usuarioActual.rol === 'admin';

    document.querySelectorAll('.admin-only').forEach((elemento) => {
        elemento.classList.toggle('hidden', !esAdmin);
    });
}

async function cargarDatos() {
    mostrarMensaje('Cargando datos...', 'info');

    try {
        const result = await fetchJson(API.buscar);
        datosTransformados = result.inventario || [];
        relaciones = result.metodologia || {};

        guardarRespaldoLocal();
        refrescarInterfaz();

        if (datosTransformados.length || Object.keys(relaciones).length) {
            mostrarMensaje('Datos cargados correctamente.', 'success');
        } else {
            mostrarMensaje('No hay datos cargados todavía.', 'info');
        }
    } catch (error) {
        datosTransformados = leerLocalStorage('inventarioIFU', []);
        relaciones = leerLocalStorage('relacionesIFU', {});
        refrescarInterfaz();
        mostrarMensaje(`No se pudo leer MySQL. Se usó localStorage como respaldo. ${error.message}`, 'error');
    }
}

function iniciarActualizacionTiempoReal() {
    detenerActualizacionTiempoReal();

    syncTimer = setInterval(async () => {
        try {
            const result = await fetchJson(API.buscar);
            const inventarioNuevo = JSON.stringify(result.inventario || []);
            const metodologiaNueva = JSON.stringify(result.metodologia || {});

            if (
                inventarioNuevo !== JSON.stringify(datosTransformados) ||
                metodologiaNueva !== JSON.stringify(relaciones)
            ) {
                datosTransformados = result.inventario || [];
                relaciones = result.metodologia || {};
                guardarRespaldoLocal();
                refrescarInterfaz();
                mostrarMensaje('Datos actualizados.', 'success');
            }
        } catch (error) {
            console.warn('Actualización omitida:', error);
        }
    }, 15000);
}

function detenerActualizacionTiempoReal() {
    if (syncTimer) {
        clearInterval(syncTimer);
        syncTimer = null;
    }
}

async function fetchJson(url, options = {}) {
    const response = await fetch(url, {
        cache: 'no-store',
        ...options
    });
    const result = await response.json();

    if (!response.ok || !result.ok) {
        throw new Error(result.message || 'Error en la solicitud');
    }

    return result;
}

function leerArchivoExcel(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();

        reader.onload = (evento) => {
            try {
                const data = new Uint8Array(evento.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const hoja = workbook.Sheets[workbook.SheetNames[0]];
                const cuadricula = XLSX.utils.sheet_to_json(hoja, {
                    header: 1,
                    defval: ''
                });

                resolve(cuadricula);
            } catch (error) {
                reject(error);
            }
        };

        reader.onerror = () => reject(new Error('No se pudo leer el archivo'));
        reader.readAsArrayBuffer(file);
    });
}

async function cargarMetodologia(e) {
    if (!esAdmin()) {
        mostrarMensaje('Solo el administrador puede subir metodología.', 'error');
        return;
    }

    const file = e.target.files[0];
    if (!file) return;

    mostrarLoading(true);
    mostrarMensaje('Procesando metodología...', 'info');

    try {
        validarArchivo(file);

        const cuadricula = await leerArchivoExcel(file);
        relaciones = transformarMetodologia(cuadricula);
        validarMetodologiaProcesada(relaciones);

        await fetchJson(API.subirMetodologia, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                file: archivoMeta(file),
                metodologia: relaciones
            })
        });

        guardarRespaldoLocal();
        refrescarInterfaz();
        mostrarMensaje('Metodología actualizada correctamente.', 'success');
    } catch (error) {
        mostrarMensaje(`Error al subir metodología: ${error.message}`, 'error');
    } finally {
        mostrarLoading(false);
        e.target.value = '';
    }
}

function transformarMetodologia(cuadricula) {
    const nuevasRelaciones = {};

    cuadricula.forEach((fila) => {
        const columnas = fila.map((celda) => celda ? celda.toString().trim() : '');
        const clavePadre = limpiarClave(columnas[2] || '');
        const descripcion = (columnas[3] || '').toString().trim();

        if (/^\d{4,6}$/.test(clavePadre)) {
            nuevasRelaciones[clavePadre] = {
                descripcion,
                hijos: []
            };

            columnas.forEach((valor) => {
                const claveHijo = limpiarClave(valor);

                if (
                    /^\d{4,6}$/.test(claveHijo) &&
                    claveHijo !== clavePadre &&
                    !nuevasRelaciones[clavePadre].hijos.includes(claveHijo)
                ) {
                    nuevasRelaciones[clavePadre].hijos.push(claveHijo);
                }
            });
        }
    });

    return nuevasRelaciones;
}

async function procesarArchivo(e) {
    if (!esAdmin()) {
        mostrarMensaje('Solo el administrador puede subir inventario.', 'error');
        return;
    }

    const file = e.target.files[0];
    if (!file) return;

    mostrarLoading(true);
    mostrarMensaje('Leyendo archivo Excel...', 'info');

    try {
        validarArchivo(file);

        const cuadricula = await leerArchivoExcel(file);
        datosTransformados = transformarInventario(cuadricula);
        validarInventarioProcesado(datosTransformados);

        // CONFIGURACIÓN DE LOTES: Dividimos los datos en paquetes de 500
        const tamanoLote = 500;
        const totalLotes = Math.ceil(datosTransformados.length / tamanoLote);

        for (let i = 0; i < totalLotes; i++) {
            const lote = datosTransformados.slice(i * tamanoLote, (i + 1) * tamanoLote);
            const isFirstChunk = (i === 0);

            // Le avisamos al usuario el progreso
            mostrarMensaje(`Guardando en servidor... Lote ${i + 1} de ${totalLotes}`, 'info');

            await fetchJson(API.subirInventario, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    file: archivoMeta(file),
                    inventario: lote,
                    isFirstChunk: isFirstChunk // Le avisamos a PHP si debe borrar la tabla
                })
            });
        }

        guardarRespaldoLocal();
        refrescarInterfaz();
        mostrarMensaje('Inventario subido y actualizado correctamente.', 'success');
    } catch (error) {
        mostrarMensaje(`Error al subir inventario: ${error.message}`, 'error');
    } finally {
        mostrarLoading(false);
        e.target.value = '';
    }
}

function transformarInventario(cuadricula) {
    let filaEncabezados = -1;

    for (let i = 0; i < cuadricula.length; i++) {
        const texto = cuadricula[i].join(' ').toUpperCase();

        if (texto.includes('50100') || texto.includes('60000')) {
            filaEncabezados = i;
            break;
        }
    }

    if (filaEncabezados === -1) {
        throw new Error('No se encontraron columnas obligatorias 50100 o 60000');
    }

    const encabezados = cuadricula[filaEncabezados] || [];
    const inventario = [];

    for (let f = filaEncabezados + 1; f < cuadricula.length; f++) {
        const filaActual = cuadricula[f] || [];
        const textoFila = filaActual.join(' ').toUpperCase();

        if (textoFila.includes('HGP 48') || textoFila.includes('BAJIO') || textoFila.includes('BAJÍO')) {
            for (let c = 0; c < encabezados.length; c++) {
                const titulo = encabezados[c] ? encabezados[c].toString().trim() : '';
                const valorCrudo = filaActual[c] ? filaActual[c].toString().trim() : '0';
                const coincidencia = titulo.match(/^(\d{4,})\s+(.*)/);

                if (coincidencia) {
                    inventario.push({
                        clave: coincidencia[1],
                        descripcion: coincidencia[2],
                        cantidad: normalizarCantidad(valorCrudo)
                    });
                }
            }
        }
    }

    return inventario.filter((item, index, self) =>
        index === self.findIndex((t) =>
            t.clave === item.clave &&
            t.descripcion === item.descripcion &&
            t.cantidad === item.cantidad
        )
    );
}

function reconstruirDatalist() {
    const lista = document.getElementById('listaClaves');
    lista.innerHTML = '';

    const opciones = datosTransformados.map((item) => `${item.clave} - ${item.descripcion}`);

    Object.keys(relaciones).forEach((clave) => {
        const descripcion = relaciones[clave].descripcion || '';
        opciones.push(`${clave} - ${descripcion}`);
    });

    [...new Set(opciones)].sort().forEach((texto) => {
        const opt = document.createElement('option');
        opt.value = texto;
        lista.appendChild(opt);
    });
}

function calcularDashboard() {
    const censables = datosTransformados.find((x) => x.clave === '50100');
    const noCensables = datosTransformados.find((x) => x.clave === '60000');

    document.getElementById('totalCensables').innerText = censables ? censables.cantidad : 0;
    document.getElementById('totalNoCensables').innerText = noCensables ? noCensables.cantidad : 0;
}

function realizarBusqueda() {
    const inputVal = document.getElementById('searchInput').value.trim();
    const divResultados = document.getElementById('results');
    const detalleDiv = document.getElementById('detalleDesglose');

    divResultados.innerHTML = '';
    detalleDiv.innerHTML = '';
    ultimoResultado = [];

    if (!inputVal) {
        mostrarSinResultados('Escribe o selecciona una clave IFU.');
        return;
    }

    const claveBuscada = inputVal.split(' - ')[0].trim();
    const relacion = relaciones[claveBuscada];

    if (!relacion) {
        const encontrados = datosTransformados.filter((item) => item.clave.toString().trim() === claveBuscada);

        if (encontrados.length === 0) {
            const texto = inputVal.toLowerCase();
            const porDescripcion = datosTransformados.filter((item) =>
                item.descripcion.toLowerCase().includes(texto) ||
                item.clave.toLowerCase().includes(texto)
            );

            if (porDescripcion.length === 0) {
                mostrarSinResultados('No existe información.');
                return;
            }

            divResultados.innerHTML = construirTabla(porDescripcion);
            filtrarTabla();
            return;
        }

        divResultados.innerHTML = construirTabla(encontrados);
        filtrarTabla();
        return;
    }

    const filas = [];

    relacion.hijos.forEach((hijo) => {
        const encontrados = datosTransformados.filter((item) =>
            item.clave.toString().trim() === hijo.toString().trim()
        );

        encontrados.forEach((encontrado) => filas.push(encontrado));
    });

    if (filas.length === 0) {
        mostrarSinResultados('No hay inventario asociado a esta metodología.');
        return;
    }

    divResultados.innerHTML = construirTabla(filas, true);
    filtrarTabla();
}

function construirTabla(filas, incluirDesglose = false, registrarResultado = true) {
    let tabla = `
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Clave</th>
                        <th>Descripción</th>
                        <th>Inventario</th>
                    </tr>
                </thead>
                <tbody>
    `;

    filas.forEach((item) => {
        if (registrarResultado) {
            ultimoResultado.push({
                clave: item.clave,
                descripcion: item.descripcion,
                inventario: item.cantidad
            });
        }

        const tieneHijos = incluirDesglose && relaciones[item.clave];

        tabla += `
            <tr>
                <td>${escaparHtml(item.clave)}</td>
                <td>
                    ${escaparHtml(item.descripcion)}
                    ${tieneHijos ? `<br><br><button type="button" onclick="mostrarDesglose('${escaparAtributo(item.clave)}')">Ver desglose</button>` : ''}
                </td>
                <td>${escaparHtml(item.cantidad)}</td>
            </tr>
        `;
    });

    tabla += `
                </tbody>
            </table>
        </div>
    `;

    return tabla;
}

function mostrarDesglose(clavePadre) {
    const detalleDiv = document.getElementById('detalleDesglose');
    const relacion = relaciones[clavePadre];

    detalleDiv.innerHTML = '';

    if (!relacion) return;

    const filas = [];

    relacion.hijos.forEach((hijo) => {
        const encontrados = datosTransformados.filter((item) =>
            item.clave.toString().trim() === hijo.toString().trim()
        );

        encontrados.forEach((encontrado) => filas.push(encontrado));
    });

    detalleDiv.innerHTML = `
        <div class="table-card">
            <div class="table-title detalle-title">
                <span>Desglose de: ${escaparHtml(clavePadre)}</span>
                <div class="detalle-actions">
                    <button type="button" class="detail-download-btn" onclick="descargarDesglose()">Descargar desglose</button>
                    <button type="button" class="hide-detail-btn" onclick="ocultarDesglose()">Ocultar desglose</button>
                </div>
            </div>
            ${construirTabla(filas, true, false)}
        </div>
    `;
}

function ocultarDesglose() {
    document.getElementById('detalleDesglose').innerHTML = '';
}

function filtrarTabla() {
    const textoFiltro = document.getElementById('filtroTabla').value.toLowerCase();
    const ocultarCeros = document.getElementById('filterZero').checked;
    const filas = document.querySelectorAll('tbody tr');

    filas.forEach((fila) => {
        const texto = fila.innerText.toLowerCase();
        const inventario = parseInt(fila.cells[2].innerText, 10);
        let mostrar = true;

        if (textoFiltro !== '' && !texto.includes(textoFiltro)) {
            mostrar = false;
        }

        if (ocultarCeros && inventario === 0) {
            mostrar = false;
        }

        fila.style.display = mostrar ? '' : 'none';
    });
}

function descargarExcel() {
    const exportarSoloVisibles = document.getElementById('filterZero').checked;
    const consulta = obtenerDatosTablaParaExcel('#results', exportarSoloVisibles);
    const desglose = obtenerDatosTablaParaExcel('#detalleDesglose', exportarSoloVisibles);

    if (consulta.length === 0) {
        mostrarMensaje('No hay resultados para exportar.', 'error');
        return;
    }

    const workbook = XLSX.utils.book_new();
    const consultaSheet = crearHojaExcel(consulta);

    XLSX.utils.book_append_sheet(workbook, consultaSheet, 'Consulta IFU');

    if (desglose.length > 0) {
        const desgloseSheet = crearHojaExcel(desglose);
        XLSX.utils.book_append_sheet(workbook, desgloseSheet, 'Desglose');
    }

    XLSX.writeFile(workbook, 'consulta_IFU.xlsx');
    mostrarMensaje('Excel generado correctamente.', 'success');
}

function descargarDesglose() {
    const exportarSoloVisibles = document.getElementById('filterZero').checked;
    const desglose = obtenerDatosTablaParaExcel('#detalleDesglose', exportarSoloVisibles);

    if (desglose.length === 0) {
        mostrarMensaje('No hay desglose visible para exportar.', 'error');
        return;
    }

    const workbook = XLSX.utils.book_new();
    const desgloseSheet = crearHojaExcel(desglose);

    XLSX.utils.book_append_sheet(workbook, desgloseSheet, 'Desglose');
    XLSX.writeFile(workbook, 'desglose_IFU.xlsx');
    mostrarMensaje('Desglose exportado correctamente.', 'success');
}

function obtenerDatosTablaParaExcel(selector, soloVisibles) {
    const contenedor = document.querySelector(selector);

    if (!contenedor) return [];

    const filas = contenedor.querySelectorAll('tbody tr');
    const datos = [];

    filas.forEach((fila) => {
        if (soloVisibles && fila.style.display === 'none') {
            return;
        }

        const celdas = fila.querySelectorAll('td');

        if (celdas.length < 3) return;

        const descripcionCelda = celdas[1].cloneNode(true);
        descripcionCelda.querySelectorAll('button').forEach((button) => button.remove());

        datos.push({
            clave: celdas[0].innerText.trim(),
            descripcion: descripcionCelda.innerText.trim(),
            inventario: normalizarCantidad(celdas[2].innerText.trim())
        });
    });

    return datos;
}

function crearHojaExcel(datos) {
    const encabezados = ['Clave', 'Descripción', 'Inventario'];
    const filas = datos.map((item) => [
        item.clave,
        item.descripcion,
        item.inventario
    ]);
    const worksheet = XLSX.utils.aoa_to_sheet([encabezados, ...filas]);

    worksheet['!cols'] = calcularAnchoColumnas([encabezados, ...filas]);
    worksheet['!autofilter'] = {
        ref: XLSX.utils.encode_range({
            s: { r: 0, c: 0 },
            e: { r: filas.length, c: encabezados.length - 1 }
        })
    };

    encabezados.forEach((_, index) => {
        const celda = XLSX.utils.encode_cell({ r: 0, c: index });

        if (worksheet[celda]) {
            worksheet[celda].s = {
                font: { bold: true },
                alignment: { horizontal: 'center' }
            };
        }
    });

    return worksheet;
}

function calcularAnchoColumnas(filas) {
    const totalColumnas = filas[0] ? filas[0].length : 0;

    return Array.from({ length: totalColumnas }, (_, columna) => {
        const maximo = filas.reduce((ancho, fila) => {
            const valor = fila[columna] === null || fila[columna] === undefined
                ? ''
                : String(fila[columna]);

            return Math.max(ancho, valor.length);
        }, 10);

        return { wch: Math.min(Math.max(maximo + 2, 12), 55) };
    });
}

async function limpiarDatos() {
    if (!esAdmin()) {
        mostrarMensaje('Solo el administrador puede borrar datos.', 'error');
        return;
    }

    const confirmar = confirm('¿Deseas borrar los datos guardados?');

    if (!confirmar) return;

    try {
        await Promise.all([
            fetchJson(API.subirInventario, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ clear: true })
            }),
            fetchJson(API.subirMetodologia, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ clear: true })
            })
        ]);

        datosTransformados = [];
        relaciones = {};
        ultimoResultado = [];
        localStorage.removeItem('inventarioIFU');
        localStorage.removeItem('relacionesIFU');
        refrescarInterfaz();

        document.getElementById('results').innerHTML = `
            <div class="welcome-card">
                <h2>Bienvenido</h2>
                <p>Busca una clave IFU para consultar el inventario.</p>
            </div>
        `;
        document.getElementById('detalleDesglose').innerHTML = '';
        mostrarMensaje('Datos borrados correctamente.', 'success');
    } catch (error) {
        mostrarMensaje(`No se pudieron borrar los datos: ${error.message}`, 'error');
    }
}

function refrescarInterfaz() {
    reconstruirDatalist();
    calcularDashboard();
    document.getElementById('searchInput').disabled = datosTransformados.length === 0;
}

function validarArchivo(file) {
    const extension = file.name.split('.').pop().toLowerCase();

    if (!['xlsx', 'csv'].includes(extension)) {
        throw new Error('Solo se permiten archivos .xlsx o .csv');
    }

    if (file.size > MAX_FILE_SIZE) {
        throw new Error('El archivo debe pesar máximo 5 MB');
    }
}

function validarInventarioProcesado(inventario) {
    if (!Array.isArray(inventario) || inventario.length === 0) {
        throw new Error('No se detectaron columnas obligatorias de inventario');
    }

    inventario.forEach((item) => {
        if (!item.clave || !item.descripcion || !Object.prototype.hasOwnProperty.call(item, 'cantidad')) {
            throw new Error('El inventario requiere clave, descripción y cantidad');
        }
    });
}

function validarMetodologiaProcesada(metodologia) {
    if (!metodologia || Object.keys(metodologia).length === 0) {
        throw new Error('No se detectaron columnas obligatorias de metodología');
    }

    Object.entries(metodologia).forEach(([clavePadre, relacion]) => {
        if (!clavePadre || !relacion.descripcion || !Array.isArray(relacion.hijos)) {
            throw new Error('La metodología requiere clave padre, descripción e hijos');
        }
    });
}

function archivoMeta(file) {
    return {
        name: file.name,
        size: file.size,
        type: file.type
    };
}

function esAdmin() {
    return usuarioActual && usuarioActual.rol === 'admin';
}

function guardarRespaldoLocal() {
    localStorage.setItem('inventarioIFU', JSON.stringify(datosTransformados));
    localStorage.setItem('relacionesIFU', JSON.stringify(relaciones));
}

function leerLocalStorage(key, fallback) {
    try {
        const raw = localStorage.getItem(key);
        return raw ? JSON.parse(raw) : fallback;
    } catch (error) {
        return fallback;
    }
}

function limpiarClave(valor) {
    return valor.toString().replace('.00', '').replace('.0', '').trim();
}

function normalizarCantidad(valorCrudo) {
    let cantidad = valorCrudo.toString().trim().replace('.00', '').replace(',00', '');

    if (cantidad === '' || cantidad.toLowerCase() === 'false') {
        cantidad = '0';
    }

    const numero = parseInt(cantidad, 10);
    return Number.isNaN(numero) ? 0 : numero;
}

function mostrarSinResultados(mensaje) {
    document.getElementById('results').innerHTML = `
        <div class="welcome-card">
            <h2>Sin resultados</h2>
            <p>${escaparHtml(mensaje)}</p>
        </div>
    `;
}

function mostrarLoading(mostrar) {
    document.getElementById('loading').classList.toggle('hidden', !mostrar);
}

function mostrarMensaje(texto, tipo = 'info') {
    const box = document.getElementById('messageBox');
    if (!box) return; // Por si el mensaje se intenta mostrar y la caja no existe en el HTML
    box.classList.remove('hidden'); // Aseguramos que se vea
    box.textContent = texto;
    box.className = `message ${tipo}`;
    
    // Opcional: Ocultar el mensaje después de unos segundos
    setTimeout(() => {
        box.classList.add('hidden');
    }, 5000);
}

function escaparHtml(valor) {
    return String(valor)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function escaparAtributo(valor) {
    return String(valor).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}