const modal = document.getElementById("modal");
const cerrar = document.getElementById("cerrar-modal");
const abrir = document.getElementById("abrir-modal");

abrir.addEventListener("click", () => {
  modal.style.display = "flex";
});

window.addEventListener("click", (e) => {
  if (e.target === modal) {
    modal.style.diplay = "none";
  }
});


document.addEventListener("click", (e) => {
    const boton = e.target.closest("[data-cerrar-modal]");
    if (!boton) return;
 
    const modalCercano = boton.closest(".modal");
    if (modalCercano) {
        modalCercano.style.display = "none";
    }
});
 
// ==================== Botón "Ver" del usuario ====================
const modalVer = document.getElementById("modal_ver");
const cerrarVer = document.getElementById("cerrar_ver");
const contenidoVer = document.getElementById("contenido_ver");

document.querySelectorAll(".btn_ver").forEach((boton) => {
  boton.addEventListener("click", () => {
    const idUsuario = boton.getAttribute("data-id");

    fetch("modulos/usuario/ver.php?id=" + idUsuario)
      .then((respuesta) => respuesta.text())
      .then((html) => {
        contenidoVer.innerHTML = html;
        modalVer.style.display = "flex";
      });
  });
});

cerrarVer.addEventListener("click", () => {
  modalVer.style.display = "none";
});

window.addEventListener("click", (e) => {
  if (e.target === modalVer) {
    modalVer.style.display = "none";
  }
});