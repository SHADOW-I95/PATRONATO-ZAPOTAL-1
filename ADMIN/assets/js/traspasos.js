/* ==========================================================================
   TRASPASOS DE VIVIENDA (módulo Usuario)
   ========================================================================== */

const btnTraspasos = document.getElementById("abrir-modal-traspasos");
const modalTraspasos = document.getElementById("modal-traspasos");
if (btnTraspasos && modalTraspasos) {
    btnTraspasos.addEventListener("click", () => abrirModal(modalTraspasos));
}

const modalProcesar = document.getElementById("modal-procesar-traspaso");

document.querySelectorAll(".btn-procesar-traspaso").forEach((boton) => {
    boton.addEventListener("click", () => {
        const datos = JSON.parse(boton.dataset.traspaso);

        document.getElementById("pt-vivienda").textContent = `#${datos.numero_vivienda} (${datos.nombre_sector ?? "—"})`;
        document.getElementById("pt-actual").textContent = `${datos.nombre_actual} (DNI ${datos.dni_actual})`;
        document.getElementById("pt-motivo").textContent = datos.motivo;
        document.getElementById("pt-comprador-nombre").textContent = `${datos.nombre_comprador} ${datos.apellido_comprador}`;
        document.getElementById("pt-comprador-dni").textContent = datos.dni_comprador;
        document.getElementById("pt-comprador-telefono").textContent = datos.telefono_comprador || "—";

        document.getElementById("pt-id-solicitud").value = datos.id_solicitud;
        document.getElementById("pt-id-solicitud-rechazar").value = datos.id_solicitud;
        document.getElementById("pt-mensaje").textContent = "";
        document.getElementById("pt-mensaje").className = "mensaje-pago";

        // Reinicia los campos de "nuevo usuario" por si quedaron de una vez anterior
        document.getElementById("pt-nuevo-codigo").value = "";
        document.getElementById("pt-nuevo-fecha-nacimiento").value = "";

        // ¿Ya existe un usuario con este DNI?
        fetch("modulos/usuario/buscar_por_dni.php?dni=" + encodeURIComponent(datos.dni_comprador))
            .then((r) => r.json())
            .then((resp) => {
                document.getElementById("pt-aviso-existente").style.display = resp.existe ? "block" : "none";
                document.getElementById("pt-form-nuevo-usuario").style.display = resp.existe ? "none" : "block";
            });

        cerrarModal(modalTraspasos);
        abrirModal(modalProcesar);
    });
});

const textosErrorTraspaso = {
    sin_permiso: "No tienes permisos para hacer esto.",
    solicitud_no_valida: "Esta solicitud ya no está disponible.",
    datos_incompletos: "Faltan datos, intenta de nuevo.",
    falta_codigo_nuevo_usuario: "Escribe un código de acceso para el nuevo usuario.",
    dato_duplicado: "Ya existe un usuario con ese DNI o código.",
    error_guardando: "Ocurrió un error al guardar. Intenta de nuevo.",
};

const formConfirmarTraspaso = document.getElementById("form-confirmar-traspaso");
if (formConfirmarTraspaso) {
    formConfirmarTraspaso.addEventListener("submit", (e) => {
        e.preventDefault();

        // Copia los datos del "nuevo usuario" (si aplica) a los campos ocultos del form real
        document.getElementById("pt-codigo-hidden").value = document.getElementById("pt-nuevo-codigo").value;
        document.getElementById("pt-fecha-hidden").value = document.getElementById("pt-nuevo-fecha-nacimiento").value;

        const mensaje = document.getElementById("pt-mensaje");
        const datosForm = new FormData(formConfirmarTraspaso);
        datosForm.append("accion", "confirmar");

        fetch("modulos/usuario/procesar_traspaso.php", { method: "POST", body: datosForm })
            .then((r) => r.json())
            .then((resp) => {
                if (resp.ok) {
                    location.reload();
                } else {
                    mensaje.textContent = textosErrorTraspaso[resp.error] || "No se pudo confirmar.";
                    mensaje.className = "mensaje-pago mensaje-error-pago";
                }
            });
    });
}

const formRechazarTraspaso = document.getElementById("form-rechazar-traspaso");
if (formRechazarTraspaso) {
    formRechazarTraspaso.addEventListener("submit", (e) => {
        e.preventDefault();
        if (!confirm("¿Rechazar esta solicitud de traspaso?")) return;

        const mensaje = document.getElementById("pt-mensaje");
        const datosForm = new FormData(formRechazarTraspaso);
        datosForm.append("accion", "rechazar");

        fetch("modulos/usuario/procesar_traspaso.php", { method: "POST", body: datosForm })
            .then((r) => r.json())
            .then((resp) => {
                if (resp.ok) {
                    location.reload();
                } else {
                    mensaje.textContent = textosErrorTraspaso[resp.error] || "No se pudo rechazar.";
                    mensaje.className = "mensaje-pago mensaje-error-pago";
                }
            });
    });
}
