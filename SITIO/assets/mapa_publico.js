/* ==========================================================================
   MAPA PÚBLICO (sitio principal)
   Muestra únicamente la ubicación general de la oficina del Patronato.
   No usa la base de datos ni expone viviendas, propietarios ni DNI: es
   completamente independiente del mapa administrativo, aunque usa la misma
   tecnología (Leaflet + OpenStreetMap) para que ambos sean compatibles.

   Ajusta OFICINA_LAT / OFICINA_LNG a la ubicación real de la oficina.
   ========================================================================== */

const OFICINA_LAT = 15.5041;
const OFICINA_LNG = -88.0250;

const mapaPublico = L.map('mapa-publico', {
    scrollWheelZoom: false // evita que el usuario quede "atrapado" haciendo scroll en la página
}).setView([OFICINA_LAT, OFICINA_LNG], 15);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19
}).addTo(mapaPublico);

L.marker([OFICINA_LAT, OFICINA_LNG])
    .addTo(mapaPublico)
    .bindPopup('<strong>Patronato el Zapotal</strong><br>Oficina principal')
    .openPopup();
