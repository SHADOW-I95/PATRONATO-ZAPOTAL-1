/* ==========================================================================
   CONFIGURACION.JS
   Pestañas (Catálogos / Mi cuenta), alta y edición de catálogos por AJAX,
   y guardado del formulario "Mi cuenta".
   ========================================================================== */

/* ==================== Pestañas ==================== */
document.querySelectorAll(".tab-btn").forEach((boton) => {
    boton.addEventListener("click", () => {
        document.querySelectorAll(".tab-btn").forEach((b) => b.classList.remove("activo"));
        document.querySelectorAll(".tab-panel").forEach((p) => p.classList.remove("activo"));

        boton.classList.add("activo");
        document.getElementById(boton.dataset.tab)?.classList.add("activo");
    });
});

/* ==================== Mensajes de error compartidos ==================== */
const textoErrorCatalogo = {
    tipo_invalido: "Tipo de catálogo no reconocido.",
    nombre_vacio: "Escribe un nombre antes de guardar.",
    id_invalido: "No se identificó el elemento a editar.",
    sin_permiso: "No tienes permisos para hacer esto.",
    error_guardando: "Ocurrió un error al guardar. Intenta de nuevo.",
};

/* ==================== Formularios "Agregar" de cada catálogo ==================== */
document.querySelectorAll(".form-catalogo").forEach((form) => {
    form.addEventListener("submit", (e) => {
        e.preventDefault();

        fetch(form.action, {
            method: "POST",
            body: new FormData(form),
        })
            .then((r) => r.json())
            .then((datos) => {
                if (datos.ok) {
                    window.location.reload();
                } else {
                    alert(textoErrorCatalogo[datos.error] || "No se pudo guardar.");
                }
            });
    });
});

/* ==================== Botones "Editar" de cada fila de catálogo ====================
   Usa prompt() en vez de un modal aparte: es un solo campo de texto, y así
   no hace falta un modal ni un archivo *_editar.php por cada catálogo. */
document.querySelectorAll(".btn-editar-catalogo").forEach((boton) => {
    boton.addEventListener("click", () => {
        const tipo = boton.dataset.tipo;
        const id = boton.dataset.id;
        const nombreActual = boton.dataset.nombre;

        const nuevoNombre = prompt("Nuevo nombre:", nombreActual);
        if (nuevoNombre === null) return; // canceló
        if (nuevoNombre.trim() === "") return;

        const datos = new FormData();
        datos.append("tipo", tipo);
        datos.append("id", id);
        datos.append("nombre", nuevoNombre.trim());

        fetch("modulos/configuracion/catalogo_actualizar.php", {
            method: "POST",
            body: datos,
        })
            .then((r) => r.json())
            .then((resp) => {
                if (resp.ok) {
                    window.location.reload();
                } else {
                    alert(textoErrorCatalogo[resp.error] || "No se pudo guardar.");
                }
            });
    });
});

/* ==================== Formulario "Mi cuenta" ==================== */
const textoErrorCuenta = {
    codigo_vacio: "El código de acceso no puede quedar vacío.",
    codigo_duplicado: "Ese código de acceso ya lo está usando otro empleado.",
    sesion_invalida: "Tu sesión expiró. Vuelve a iniciar sesión.",
    error_guardando: "Ocurrió un error al guardar. Intenta de nuevo.",
};

const formMiCuenta = document.getElementById("form_mi_cuenta");

if (formMiCuenta) {
    formMiCuenta.addEventListener("submit", (e) => {
        e.preventDefault();

        fetch(formMiCuenta.action, {
            method: "POST",
            body: new FormData(formMiCuenta),
        })
            .then((r) => r.json())
            .then((datos) => {
                if (datos.ok) {
                    alert("Tus datos se guardaron correctamente.");
                } else {
                    alert(textoErrorCuenta[datos.error] || "No se pudo guardar.");
                }
            });
    });
}