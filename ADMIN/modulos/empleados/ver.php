<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/auth.php";

if (!esAdministrador()) {
    echo '<p>No tienes permisos para ver esta información.</p>';
    exit;
}

$conexion = Connection();

$id_empleado = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_empleado) {
    echo "<p>Empleado no válido.</p>";
    exit;
}

$sql = "SELECT e.dni, e.nombre, e.apellido, e.telefono, e.codigo, e.fecha_registro, r.nombre_rol,
               TIMESTAMPDIFF(YEAR, e.fecha_nacimiento, CURDATE()) AS edad
        FROM empleados e
        LEFT JOIN roles r ON e.id_rol = r.id_roles
        WHERE e.id_empleado = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id_empleado]);
$empleado = $stmt->fetch();

if (!$empleado) {
    echo "<p>Empleado no encontrado.</p>";
    exit;
}
?>

<h3>Información del empleado</h3>

<div class="informacion">
    <div class="campo"><label>DNI</label><span><?= htmlspecialchars($empleado['dni']) ?></span></div>
    <div class="campo"><label>Nombre</label><span><?= htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido']) ?></span></div>
    <div class="campo"><label>Edad</label><span><?= htmlspecialchars($empleado['edad'] ?? '—') ?></span></div>
    <div class="campo"><label>Teléfono</label><span><?= htmlspecialchars($empleado['telefono'] ?? '—') ?></span></div>
    <div class="campo"><label>Código de acceso</label><span><?= htmlspecialchars($empleado['codigo']) ?></span></div>
    <div class="campo"><label>Rol</label><span><?= htmlspecialchars($empleado['nombre_rol'] ?? '—') ?></span></div>
    <div class="campo"><label>Empleado desde</label><span><?= htmlspecialchars($empleado['fecha_registro']) ?></span></div>
</div>