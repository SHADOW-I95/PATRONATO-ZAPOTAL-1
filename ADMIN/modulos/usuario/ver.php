<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/permisos.php";
require_once __DIR__ . "/../agua/helpers_agua.php";

if (!tienePermiso('usuario')) {
    http_response_code(403);
    exit;
}

$conexion = Connection();

$id_usuario = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_usuario) {
    echo "<p>Usuario no válido.</p>";
    exit;
}

// Datos básicos del usuario
$sql_usuario = "SELECT dni, nombre, apellido, telefono, codigo, foto_perfil,
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

// Historial de traspasos donde este usuario participó (como comprador o como vendedor)
$sql_historial = "SELECT t.id_traspaso, t.motivo, t.deuda_al_momento, t.fecha_traspaso,
                          v.numero_vivienda,
                          CONCAT(ua.nombre, ' ', ua.apellido) AS nombre_anterior,
                          CONCAT(un.nombre, ' ', un.apellido) AS nombre_nuevo
                   FROM traspasos_vivienda t
                   INNER JOIN viviendas v ON t.id_vivienda = v.id_vivienda
                   INNER JOIN usuarios ua ON t.id_usuario_anterior = ua.id_usuario
                   INNER JOIN usuarios un ON t.id_usuario_nuevo = un.id_usuario
                   WHERE t.id_usuario_anterior = ? OR t.id_usuario_nuevo = ?
                   ORDER BY t.fecha_traspaso DESC";
$stmt_historial = $conexion->prepare($sql_historial);
$stmt_historial->execute([$id_usuario, $id_usuario]);
$historial_traspasos = $stmt_historial->fetchAll();

// Traduce el nombre del estado a la clase CSS del badge correspondiente
function clase_badge($nombre_estado)
{
    if ($nombre_estado === 'Pagado') return 'badge-pagado';
    if ($nombre_estado === 'Mora')   return 'badge-mora';
    return 'badge-pendiente';
}
?>

<h3>Información del usuario</h3>

<div class="ver-usuario-foto">
    <?php if (!empty($usuario['foto_perfil'])): ?>
        <img src="../SITIO/<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto de perfil">
    <?php else: ?>
        <div class="ver-usuario-sin-foto"><?= htmlspecialchars(strtoupper(substr($usuario['nombre'], 0, 1))) ?></div>
    <?php endif; ?>
</div>

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
            <?php
            // Recalcula el estado en cada visita, así el "Ver" nunca muestra un dato viejo
            $estado = refrescar_estado_vivienda($conexion, (int) $v['id_vivienda']);
            ?>
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

<?php if ($historial_traspasos): ?>
<h4 style="margin-top:18px;">Historial de traspasos</h4>
<table class="tabla_datos">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Vivienda</th>
            <th>De</th>
            <th>A</th>
            <th>Motivo</th>
            <th>Deuda heredada</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($historial_traspasos as $h): ?>
        <tr>
            <td><?= date('d/m/Y', strtotime($h['fecha_traspaso'])) ?></td>
            <td>#<?= htmlspecialchars($h['numero_vivienda']) ?></td>
            <td><?= htmlspecialchars($h['nombre_anterior']) ?></td>
            <td><?= htmlspecialchars($h['nombre_nuevo']) ?></td>
            <td><?= htmlspecialchars($h['motivo']) ?></td>
            <td>L<?= number_format($h['deuda_al_momento'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>