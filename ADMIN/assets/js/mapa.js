/* ==========================================================================
   MAPA.JS
   Muestra las viviendas en un mapa (Leaflet + OpenStreetMap), con búsqueda,
   filtros, y (solo Administrador) registrar/editar/quitar coordenadas.
   ========================================================================== */

const mapa = L.map('mapa-viviendas').setView([15.5041, -88.0250], 14);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19
}).addTo(mapa);

// Un marcador por cada vivienda que ya tiene coordenadas, guardado por
// id_vivienda para poder actualizarlo o quitarlo sin recargar nada.
const marcadores = {};

function buscarVivienda(id) {
    return VIVIENDAS_MAPA.find((v) => String(v.id_vivienda) === String(id));
}

function textoPopup(v) {
    let html = `
        <strong>Vivienda #${v.numero_vivienda}</strong><br>
        Sector: ${v.nombre_sector ?? 'Sin sector'}<br>
        Propietario: ${v.nombre_usuario ?? '—'}<br>
        DNI: ${v.dni ?? '—'}
    `;

    if (PUEDE_EDITAR_MAPA) {
        html += `
            <div class="mapa-popup-acciones">
                <button type="button" class="mapa-popup-editar" data-id="${v.id_vivienda}">Editar ubicación</button>
                <button type="button" class="mapa-popup-quitar" data-id="${v.id_vivienda}">Quitar ubicación</button>
            </div>`;
    }

    return html;
}

function crearOActualizarMarcador(v) {
    if (v.latitud === null || v.longitud === null) return;

    if (marcadores[v.id_vivienda]) {
        marcadores[v.id_vivienda].setLatLng([v.latitud, v.longitud]);
        marcadores[v.id_vivienda].setPopupContent(textoPopup(v));
    } else {
        const marcador = L.marker([v.latitud, v.longitud]).addTo(mapa);
        marcador.bindPopup(textoPopup(v));
        marcadores[v.id_vivienda] = marcador;
    }
}

function quitarMarcador(id) {
    if (marcadores[id]) {
        mapa.removeLayer(marcadores[id]);
        delete marcadores[id];
    }
}

// Pinta todos los marcadores iniciales
VIVIENDAS_MAPA.forEach(crearOActualizarMarcador);

// Ajusta el zoom inicial para que se vean todos los marcadores, si hay alguno
const idsIniciales = Object.keys(marcadores);
if (idsIniciales.length > 0) {
    const grupo = L.featureGroup(idsIniciales.map((id) => marcadores[id]));
    mapa.fitBounds(grupo.getBounds(), { padding: [30, 30] });
}

// Los botones "Editar ubicación"/"Quitar ubicación" viven dentro de un popup
// que Leaflet crea y destruye dinámicamente, por eso se delega el clic sobre
// todo el contenedor del mapa en vez de escuchar cada botón por separado.
document.getElementById('mapa-viviendas').addEventListener('click', (e) => {
    if (e.target.classList.contains('mapa-popup-editar')) {
        seleccionarViviendaParaEditar(e.target.getAttribute('data-id'));
    }
    if (e.target.classList.contains('mapa-popup-quitar')) {
        quitarUbicacion(e.target.getAttribute('data-id'));
    }
});

/* ==========================================================================
   Búsqueda y filtros (sobre el arreglo ya cargado, sin pedir nada al servidor)
   ========================================================================== */

const inputBuscar = document.getElementById('mapa-buscar');
const selectSector = document.getElementById('mapa-filtro-sector');
const checkSinUbicacion = document.getElementById('mapa-solo-sin-ubicacion');
const divResultados = document.getElementById('mapa-resultados');

function viviendaCoincide(v, texto, sector, soloSinUbicacion) {
    if (soloSinUbicacion && v.latitud !== null) return false;
    if (sector && v.nombre_sector !== sector) return false;

    if (texto) {
        const enTexto = `${v.numero_vivienda} ${v.nombre_sector ?? ''} ${v.nombre_usuario ?? ''} ${v.dni ?? ''}`.toLowerCase();
        if (!enTexto.includes(texto.toLowerCase())) return false;
    }
    return true;
}

function aplicarFiltros() {
    const texto = inputBuscar.value.trim();
    const sector = selectSector.value;
    const soloSinUbicacion = checkSinUbicacion.checked;

    const coincidencias = VIVIENDAS_MAPA.filter((v) => viviendaCoincide(v, texto, sector, soloSinUbicacion));

    // Muestra en el mapa solo los marcadores que coinciden con el filtro
    VIVIENDAS_MAPA.forEach((v) => {
        if (v.latitud === null) return; // nunca tuvo marcador, no hay nada que ocultar
        if (coincidencias.includes(v)) {
            crearOActualizarMarcador(v);
        } else {
            quitarMarcador(v.id_vivienda);
        }
    });

    // Lista de resultados en el panel lateral: es la única forma de ver las
    // viviendas sin ubicación, ya que esas nunca tienen marcador en el mapa
    divResultados.innerHTML = '';
    coincidencias.forEach((v) => {
        const item = document.createElement('div');
        item.className = 'mapa-resultado-item';

        const sinUbicacion = v.latitud === null;
        item.innerHTML = `
            <span>#${v.numero_vivienda} — ${v.nombre_sector ?? 'Sin sector'} — ${v.nombre_usuario ?? '—'}</span>
            ${sinUbicacion
                ? '<span class="badge badge-pendiente">Sin ubicación</span>'
                : `<button type="button" class="mapa-btn-centrar" data-id="${v.id_vivienda}">Centrar</button>`}
        `;
        divResultados.appendChild(item);
    });
}

inputBuscar.addEventListener('input', aplicarFiltros);
selectSector.addEventListener('change', aplicarFiltros);
checkSinUbicacion.addEventListener('change', aplicarFiltros);

divResultados.addEventListener('click', (e) => {
    if (e.target.classList.contains('mapa-btn-centrar')) {
        const marcador = marcadores[e.target.getAttribute('data-id')];
        if (marcador) {
            mapa.setView(marcador.getLatLng(), 17);
            marcador.openPopup();
        }
    }
});

aplicarFiltros(); // primera pasada, para que la lista de resultados aparezca desde el inicio

/* ==========================================================================
   Solo Administrador: registrar / editar / quitar ubicaciones.
   Estos elementos solo existen en el HTML si $puedeEditar era true en PHP,
   así que en getElementById() devuelven null para un Empleado normal — por
   eso todo aquí adentro se protege con "if (PUEDE_EDITAR_MAPA)".
   El backend (guardar_ubicacion.php / eliminar_ubicacion.php) vuelve a
   validar el permiso de todas formas, por si alguien intenta llamarlo directo.
   ========================================================================== */

const selectVivienda = document.getElementById('mapa-vivienda-seleccionada');
const btnColocar = document.getElementById('mapa-btn-colocar');
const ayuda = document.getElementById('mapa-ayuda');
const inputLatManual = document.getElementById('mapa-lat-manual');
const inputLngManual = document.getElementById('mapa-lng-manual');
const btnGuardar = document.getElementById('mapa-btn-guardar');
const btnQuitar = document.getElementById('mapa-btn-quitar');

let marcadorTemporal = null;
let esperandoClicEnMapa = false;

function seleccionarViviendaParaEditar(id) {
    if (!PUEDE_EDITAR_MAPA || !selectVivienda) return;
    selectVivienda.value = id;
    selectVivienda.dispatchEvent(new Event('change'));
    selectVivienda.scrollIntoView({ behavior: 'smooth' });
}

function limpiarFormularioEdicion() {
    if (!PUEDE_EDITAR_MAPA) return;
    selectVivienda.value = '';
    btnColocar.disabled = true;
    inputLatManual.value = '';
    inputLngManual.value = '';
    ayuda.textContent = '';
    if (marcadorTemporal) {
        mapa.removeLayer(marcadorTemporal);
        marcadorTemporal = null;
    }
}

function quitarUbicacion(idVivienda) {
    if (!PUEDE_EDITAR_MAPA) return;
    if (!confirm('¿Quitar la ubicación de esta vivienda?')) return;

    const datos = new FormData();
    datos.append('id_vivienda', idVivienda);

    fetch('modulos/mapa/eliminar_ubicacion.php', { method: 'POST', body: datos })
        .then((r) => r.json())
        .then((respuesta) => {
            if (respuesta.ok) {
                const v = buscarVivienda(idVivienda);
                v.latitud = null;
                v.longitud = null;
                quitarMarcador(idVivienda);
                limpiarFormularioEdicion();
                aplicarFiltros();
            } else {
                alert('No se pudo quitar la ubicación: ' + (respuesta.error || 'error desconocido'));
            }
        });
}

if (PUEDE_EDITAR_MAPA) {

    selectVivienda.addEventListener('change', () => {
        btnColocar.disabled = !selectVivienda.value;
        const v = buscarVivienda(selectVivienda.value);
        if (v && v.latitud !== null) {
            inputLatManual.value = v.latitud;
            inputLngManual.value = v.longitud;
        } else {
            inputLatManual.value = '';
            inputLngManual.value = '';
        }
    });

    btnColocar.addEventListener('click', () => {
        esperandoClicEnMapa = true;
        ayuda.textContent = 'Haz clic en el mapa sobre la ubicación de la vivienda…';
    });

    mapa.on('click', (e) => {
        if (!esperandoClicEnMapa) return;

        inputLatManual.value = e.latlng.lat.toFixed(7);
        inputLngManual.value = e.latlng.lng.toFixed(7);

        if (marcadorTemporal) mapa.removeLayer(marcadorTemporal);
        marcadorTemporal = L.marker(e.latlng, { opacity: 0.6 }).addTo(mapa);

        ayuda.textContent = 'Ubicación seleccionada. Revisa las coordenadas y presiona "Guardar ubicación".';
        esperandoClicEnMapa = false;
    });

    btnGuardar.addEventListener('click', () => {
        const idVivienda = selectVivienda.value;
        const lat = inputLatManual.value;
        const lng = inputLngManual.value;

        if (!idVivienda) {
            alert('Selecciona una vivienda primero.');
            return;
        }
        if (lat === '' || lng === '') {
            alert('Indica la latitud y la longitud (haciendo clic en el mapa o escribiéndolas).');
            return;
        }

        const datos = new FormData();
        datos.append('id_vivienda', idVivienda);
        datos.append('latitud', lat);
        datos.append('longitud', lng);

        fetch('modulos/mapa/guardar_ubicacion.php', { method: 'POST', body: datos })
            .then((r) => r.json())
            .then((respuesta) => {
                if (respuesta.ok) {
                    const v = buscarVivienda(idVivienda);
                    v.latitud = parseFloat(lat);
                    v.longitud = parseFloat(lng);
                    crearOActualizarMarcador(v);
                    limpiarFormularioEdicion();
                    aplicarFiltros();
                } else {
                    alert('No se pudo guardar: ' + (respuesta.error || 'error desconocido'));
                }
            });
    });

    btnQuitar.addEventListener('click', () => {
        if (!selectVivienda.value) {
            alert('Selecciona una vivienda primero.');
            return;
        }
        quitarUbicacion(selectVivienda.value);
    });
}