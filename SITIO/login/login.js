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

    if (!valido) {
        e.preventDefault(); // evita envío si hay errores
    }
});

function mostrarError(idSpan, mensaje) {
    const span = document.getElementById(idSpan); // obtiene el span de error
    if (span) span.textContent = mensaje; // coloca el mensaje en pantalla
}

function limpiarErrores() {
    document.querySelectorAll(".error").forEach(span => span.textContent = "");
}

