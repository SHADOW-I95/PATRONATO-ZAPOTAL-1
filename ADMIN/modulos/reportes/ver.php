<?php
require_once __DIR__ . "/../../../config/conexion.php";
$conexion = Connection();

$id_reporte = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_reporte) {
    echo "<p>Reporte no válido.</p>";
    exit;
}

$sql = "SELECT reportes.descripcion_reporte, tipo_reporte.tipo_reporte,
               CONCAT(usuarios.nombre, ' ', usuarios.apellido) AS nombre_usuario,
               usuarios.dni, usuarios.telefono
        FROM reportes
        INNER JOIN usuarios ON reportes.id_usuario = usuarios.id_usuario
        INNER JOIN tipo_reporte ON reportes.id_tipo_reporte = tipo_reporte.id_tipo_reporte
        WHERE reportes.id_reporte = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id_reporte]);
$reporte = $stmt->fetch();

if (!$reporte) {
    echo "<p>Reporte no encontrado.</p>";
    exit;
}
?>

<h3>Información del reporte</h3>

<div class="informacion">
    <div class="campo"><label>Usuario</label><span><?= htmlspecialchars($reporte['nombre_usuario']) ?></span></div>
    <div class="campo"><label>DNI</label><span><?= htmlspecialchars($reporte['dni']) ?></span></div>
    <div class="campo"><label>Teléfono</label><span><?= htmlspecialchars($reporte['telefono'] ?? '—') ?></span></div>
    <div class="campo"><label>Tipo de reporte</label><span><?= htmlspecialchars($reporte['tipo_reporte']) ?></span></div>
</div>

<div class="campo" style="padding: 0 20px 20px;">
    <label>Descripción</label>
    <span><?= nl2br(htmlspecialchars($reporte['descripcion_reporte'])) ?></span>
</div>