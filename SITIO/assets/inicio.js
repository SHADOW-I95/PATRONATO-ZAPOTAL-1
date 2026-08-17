function toggleMenu() {
            const btn     = document.getElementById('BTN_BURGER'); /* Obtiene el elemento del botón de menú por su ID */
            const menu    = document.getElementById('MENU'); /* Obtiene el elemento del menú por su ID */
            const overlay = document.getElementById('OVERLAY'); /* Obtiene el elemento del overlay por su ID */

            const estaAbierto = menu.classList.toggle('abierto'); /* Cambia la clase 'abierto' del menú para mostrar u ocultar el menú */
            btn.classList.toggle('activo', estaAbierto); /* Cambia la clase 'activo' del botón de menú según el estado del menú */
            overlay.classList.toggle('visible', estaAbierto); /* Muestra u oculta el overlay según el estado del menú */
            btn.setAttribute('aria-expanded', estaAbierto); /* Actualiza el atributo aria-expanded según el estado del menú */
        }

        function cerrarMenu() {
            document.getElementById('BTN_BURGER').classList.remove('activo'); /* Elimina la clase 'activo' del botón de menú para indicar que no está activo */
            document.getElementById('MENU').classList.remove('abierto'); /* Elimina la clase 'abierto' del menú para cerrarlo */
            document.getElementById('OVERLAY').classList.remove('visible'); /* Elimina la clase 'visible' del overlay para ocultarlo */
            document.getElementById('BTN_BURGER').setAttribute('aria-expanded', false); /* Actualiza el atributo aria-expanded a false cuando se cierra el menú */
        }



const SECION = document.getElementById("BTN-SECION"); /* Obtiene el elemento del botón de inicio de sesión por su ID */

SECION.addEventListener("click", () => { /* Agrega un evento de clic al botón de inicio de sesión */
    window.location.href = "login/login.php"; /* Redirige a la página de inicio de sesión */
});


