// Todo este script solo debe correr cuando el módulo de USUARIO está cargado
// en la página (form_usuario es único de usuario2.php). Sin este guard, al
// estar en otro módulo que también use clases .btn-ver/.btn-editar (como
// reportes), este script se dispararía sobre esos botones también.
if (document.getElementById("form_usuario")) {

// ==================== Botón "Ver" del usuario ====================
const modalVer = document.getElementById("modal_ver");
const contenidoVer = document.getElementById("contenido_ver");

// Por cada botón "Ver" de la tabla, trae el HTML de ver.php y lo muestra en el modal
document.querySelectorAll(".btn-ver").forEach((boton) => {
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

// ==================== Botón "Editar" del usuario ====================
const modalEditar = document.getElementById("modal_editar");
const contenidoEditar = document.getElementById("contenido_editar");

// Por cada botón "Editar" de la tabla, trae el formulario ya lleno desde editar.php
document.querySelectorAll(".btn-editar").forEach((boton) => {
    boton.addEventListener("click", () => {
        const idUsuario = boton.getAttribute("data-id");

        fetch("modulos/usuario/editar.php?id=" + idUsuario)
            .then((respuesta) => respuesta.text())
            .then((html) => {
                contenidoEditar.innerHTML = html;
                modalEditar.style.display = "flex";
                // El formulario recién llegó al DOM: hay que activar sus botones
                inicializarFormularioEditar();
            });
    });
});

// Prepara "Agregar vivienda" y "Quitar vivienda" del formulario que se acaba de cargar.
// Se llama cada vez que se abre el modal de editar, porque el formulario es distinto
// para cada usuario (llega nuevo por fetch).
function inicializarFormularioEditar() {
    const contenedor = document.getElementById("editar_contenedor_viviendas");
    const plantilla = document.getElementById("plantilla-vivienda-editar");
    const btnAgregar = document.getElementById("editar_agregar_vivienda");
    const inputEliminar = document.getElementById("editar_viviendas_eliminar");

    if (!contenedor || !plantilla || !btnAgregar || !inputEliminar) return;

    let indice = contenedor.children.length; // sigue el índice después de las viviendas que ya vinieron cargadas
    const idsEliminados = [];

    // Le pone el evento de "Quitar" a una fila de vivienda (ya sea cargada o recién agregada)
    function activarQuitar(fila) {
        const btnQuitar = fila.querySelector(".btn-quitar-vivienda");
        const inputId = fila.querySelector('input[name$="[id]"]');

        btnQuitar.addEventListener("click", () => {
            // Si tenía id_vivienda, hay que decirle a actualizar.php que la borre
            if (inputId && inputId.value) {
                idsEliminados.push(inputId.value);
                inputEliminar.value = idsEliminados.join(",");
            }
            // Si no tenía id (era nueva), con solo quitarla del formulario basta
            fila.remove();
        });
    }

    // Activa "Quitar" en cada una de las viviendas que ya vinieron cargadas desde el servidor
    contenedor.querySelectorAll(".vivienda-fila").forEach(activarQuitar);

    // Al presionar "Agregar vivienda", clona la <template> y le pone el índice que sigue
    btnAgregar.addEventListener("click", () => {
        const fila = plantilla.content.cloneNode(true).firstElementChild;
        fila.innerHTML = fila.innerHTML.replaceAll("__INDICE__", indice);
        contenedor.appendChild(fila);
        activarQuitar(fila);
        indice++;
    });
}

} // fin del guard "if (document.getElementById('form_usuario'))"