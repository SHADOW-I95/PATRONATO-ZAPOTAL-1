// ==================== Cerrar modales (genérico) ====================
// Cualquier elemento con data-cerrar-modal cierra el modal donde esté dentro,
// sin importar si ese elemento ya existía en la página o se cargó después
// (por ejemplo, el contenido de "Editar" que llega por fetch).
document.addEventListener("click", (e) => {
    // Busca hacia arriba desde lo que se clickeó, por si el clic fue en un ícono dentro del botón
    const boton = e.target.closest("[data-cerrar-modal]");
    if (!boton) return;

    // Encuentra el modal que envuelve a ese botón y lo oculta
    const modalCercano = boton.closest(".modal");
    if (modalCercano) {
        modalCercano.style.display = "none";
    }
});

// Cerrar al hacer clic en el fondo oscuro de cualquier modal (fuera del modal-contenido)
document.addEventListener("click", (e) => {
    if (e.target.classList.contains("modal")) {
        e.target.style.display = "none";
    }
});

// ==================== Modal "Nuevo usuario": abrir ====================
// (el mismo id="modal" / id="abrir-modal" se reutiliza en otros módulos,
// por eso se valida que existan antes de usarlos)
const modal = document.getElementById("modal");
const abrir = document.getElementById("abrir-modal");

if (modal && abrir) {
    abrir.addEventListener("click", () => {
        modal.style.display = "flex";
    });
}