const modal = document.getElementById("modal");
const cerrar = document.getElementById("cerrar-modal");
const abrir = document.getElementById("abrir-modal");


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