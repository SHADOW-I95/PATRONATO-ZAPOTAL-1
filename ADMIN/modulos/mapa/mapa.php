<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/auth.php";

// Cualquier empleado (Empleado o Administrador) puede ver el mapa.
// Solo el Administrador puede registrar, editar o quitar ubicaciones
// (esto también se valida de nuevo en guardar_ubicacion.php y
// eliminar_ubicacion.php — nunca hay que confiar solo en ocultar botones).
if (!esEmpleado()) {
    echo '<p>No tienes permisos para acceder a este módulo.</p>';
    exit;
}

$conexion = Connection();
$puedeEditar = esAdministrador();

// Viviendas con su ubicación (si ya la tienen) y los datos de usuario/sector.
// El popup del mapa siempre lee estos datos en vivo, nunca los guarda aparte,
// así que jamás queda desactualizado si cambian en el CRUD de usuarios.
$sql = "SELECT v.id_vivienda, v.numero_vivienda, v.latitud, v.longitud,
               s.nombre_sector,
               CONCAT(u.nombre, ' ', u.apellido) AS nombre_usuario, u.dni
        FROM viviendas v
        LEFT JOIN sectores s ON v.id_sector = s.id_sector
        LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
        ORDER BY u.nombre, v.numero_vivienda";
$viviendas = $conexion->query($sql)->fetchAll();

$sectores = $conexion->query("SELECT id_sector, nombre_sector FROM sectores ORDER BY nombre_sector")->fetchAll();
?>

<div class="modulo_header">
    <div class="encabezado">
        <h1>Mapa de viviendas</h1>
    </div>
</div>

<div class="mapa-layout">
    <aside class="mapa-panel">

        <div class="campo">
            <label>Buscar (número, sector, nombre o DNI)</label>
            <input type="text" id="mapa-buscar" placeholder="Ej. 34, Campito, Juan...">
        </div>

        <div class="campo">
            <label>Sector</label>
            <select id="mapa-filtro-sector">
                <option value="">Todos los sectores</option>
                <?php foreach ($sectores as $s): ?>
                <option value="<?= htmlspecialchars($s['nombre_sector']) ?>"><?= htmlspecialchars($s['nombre_sector']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <label class="mapa-check">
            <input type="checkbox" id="mapa-solo-sin-ubicacion">
            Mostrar solo viviendas sin ubicación
        </label>

        <?php if ($puedeEditar): ?>
        <hr>
        <h4>Registrar / editar ubicación</h4>

        <div class="campo">
            <label>Vivienda</label>
            <select id="mapa-vivienda-seleccionada">
                <option value="">Selecciona una vivienda…</option>
                <?php foreach ($viviendas as $v): ?>
                <option value="<?= $v['id_vivienda'] ?>">
                    #<?= htmlspecialchars($v['numero_vivienda']) ?> — <?= htmlspecialchars($v['nombre_sector'] ?? 'Sin sector') ?>
                    <?= $v['latitud'] === null ? ' (sin ubicación)' : '' ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="button" id="mapa-btn-colocar" class="btn-secundario" disabled>Colocar en el mapa</button>
        <p class="mapa-ayuda" id="mapa-ayuda"></p>

        <div class="campo">
            <label>Latitud</label>
            <input type="number" step="0.0000001" id="mapa-lat-manual" placeholder="Ej. 15.5041">
        </div>
        <div class="campo">
            <label>Longitud</label>
            <input type="number" step="0.0000001" id="mapa-lng-manual" placeholder="Ej. -88.0250">
        </div>

        <div class="mapa-acciones-edicion">
            <button type="button" id="mapa-btn-guardar" class="btn-primario">Guardar ubicación</button>
            <button type="button" id="mapa-btn-quitar" class="btn-secundario">Quitar ubicación</button>
        </div>
        <?php endif; ?>

        <hr>
        <div id="mapa-resultados" class="mapa-resultados"></div>

    </aside>

    <div id="mapa-viviendas" class="mapa-contenedor"></div>
</div>

<!-- Leaflet + OpenStreetMap: mapa libre, sin depender de Google Maps -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
/* Estilos propios del módulo Mapa (no había un mapa.css enlazado en index.php) */
.mapa-layout {
    display: flex;
    gap: 16px;
    margin-top: 16px;
    align-items: flex-start;
}
.mapa-panel {
    width: 300px;
    flex-shrink: 0;
    background: #fff;
    border-radius: var(--radio, 10px);
    box-shadow: var(--sombra, 0 1px 3px rgba(0,0,0,0.08));
    padding: 16px;
}
.mapa-panel h4 { margin: 0 0 10px; font-size: 14px; }
.mapa-panel hr { border: 0; border-top: 1px solid var(--borde, #e2e8f0); margin: 14px 0; }
.mapa-check { display: flex; align-items: center; gap: 8px; font-size: 13px; margin-bottom: 10px; cursor: pointer; }
.mapa-ayuda { font-size: 12.5px; color: var(--primario, #2563eb); min-height: 16px; margin: 4px 0 10px; }
.mapa-acciones-edicion { display: flex; gap: 8px; margin-top: 6px; flex-wrap: wrap; }
.mapa-acciones-edicion button { flex: 1; }

.mapa-contenedor {
    flex: 1;
    min-width: 0;
    height: 65vh;
    min-height: 380px;
    border-radius: var(--radio, 10px);
    overflow: hidden;
    box-shadow: var(--sombra, 0 1px 3px rgba(0,0,0,0.08));
}

.mapa-resultados { display: flex; flex-direction: column; gap: 6px; max-height: 260px; overflow-y: auto; }
.mapa-resultado-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    font-size: 12.5px;
    padding: 6px 8px;
    border-radius: 6px;
    background: #f8fafc;
}
.mapa-btn-centrar {
    border: 1px solid var(--borde, #e2e8f0);
    background: #fff;
    border-radius: 6px;
    padding: 3px 8px;
    font-size: 11.5px;
    cursor: pointer;
    flex-shrink: 0;
}
.mapa-btn-centrar:hover { background: #f1f5f9; }

.mapa-popup-acciones { display: flex; gap: 6px; margin-top: 8px; }
.mapa-popup-acciones button {
    border: 1px solid var(--borde, #e2e8f0);
    background: #fff;
    border-radius: 6px;
    padding: 4px 8px;
    font-size: 11.5px;
    cursor: pointer;
}
.mapa-popup-acciones button:hover { background: #f1f5f9; }

/* Responsive: en pantallas angostas el panel se apila arriba del mapa */
@media (max-width: 900px) {
    .mapa-layout { flex-direction: column; }
    .mapa-panel { width: 100%; }
    .mapa-contenedor { width: 100%; height: 50vh; }
}
</style>

<script>
    // Todas las viviendas ya vienen cargadas del servidor; la búsqueda y los
    // filtros trabajan sobre este arreglo en el navegador, sin recargar nada.
    const VIVIENDAS_MAPA = <?= json_encode($viviendas) ?>;
    const PUEDE_EDITAR_MAPA = <?= $puedeEditar ? 'true' : 'false' ?>;
</script>
<script src="assets/js/mapa.js"></script>