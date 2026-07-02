const modal = document.getElementById("modal");
const abrir = document.getElementById("abrir-modal");
const cerrar = document.getElementById("cerrar-modal");

abrir.addEventListener("click", () => {
    modal.style.display = "flex";
});

cerrar.addEventListener("click", () =>{
    modal.style.display = "none";
});

window.addEventListener("click", (e) => {
    if (e.target === modal) {
        modal.style.diplay = "none";
    }
});