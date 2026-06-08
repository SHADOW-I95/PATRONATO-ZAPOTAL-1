function toggleMenu() {
            const btn     = document.getElementById('BTN_BURGER');
            const menu    = document.getElementById('MENU');
            const overlay = document.getElementById('OVERLAY');

            const estaAbierto = menu.classList.toggle('abierto');
            btn.classList.toggle('activo', estaAbierto);
            overlay.classList.toggle('visible', estaAbierto);
            btn.setAttribute('aria-expanded', estaAbierto);
        }

        function cerrarMenu() {
            document.getElementById('BTN_BURGER').classList.remove('activo');
            document.getElementById('MENU').classList.remove('abierto');
            document.getElementById('OVERLAY').classList.remove('visible');
            document.getElementById('BTN_BURGER').setAttribute('aria-expanded', false);}


const SECION = document.getElementById("BTN-SECION");

SECION.addEventListener("click", () => {
    window.location.href = "../incio_seccion/seccion/seccion.html";
});
