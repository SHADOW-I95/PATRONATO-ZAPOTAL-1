function limpiarFiltros(e) {
  e.preventDefault();
  document.querySelectorAll('.fila-filtros input, .fila-filtros select').forEach(el => {
    if(el.tagName === "SELECT") {
      el.selectedIndex = 0;
    } else {
      el.value = "";
    }
  });
  window.location.href = "reportes.php"; // recarga tabla
}

function nuevoReporte() {
  window.location.href = "nuevo_reporte.php";
}
