/* ==========================================================================
   EMPLEADOS.JS
   Mismo patrón que usuario.js, pero más simple: los empleados no tienen
   viviendas, así que no hace falta ninguna <template> ni filas dinámicas.
   Usa abrirModal()/cerrarModal() de modal.js — no las vuelve a definir.
   ========================================================================== */

/* ==================== Buscador de la tabla ==================== */
const inputBuscarEmpleado = document.querySelector(".buscar");

if (inputBuscarEmpleado) {
    inputBuscarEmpleado.addEventListener("input", () => {
        const texto = inputBuscarEmpleado.value.trim().toLowerCase();

        document.querySelectorAll(".tabla_datos tbody tr").forEach((fila) => {
            fila.style.display = fila.textContent.toLowerCase().includes(texto) ? "" : "none";
        });
    });
}

/* ==================== Modal "Nuevo empleado" ==================== */
const formularioNuevoEmpleado = document.getElementById("form_empleado");

const textoDeCadaErrorEmpleado = {
    dni_duplicado: "Ya existe un empleado registrado con ese número de identidad.",
    codigo_duplicado: "Ese código de acceso ya está en uso por otro empleado.",
    dato_duplicado: "Alguno de los datos ingresados ya existe.",
    error_guardando: "Ocurrió un error al guardar. Intenta de nuevo."
};

formularioNuevoEmpleado.addEventListener("submit", (e) => {
    e.preventDefault();

    fetch(formularioNuevoEmpleado.action, {
        method: "POST",
        body: new FormData(formularioNuevoEmpleado)
    })
        .then((respuesta) => respuesta.json())
        .then((datos) => {
            if (datos.ok) {
                alert("Empleado guardado correctamente.");
                window.location.reload();
            } else {
                alert(textoDeCadaErrorEmpleado[datos.error] || "No se pudo guardar. Revisa los datos.");
            }
        });
});

/* ==================== Modal "Ver" ==================== */
const modalVerEmpleado = document.getElementById("modal_ver");
const contenidoVerEmpleado = document.getElementById("contenido_ver");

document.querySelectorAll(".btn-ver").forEach((boton) => {
    boton.addEventListener("click", () => {
        const idEmpleado = boton.getAttribute("data-id");

        fetch("modulos/empleados/ver.php?id=" + idEmpleado)
            .then((respuesta) => respuesta.text())
            .then((html) => {
                contenidoVerEmpleado.innerHTML = html;
                abrirModal(modalVerEmpleado);
            });
    });
});

/* ==================== Modal "Editar" ==================== */
const modalEditarEmpleado = document.getElementById("modal_editar");
const contenidoEditarEmpleado = document.getElementById("contenido_editar");

document.querySelectorAll(".btn-editar").forEach((boton) => {
    boton.addEventListener("click", () => {
        const idEmpleado = boton.getAttribute("data-id");

        fetch("modulos/empleados/editar.php?id=" + idEmpleado)
            .then((respuesta) => respuesta.text())
            .then((html) => {
                contenidoEditarEmpleado.innerHTML = html;
                abrirModal(modalEditarEmpleado);

                const formEditar = document.getElementById("form_editar_empleado");
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
                                    alert("Empleado actualizado correctamente.");
                                    window.location.reload();
                                } else {
                                    alert(textoDeCadaErrorEmpleado[datos.error] || "No se pudo guardar. Revisa los datos.");
                                }
                            });
                    });
                }
            });
    });
});