/* ==========================================================================
   NOTIFICACIONES DE PAGOS (módulo Agua)
   ========================================================================== */

const btnNotificaciones = document.getElementById("abrir-modal-notificaciones");
const modalNotificaciones = document.getElementById("modal-notificaciones");
if (btnNotificaciones && modalNotificaciones) {
    btnNotificaciones.addEventListener("click", () => abrirModal(modalNotificaciones));
}

const modalRevisar = document.getElementById("modal-revisar-solicitud");

document.querySelectorAll(".btn-revisar-solicitud").forEach((boton) => {
    boton.addEventListener("click", () => {
        const datos = JSON.parse(boton.dataset.solicitud);

        document.getElementById("rev-imagen").src = datos.ruta_comprobante;
        document.getElementById("rev-imagen-link").href = datos.ruta_comprobante;
        document.getElementById("rev-vivienda").textContent = `#${datos.numero_vivienda} (${datos.nombre_sector ?? "—"})`;
        document.getElementById("rev-usuario").textContent = datos.nombre_usuario;
        document.getElementById("rev-dni").textContent = datos.dni;
        document.getElementById("rev-telefono").textContent = datos.telefono ?? "—";
        document.getElementById("rev-codigo").textContent = datos.codigo_referencia;
        document.getElementById("rev-meses").textContent = datos.meses_texto;
        document.getElementById("rev-monto").textContent = "L" + parseFloat(datos.monto_declarado).toFixed(2);
        document.getElementById("rev-fecha").textContent = datos.fecha_solicitud;

        document.getElementById("rev-id-solicitud-verificar").value = datos.id_solicitud;
        document.getElementById("rev-id-solicitud-rechazar").value = datos.id_solicitud;

        const inputMeses = document.getElementById("rev-meses-confirmados");
        inputMeses.value = datos.cantidad_meses;
        inputMeses.max = datos.cantidad_meses;

        document.getElementById("rev-mensaje").textContent = "";
        document.getElementById("rev-mensaje").className = "mensaje-pago";

        cerrarModal(modalNotificaciones);
        abrirModal(modalRevisar);
    });
});

function enviarRevision(datosForm, mensajeEl) {
    return fetch("modulos/agua/revisar_solicitud.php", {
        method: "POST",
        body: datosForm,
    }).then((r) => r.json());
}

const textosErrorRevision = {
    sin_permiso: "No tienes permisos para hacer esto.",
    solicitud_no_valida: "Esta solicitud ya no está disponible.",
    datos_incompletos: "Faltan datos, intenta de nuevo.",
    error_guardando: "Ocurrió un error al guardar. Intenta de nuevo.",
    autocobro_bloqueado: "Esta vivienda pertenece a un empleado del patronato. Solo el Administrador puede procesar este pago.",
};

const formVerificar = document.getElementById("form-verificar-solicitud");
if (formVerificar) {
    formVerificar.addEventListener("submit", (e) => {
        e.preventDefault();
        const mensaje = document.getElementById("rev-mensaje");
        const datosForm = new FormData(formVerificar);
        datosForm.append("accion", "verificar");

        enviarRevision(datosForm).then((resp) => {
            if (resp.ok) {
                location.reload();
            } else {
                mensaje.textContent = textosErrorRevision[resp.error] || "No se pudo verificar.";
                mensaje.className = "mensaje-pago mensaje-error-pago";
            }
        });
    });
}

const formRechazar = document.getElementById("form-rechazar-solicitud");
if (formRechazar) {
    formRechazar.addEventListener("submit", (e) => {
        e.preventDefault();
        if (!confirm("¿Rechazar esta solicitud de pago?")) return;

        const mensaje = document.getElementById("rev-mensaje");
        const datosForm = new FormData(formRechazar);
        datosForm.append("accion", "rechazar");

        enviarRevision(datosForm).then((resp) => {
            if (resp.ok) {
                location.reload();
            } else {
                mensaje.textContent = textosErrorRevision[resp.error] || "No se pudo rechazar.";
                mensaje.className = "mensaje-pago mensaje-error-pago";
            }
        });
    });
}