<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/auth.php";

// Exclusivo del Administrador — a propósito NO pasa por el sistema de
// permisos dinámicos (ver config/permisos.php), igual que Configuración.
if (!esAdministrador()) {
    echo '<p>No tienes permisos para acceder a este módulo.</p>';
    exit;
}

$conexion = Connection();

$id_empleado_filtro = filter_input(INPUT_GET, 'id_empleado', FILTER_VALIDATE_INT);
$fecha_desde = $_GET['desde'] ?? '';
$fecha_hasta = $_GET['hasta'] ?? '';

$where = [];
$params = [];

if ($id_empleado_filtro) {
    $where[] = "b.id_empleado = ?";
    $params[] = $id_empleado_filtro;
}
if ($fecha_desde) {
    $where[] = "DATE(b.fecha_hora) >= ?";
    $params[] = $fecha_desde;
}
if ($fecha_hasta) {
    $where[] = "DATE(b.fecha_hora) <= ?";
    $params[] = $fecha_hasta;
}

$sqlWhere = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "SELECT b.id_bitacora, b.modulo, b.accion, b.descripcion, b.fecha_hora,
               CONCAT(e.nombre, ' ', e.apellido) AS nombre_empleado
        FROM bitacora b
        INNER JOIN empleados e ON b.id_empleado = e.id_empleado
        $sqlWhere
        ORDER BY b.fecha_hora DESC
        LIMIT 300";
$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$registros = $stmt->fetchAll();

$empleados = $conexion->query("SELECT id_empleado, CONCAT(nombre, ' ', apellido) AS nombre_completo FROM empleados ORDER BY nombre")->fetchAll();

function clase_badge_accion($accion)
{
    if ($accion === 'creó')    return 'badge-pagado';
    if ($accion === 'eliminó') return 'badge-mora';
    return 'badge-pendiente'; // editó
}
?>

<div class="modulo_header">
    <div class="encabezado">
        <h1>Registro de empleado</h1>
    </div>
</div>

<div class="seccion">
    <form method="GET" class="opciones">
        <input type="hidden" name="modulo" value="registro_empleado">

        <select name="id_empleado">
            <option value="">Todos los empleados</option>
            <?php foreach ($empleados as $e): ?>
            <option value="<?= $e['id_empleado'] ?>" <?= $id_empleado_filtro == $e['id_empleado'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($e['nombre_completo']) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <input type="date" name="desde" value="<?= htmlspecialchars($fecha_desde) ?>" title="Desde">
        <input type="date" name="hasta" value="<?= htmlspecialchars($fecha_hasta) ?>" title="Hasta">

        <button type="submit" class="btn-secundario">Filtrar</button>
        <a href="index.php?modulo=registro_empleado" class="btn-secundario">Limpiar</a>
    </form>
</div>

<div class="seccion">
    <h3>Actividad reciente <?= $id_empleado_filtro || $fecha_desde || $fecha_hasta ? '(filtrada)' : '(últimos 300 registros)' ?></h3>

    <?php if (!$registros): ?>
    <p>No hay actividad registrada todavía con estos filtros.</p>
    <?php else: ?>
    <table class="tabla_datos">
        <thead>
            <tr>
                <th>Fecha y hora</th>
                <th>Empleado</th>
                <th>Módulo</th>
                <th>Acción</th>
                <th>Detalle</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($registros as $r): ?>
            <tr>
                <td><?= date('d/m/Y h:i A', strtotime($r['fecha_hora'])) ?></td>
                <td><?= htmlspecialchars($r['nombre_empleado']) ?></td>
                <td><?= htmlspecialchars(ucfirst($r['modulo'])) ?></td>
                <td><span class="badge <?= clase_badge_accion($r['accion']) ?>"><?= htmlspecialchars(ucfirst($r['accion'])) ?></span></td>
                <td><?= htmlspecialchars($r['descripcion']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>