function mostrarPassword() {
    let password = document.getElementById("password");
    password.type = (password.type === "password") ? "text" : "password";
}

const INGRESAR = document.getElementById("Ingresar");
INGRESAR.addEventListener("click", (e) => {
    // El botón envía el formulario, no redirige manualmente
    // Si quieres redirigir después del login, hazlo en PHP
});
