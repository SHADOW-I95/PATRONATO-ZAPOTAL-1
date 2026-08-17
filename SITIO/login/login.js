function mostrarPassword() {
    const password = document.getElementById("password");
    const icono = document.querySelector(".password-box i");
    const esPassword = password.type === "password";

    password.type = esPassword ? "text" : "password";

    if (icono) {
        icono.classList.toggle("fa-eye");
        icono.classList.toggle("fa-eye-slash");
    }
}

const formulario = document.getElementById("formLogin");

formulario.addEventListener("submit", (e) => {
    let valido = true;

    const nombre = document.getElementById("nombre");
    const dni = document.getElementById("dni");
    const password = document.getElementById("password");

    limpiarErrores();

    if (nombre.value.trim().length < 3) {
        mostrarError("errorNombre", "Ingrese un nombre válido");
        valido = false;
    }

    if (dni.value.trim().length < 5) {
        mostrarError("errorDni", "Ingrese un DNI válido");
        valido = false;
    }

    if (password.value.length < 2) {
        mostrarError("errorPassword", "Ingrese el código de acceso asignado");
        valido = false;
    }

    if (!valido) {
        e.preventDefault();
    }
});

function mostrarError(idSpan, mensaje) {
    const span = document.getElementById(idSpan);
    if (span) span.textContent = mensaje;
}

function limpiarErrores() {
    document.querySelectorAll(".error").forEach(span => span.textContent = "");
}

