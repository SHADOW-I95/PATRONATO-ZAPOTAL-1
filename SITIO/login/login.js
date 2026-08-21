function mostrarPassword() {
    const password = document.getElementById("password"); // obtiene el campo de contraseña
    const icono = document.querySelector(".password-box i"); // ícono del ojo
    const esPassword = password.type === "password"; // verifica si está en modo oculto

    password.type = esPassword ? "text" : "password"; // alterna entre mostrar/ocultar

    if (icono) {
        icono.classList.toggle("fa-eye"); // cambia a ícono de ojo abierto
        icono.classList.toggle("fa-eye-slash"); // cambia a ícono de ojo cerrado
    }
}

const formulario = document.getElementById("formLogin"); // obtiene el formulario

formulario.addEventListener("submit", (e) => {
    e.preventDefault(); // siempre evitamos el envío normal: lo hacemos por fetch

    let valido = true; // bandera de validación

    const nombre = document.getElementById("nombre"); // campo nombre
    const dni = document.getElementById("dni"); // campo DNI
    const password = document.getElementById("password"); // campo contraseña

    limpiarErrores(); // limpia errores previos

    if (nombre.value.trim().length < 3) {
        mostrarError("errorNombre", "Ingrese un nombre válido"); // error en nombre
        valido = false;
    }

    if (dni.value.trim().length < 5) {
        mostrarError("errorDni", "Ingrese un DNI válido"); // error en DNI
        valido = false;
    }

    if (password.value.length < 2) {
        mostrarError("errorPassword", "Ingrese el código de acceso asignado"); // error en contraseña
        valido = false;
    }

    if (!valido) return;

    // Envío por fetch: así, si el login falla, el mensaje del servidor
    // aparece directo en la tarjeta de login en vez de dejar al usuario
    // en una página en blanco con texto sin estilo.
    const boton = document.getElementById("Ingresar");
    boton.disabled = true;
    boton.textContent = "Ingresando...";

    fetch(formulario.action, {
        method: "POST",
        body: new FormData(formulario),
    })
        .then((respuesta) => {
            if (respuesta.redirected) {
                // Login correcto: el servidor redirige a perfil.php o al ADMIN
                window.location.href = respuesta.url;
                return null;
            }
            return respuesta.text();
        })
        .then((texto) => {
            if (texto !== null) {
                mostrarError("errorServidor", texto);
                boton.disabled = false;
                boton.textContent = "Ingresar";
            }
        })
        .catch(() => {
            mostrarError("errorServidor", "Error de conexión. Intenta de nuevo.");
            boton.disabled = false;
            boton.textContent = "Ingresar";
        });
});

function mostrarError(idSpan, mensaje) {
    const span = document.getElementById(idSpan); // obtiene el span de error
    if (span) span.textContent = mensaje; // coloca el mensaje en pantalla
}

function limpiarErrores() {
    document.querySelectorAll(".error").forEach(span => span.textContent = "");
}