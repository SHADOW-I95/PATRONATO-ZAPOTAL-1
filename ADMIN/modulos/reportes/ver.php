<?php
require_once __DIR__ . "/../../../config/conexion.php";
$conexion = Connection();

$id_reporte = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_reporte) {
    echo "<p>Reporte no válido.</p>";
    exit;
}

// Detalle del reporte, con el nombre del usuario y del tipo de reporte ya resueltos
$sql = "SELECT r.id_reporte, r.id_usuario, r.id_tipo_reporte, r.descripcion_reporte,
               u.nombre, u.apellido, u.dni,
               tr.tipo_reporte
        FROM reportes r
        LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario
        LEFT JOIN tipo_reporte tr ON r.id_tipo_reporte = tr.id_tipo_reporte
        WHERE r.id_reporte = ?";
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
    <div class="campo"><label>ID Reporte</label><span><?= htmlspecialchars($reporte['id_reporte']) ?></span></div>
    <div class="campo"><label>Usuario</label><span><?= htmlspecialchars(($reporte['nombre'] ?? '—') . ' ' . ($reporte['apellido'] ?? '')) ?></span></div>
    <div class="campo"><label>DNI</label><span><?= htmlspecialchars($reporte['dni'] ?? '—') ?></span></div>
    <div class="campo"><label>Tipo de reporte</label><span><?= htmlspecialchars($reporte['tipo_reporte'] ?? '—') ?></span></div>
</div>

<div class="campo">
    <label>Descripción</label>
    <span><?= nl2br(htmlspecialchars($reporte['descripcion_reporte'])) ?></span>
</div>