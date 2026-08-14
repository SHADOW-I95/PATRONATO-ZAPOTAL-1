/* ==========================================================================
   AGUA.JS
   Todo el JS del módulo de agua: buscador + filtro de estado, reabrir el
   modal de pago, calcular totales por vivienda, y el modal de detalle.
   Usa abrirModal()/cerrarModal() de modal.js — no las vuelve a definir.
   ========================================================================== */

// ==================== Buscador + filtro de estado ====================
// El buscador de texto funciona en ambas tablas ("Estado de viviendas" y
// "Pagos registrados"). El filtro de estado (Pagado/Pendiente/Mora) solo
// afecta a las filas que tengan data-estado, es decir, la de "Estado de
// viviendas" — las de "Pagos registrados" no tienen ese atributo, así que
// nunca las oculta.
const inputBuscarAgua = document.querySelector(".buscar");
const selectFiltroEstado = document.getElementById("filtro-estado");

function filtrarTablasAgua() {
    const texto = inputBuscarAgua ? inputBuscarAgua.value.trim().toLowerCase() : "";
    const estado = selectFiltroEstado ? selectFiltroEstado.value : "";

    document.querySelectorAll(".tabla_datos tbody tr").forEach((fila) => {
        const coincideTexto = fila.textContent.toLowerCase().includes(texto);

        const estadoFila = fila.getAttribute("data-estado");
        const coincideEstado = !estado || estadoFila === null || estadoFila === estado;

        fila.style.display = (coincideTexto && coincideEstado) ? "" : "none";
    });
}

if (inputBuscarAgua) {
    inputBuscarAgua.addEventListener("input", filtrarTablasAgua);
}
if (selectFiltroEstado) {
    selectFiltroEstado.addEventListener("change", filtrarTablasAgua);
}

// Si venimos de "Buscar viviendas" (la URL trae id_usuario), o del acceso directo
// "Registrar pago" del dashboard (#registrar), reabrimos el modal de pago
const modalPago = document.getElementById("modal");

if (new URLSearchParams(window.location.search).get("id_usuario") || window.location.hash === "#registrar") {
    abrirModal(modalPago);
}

// ==================== Calcular el total de cada vivienda ====================
// Total = meses a pagar x monto por mes. Se recalcula al cargar y cada vez que
// cambian esos dos campos.
document.querySelectorAll(".tarjeta-vivienda").forEach((tarjeta) => {
    const meses = tarjeta.querySelector(".input-meses");
    const monto = tarjeta.querySelector(".input-monto-mensual");
    const total = tarjeta.querySelector(".input-total");

    function recalcular() {
        total.value = ((parseFloat(meses.value) || 0) * (parseFloat(monto.value) || 0)).toFixed(2);
    }

    meses.addEventListener("input", recalcular);
    monto.addEventListener("input", recalcular);
    recalcular();
});

// ==================== Modal de detalle de un pago ====================
const modalDetalle = document.getElementById("modal-detalle");

document.querySelectorAll(".btn-detalle").forEach((btn) => {
    btn.addEventListener("click", () => {
        const nombresMes = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio",
            "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
        ];
        const detalle = JSON.parse(btn.getAttribute("data-detalle") || "[]");

        document.getElementById("detalle-recibo").textContent = "#" + btn.getAttribute("data-recibo");

        const cuerpo = document.getElementById("detalle-cuerpo");
        cuerpo.innerHTML = "";

        detalle.forEach((fila) => {
            const tr = document.createElement("tr");
            tr.innerHTML =
                `<td>${nombresMes[parseInt(fila.mes, 10)] || fila.mes}</td><td>${fila.anio}</td><td>L${parseFloat(fila.monto).toFixed(2)}</td>`;
            cuerpo.appendChild(tr);
        });

        abrirModal(modalDetalle);
    });
});