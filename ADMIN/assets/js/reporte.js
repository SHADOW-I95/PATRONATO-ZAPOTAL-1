// Todo este script solo debe correr cuando el módulo de REPORTES está
// cargado en la página (form_reporte es único de reportes.php). Ver la
// nota equivalente en usuario.js.
if (document.getElementById("form_reporte")) {

// ==================== Botón "Ver" del reporte ====================
const modalVerReporte = document.getElementById("modal_ver");
const contenidoVerReporte = document.getElementById("contenido_ver");

// Por cada botón "Ver" de la tabla de reportes, trae el HTML de ver.php y lo muestra en el modal
document.querySelectorAll(".btn-ver").forEach((boton) => {
    boton.addEventListener("click", () => {
        const idReporte = boton.getAttribute("data-id");

        fetch("modulos/reportes/ver.php?id=" + idReporte)
            .then((respuesta) => respuesta.text())
            .then((html) => {
                contenidoVerReporte.innerHTML = html;
                modalVerReporte.style.display = "flex";
            });
    });
});

// ==================== Botón "Editar" del reporte ====================
const modalEditarReporte = document.getElementById("modal_editar");
const contenidoEditarReporte = document.getElementById("contenido_editar");

// Por cada botón "Editar" de la tabla de reportes, trae el formulario ya lleno desde editar.php
document.querySelectorAll(".btn-editar").forEach((boton) => {
    boton.addEventListener("click", () => {
        const idReporte = boton.getAttribute("data-id");

        fetch("modulos/reportes/editar.php?id=" + idReporte)
            .then((respuesta) => respuesta.text())
            .then((html) => {
                contenidoEditarReporte.innerHTML = html;
                modalEditarReporte.style.display = "flex";
            });
    });
});

} // fin del guard "if (document.getElementById('form_reporte'))"