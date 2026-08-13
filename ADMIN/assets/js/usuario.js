/* ==========================================================================
   USUARIO.JS
   Todo el JS de usuario.php + editar.php, dividido en partes:
     0. Buscador de la tabla
     1. Modal "Nuevo usuario"
     2. Modal "Ver"
     3. Modal "Editar"
   Usa abrirModal()/cerrarModal() de modal.js — no las vuelve a definir.
   ========================================================================== */


/* ==========================================================================
   0. BUSCADOR DE LA TABLA
   ========================================================================== */

// Filtra las filas de la tabla en vivo, según lo que se escriba en el input .buscar.
// No hace falta recargar la página ni pedir nada al servidor.
const inputBuscarUsuario = document.querySelector(".buscar");

if (inputBuscarUsuario) {
    inputBuscarUsuario.addEventListener("input", () => {
        const texto = inputBuscarUsuario.value.trim().toLowerCase();

        document.querySelectorAll(".tabla_datos tbody tr").forEach((fila) => {
            const coincide = fila.textContent.toLowerCase().includes(texto);
            fila.style.display = coincide ? "" : "none";
        });
    });
}


/* ==========================================================================
   1. MODAL "NUEVO USUARIO"
   ========================================================================== */

const formularioNuevo = document.getElementById("form_usuario");
const contenedorNuevo = document.getElementById("contenedor_viviendas");
const btnAgregarNuevo = document.getElementById("agregar_vivienda");
const btnCancelarNuevo = document.getElementById("cancelar");
const plantillaNuevo = document.getElementById("plantilla-vivienda-nuevo");

// Copia del HTML original del contenedor de viviendas, para poder restaurarlo al cancelar
const viviendaOriginalNuevo = contenedorNuevo.innerHTML;

// Arranca en 1 porque la vivienda 0 ya viene escrita en el HTML de usuario.php
let indiceNuevo = 1;

// --- Agregar vivienda ---
// Clona la <template> (ya tiene las opciones de sector/servicio/estado renderizadas por PHP)
// y le pone el índice y el número de vivienda que siguen.
btnAgregarNuevo.addEventListener("click", () => {
    const fila = plantillaNuevo.content.cloneNode(true).firstElementChild;

    fila.innerHTML = fila.innerHTML
        .replaceAll("__INDICE__", indiceNuevo)
        .replaceAll("__NUMERO__", indiceNuevo + 1);

    contenedorNuevo.appendChild(fila);
    indiceNuevo++;
});

// Si venimos del acceso directo "Nuevo usuario" del dashboard, abrimos el modal de una vez
if (window.location.hash === "#nuevo") {
    abrirModal(modal);
}

// --- Cancelar ---
// El cierre del modal ya lo hace data-cerrar-modal (modal.js); aquí solo se limpia el formulario
function reiniciarFormularioNuevo() {
    formularioNuevo.reset();
    contenedorNuevo.innerHTML = viviendaOriginalNuevo;
    indiceNuevo = 1;
}

btnCancelarNuevo.addEventListener("click", () => {
    reiniciarFormularioNuevo();
});

// --- Guardar ---
// Se envía por fetch en vez de navegar a otra página. Si hay un error (DNI repetido,
// vivienda duplicada, etc.), se avisa con un alert y el modal se queda abierto tal
// como estaba, sin perder lo que ya se escribió.
const textoDeCadaError = {
    dni_duplicado: "Ya existe un usuario registrado con ese número de identidad.",
    codigo_duplicado: "Ese código de acceso ya está en uso por otro usuario.",
    vivienda_duplicada: "Ya existe una vivienda con ese número en ese sector.",
    dato_duplicado: "Alguno de los datos ingresados ya existe.",
    error_guardando: "Ocurrió un error al guardar. Intenta de nuevo."
};

formularioNuevo.addEventListener("submit", (e) => {
    e.preventDefault();

    fetch(formularioNuevo.action, {
        method: "POST",
        body: new FormData(formularioNuevo)
    })
        .then((respuesta) => respuesta.json())
        .then((datos) => {
            if (datos.ok) {
                alert("Usuario guardado correctamente.");
                window.location.reload(); // para ver al usuario nuevo en la tabla
            } else {
                alert(textoDeCadaError[datos.error] || "No se pudo guardar. Revisa los datos.");
                // el modal se queda abierto y con los datos que ya se habían escrito
            }
        });
});


/* ==========================================================================
   2. MODAL "VER"
   ========================================================================== */

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
                abrirModal(modalVer);
            });
    });
});


/* ==========================================================================
   3. MODAL "EDITAR"
   ========================================================================== */

const modalEditar = document.getElementById("modal_editar");
const contenidoEditar = document.getElementById("contenido_editar");

// Textos de cada posible error que puede devolver actualizar.php
const textoDeCadaErrorEditar = {
    dni_duplicado: "Ya existe otro usuario registrado con ese número de identidad.",
    codigo_duplicado: "Ese código de acceso ya está en uso por otro usuario.",
    vivienda_duplicada: "Ya existe otra vivienda con ese número en ese sector.",
    dato_duplicado: "Alguno de los datos ingresados ya existe.",
    error_guardando: "Ocurrió un error al guardar. Intenta de nuevo.",
    usuario_invalido: "No se identificó al usuario a editar."
};

// Por cada botón "Editar" de la tabla, trae el formulario ya lleno desde editar.php
document.querySelectorAll(".btn-editar").forEach((boton) => {
    boton.addEventListener("click", () => {
        const idUsuario = boton.getAttribute("data-id");

        fetch("modulos/usuario/editar.php?id=" + idUsuario)
            .then((respuesta) => respuesta.text())
            .then((html) => {
                contenidoEditar.innerHTML = html;
                abrirModal(modalEditar);
                // El formulario recién llegó al DOM: hay que activar sus botones
                inicializarFormularioEditar();
            });
    });
});

// Prepara "Agregar vivienda", "Quitar vivienda" y el guardado del formulario que se
// acaba de cargar. Se llama cada vez que se abre el modal, porque el formulario es
// distinto para cada usuario (llega nuevo por fetch, no existe hasta ese momento).
function inicializarFormularioEditar() {
    const contenedor = document.getElementById("editar_contenedor_viviendas");
    const plantilla = document.getElementById("plantilla-vivienda-editar");
    const btnAgregar = document.getElementById("editar_agregar_vivienda");
    const inputEliminar = document.getElementById("editar_viviendas_eliminar");
    const formEditar = document.getElementById("form_editar");

    if (!contenedor || !plantilla || !btnAgregar || !inputEliminar) return;

    let indice = contenedor.children.length; // sigue el índice después de las viviendas que ya vinieron cargadas
    const idsEliminados = [];

    // --- Quitar vivienda ---
    // Le pone el evento de "Quitar" a una fila (ya sea cargada desde el servidor o recién agregada)
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

    // --- Agregar vivienda ---
    // Clona la <template> (ya tiene las opciones de sector/servicio/estado) y le pone el índice que sigue
    btnAgregar.addEventListener("click", () => {
        const fila = plantilla.content.cloneNode(true).firstElementChild;
        fila.innerHTML = fila.innerHTML.replaceAll("__INDICE__", indice);
        contenedor.appendChild(fila);
        activarQuitar(fila);
        indice++;
    });

    // --- Guardar cambios ---
    // Igual que en "Nuevo usuario": se envía por fetch, y si hay error se avisa con
    // un alert sin cerrar el modal ni perder lo que ya se había editado.
    if (formEditar) {
        formEditar.addEventListener("submit", (e) => {
            e.preventDefault();

            fetch(formEditar.action, {
                method: "POST",
                body: new FormData(formEditar)
            })
                .then((respuesta) => respuesta.json())
                .then((datos) => {
                    if (datos.ok) {
                        alert("Usuario actualizado correctamente.");
                        window.location.reload(); // para ver los cambios en la tabla
                    } else {
                        alert(textoDeCadaErrorEditar[datos.error] || "No se pudo guardar. Revisa los datos.");
                    }
                });
        });
    }
}