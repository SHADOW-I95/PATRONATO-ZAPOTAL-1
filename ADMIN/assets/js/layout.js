/* Controla el sidebar deslizante en pantallas de celular (ver barras.css) */
function abrirSidebarAdmin() {
    document.getElementById('barraLateralAdmin')?.classList.add('abierto');
    document.getElementById('overlaySidebarAdmin')?.classList.add('visible');
}

function cerrarSidebarAdmin() {
    document.getElementById('barraLateralAdmin')?.classList.remove('abierto');
    document.getElementById('overlaySidebarAdmin')?.classList.remove('visible');
}

// Si el empleado toca un módulo del menú estando en celular, cerramos
// el panel automáticamente para que no tape el contenido que acaba de abrir.
document.querySelectorAll('.barraNavegacion a').forEach((enlace) => {
    enlace.addEventListener('click', cerrarSidebarAdmin);
});