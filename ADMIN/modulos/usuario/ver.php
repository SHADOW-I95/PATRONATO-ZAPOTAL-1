<?php
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../agua/helpers_agua.php";
$conexion = Connection();

$id_usuario = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_usuario) {
    echo "<p>Usuario no válido.</p>";
    exit;
}

// Datos básicos del usuario
$sql_usuario = "SELECT dni, nombre, apellido, telefono, codigo,
                       TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad
                FROM usuarios WHERE id_usuario = ?";
$stmt_usuario = $conexion->prepare($sql_usuario);
$stmt_usuario->execute([$id_usuario]);
$usuario = $stmt_usuario->fetch();

if (!$usuario) {
    echo "<p>Usuario no encontrado.</p>";
    exit;
}

// Viviendas del usuario, con su estado de pago recalculado
$sql_viviendas = "SELECT v.id_vivienda, v.numero_vivienda, v.cuota,
                         s.nombre_sector, se.nombre_servicio
                  FROM viviendas v
                  LEFT JOIN sectores s ON v.id_sector = s.id_sector
                  LEFT JOIN servicios se ON v.id_servicio = se.id_servicio
                  WHERE v.id_usuario = ?";
$stmt_viviendas = $conexion->prepare($sql_viviendas);
$stmt_viviendas->execute([$id_usuario]);
$viviendas = $stmt_viviendas->fetchAll();

function clase_badge($nombre_estado)
{
    if ($nombre_estado === 'Pagado') return 'badge-pagado';
    if ($nombre_estado === 'Mora')   return 'badge-mora';
    return 'badge-pendiente';
}
?>

<div class="informacion">
    <div class="campo"><label>DNI</label><span><?= htmlspecialchars($usuario['dni']) ?></span></div>
    <div class="campo"><label>Nombre</label><span><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></span></div>
    <div class="campo"><label>Edad</label><span><?= htmlspecialchars($usuario['edad'] ?? '—') ?></span></div>
    <div class="campo"><label>Teléfono</label><span><?= htmlspecialchars($usuario['telefono'] ?? '—') ?></span></div>
    <div class="campo"><label>Código</label><span><?= htmlspecialchars($usuario['codigo']) ?></span></div>
</div>

<h4>Viviendas</h4>

<?php if (empty($viviendas)): ?>
    <p>Este usuario no tiene viviendas registradas.</p>
<?php else: ?>
<table class="tabla_datos">
    <thead>
        <tr>
            <th>Vivienda</th>
            <th>Sector</th>
            <th>Servicio</th>
            <th>Cuota (L)</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($viviendas as $v): ?>
            <?php $estado = refrescar_estado_vivienda($conexion, (int) $v['id_vivienda']); ?>
            <tr>
                <td>#<?= htmlspecialchars($v['numero_vivienda']) ?></td>
                <td><?= htmlspecialchars($v['nombre_sector'] ?? '—') ?></td>
                <td><?= htmlspecialchars($v['nombre_servicio'] ?? '—') ?></td>
                <td>L<?= number_format($v['cuota'], 2) ?></td>
                <td><span class="badge <?= clase_badge($estado['nombre']) ?>"><?= $estado['nombre'] ?></span></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<style>
.badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
.badge-pagado    { background: #dcfce7; color: #166534; }
.badge-pendiente { background: #fef3c7; color: #92400e; }
.badge-mora      { background: #fee2e2; color: #991b1b; }
</style>