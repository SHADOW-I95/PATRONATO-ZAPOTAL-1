// Función para mostrar u ocultar la contraseña
function mostrarPassword() {
    const password = document.getElementById("password"); // Campo de contraseña
    const icono = document.querySelector(".password-box i"); // Ícono dentro del contenedor
    const esPassword = password.type === "password"; // Verifica si el campo está en modo "password"

    // Cambia el tipo de input entre "text" y "password"
    password.type = esPassword ? "text" : "password";

    // Cambia el ícono (ojo abierto / ojo cerrado)
    if (icono) {
        icono.classList.toggle("fa-eye");
        icono.classList.toggle("fa-eye-slash");
    }
}

// Obtiene el formulario de login
const formulario = document.getElementById("formLogin");

// Agrega evento al enviar el formulario
formulario.addEventListener("submit", (e) => {
    let valido = true; // Bandera para validar

    // Obtiene los campos del formulario
    const nombre = document.getElementById("nombre");
    const dni = document.getElementById("dni");
    const password = document.getElementById("password"); // antes decía "codigo" (bug corregido)

    // Limpia errores previos
    limpiarErrores();

    // Validación del nombre
    if (nombre.value.trim().length < 3) {
        mostrarError("errorNombre", "Ingrese un nombre válido");
        valido = false;
    }

    // Validación del DNI
    if (dni.value.trim().length < 5) {
        mostrarError("errorDni", "Ingrese un DNI válido");
        valido = false;
    }

    // Validación de la contraseña
    if (password.value.length < 4) {
        mostrarError("errorPassword", "Ingrese la contraseña asignado");
        valido = false;
    }

    // Si hay errores, evita que se envíe el formulario
    if (!valido) {
        e.preventDefault();
    }
});

// Función para mostrar un mensaje de error en un <span>
function mostrarError(idSpan, mensaje) {
    const span = document.getElementById(idSpan);
    if (span) span.textContent = mensaje;
}

// Función para limpiar todos los mensajes de error
function limpiarErrores() {
    document.querySelectorAll(".error").forEach(span => span.textContent = "");
}
