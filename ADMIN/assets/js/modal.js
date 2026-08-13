/* ==========================================================================
   MODAL.JS — Genérico, para cualquier módulo.
   No pongas aquí nada específico de un módulo (eso va en usuario.js, agua.js, etc.)
   ========================================================================== */

// abrirModal/cerrarModal quedan como funciones globales: los demás archivos
// (usuario.js, agua.js...) las usan directamente, sin tener que redefinirlas.
function abrirModal(modal) {
    if (modal) modal.style.display = "flex";
}

function cerrarModal(modal) {
    if (modal) modal.style.display = "none";
}

// ==================== Abrir el modal principal de cada módulo ====================
// El botón "abrir-modal" abre el modal "modal" — este mismo patrón de ids se
// reutiliza en varios módulos (usuario: "Nuevo usuario", agua: "Registrar pago"),
// por eso se valida que existan antes de usarlos.
const modal = document.getElementById("modal");
const abrir = document.getElementById("abrir-modal");

if (modal && abrir) {
    abrir.addEventListener("click", () => abrirModal(modal));
}

// ==================== Cerrar modales con data-cerrar-modal ====================
// Cualquier elemento con data-cerrar-modal cierra el modal donde esté dentro,
// sin importar si ya existía en la página o se cargó después (por ejemplo,
// el contenido de "Editar" que llega por fetch). Se usa delegación de eventos
// por eso mismo: un solo listener sirve para todo lo que se cargue después.
document.addEventListener("click", (e) => {
    const boton = e.target.closest("[data-cerrar-modal]");
    if (!boton) return;

    cerrarModal(boton.closest(".modal"));
});

// ==================== Cerrar al hacer clic fuera del modal ====================
// Un clic directo sobre el fondo oscuro (no sobre .modal-contenido) cierra el modal.
document.addEventListener("click", (e) => {
    if (e.target.classList.contains("modal")) {
        cerrarModal(e.target);
    }
});