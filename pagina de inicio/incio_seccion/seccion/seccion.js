function mostrarPassword() {
    let password = document.getElementById("password");
    password.type = (password.type === "password") ? "text" : "password";
}

const formulario = document.getElementById("formLogin");

formulario.addEventListener("submit", (e) => {
    let valido = true;

    const nombre = document.getElementById("nombre");
    const dni = document.getElementById("dni");
    const password = document.getElementById("codigo");

    limpiarErrores();

    if (nombre.value.trim().length < 3) {
        mostrarError("errorNombre", "Ingrese un nombre válido");
        valido = false;
    }

    if (dni.value.trim().length < 5) {
        mostrarError("errorDni", "Ingrese un DNI válido");
        valido = false;
    }

    if (password.value.length < 4) {
        mostrarError("errorPassword", "Ingrese el código asignado");
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