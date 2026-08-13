<?php
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../agua/helpers_agua.php"; // solo para usar ID_ESTADO_PAGADO / ID_ESTADO_MORA
$conexion = Connection();

// =======================
// TOTAL RECAUDADO
// =======================
$total_recaudado = $conexion->query("SELECT SUM(total) AS total FROM pagos_agua")->fetch()['total'] ?? 0;

// =======================
// USUARIOS REGISTRADOS
// =======================
$total_usuarios = $conexion->query("SELECT COUNT(*) AS total FROM usuarios")->fetch()['total'] ?? 0;

// =======================
// VIVIENDAS PAGADAS / EN MORA
// =======================
$stmt_pagadas = $conexion->prepare("SELECT COUNT(*) AS total FROM viviendas WHERE id_estado_pago = ?");
$stmt_pagadas->execute([ID_ESTADO_PAGADO]);
$viviendas_pagadas = $stmt_pagadas->fetch()['total'] ?? 0;

$stmt_mora = $conexion->prepare("SELECT COUNT(*) AS total FROM viviendas WHERE id_estado_pago = ?");
$stmt_mora->execute([ID_ESTADO_MORA]);
$viviendas_mora = $stmt_mora->fetch()['total'] ?? 0;

// =======================
// ÚLTIMOS PAGOS
// =======================
$sql_ultimos = "SELECT pa.numero_recibo, CONCAT(u.nombre, ' ', u.apellido) AS nombre_usuario,
                       s.nombre_sector, v.numero_vivienda, pa.fecha_pago_agua, pa.total
                FROM pagos_agua pa
                INNER JOIN usuarios u ON pa.id_usuario = u.id_usuario
                INNER JOIN viviendas v ON pa.id_vivienda = v.id_vivienda
                LEFT JOIN sectores s ON v.id_sector = s.id_sector
                ORDER BY pa.fecha_pago_agua DESC, pa.id_pago_agua DESC
                LIMIT 10";
$ultimos_pagos = $conexion->query($sql_ultimos)->fetchAll();
?>

<div class="dashboard-header">
    <h1>Dashboard</h1>
    <p class="dashboard-subtexto">Resumen general del Patronato Pro-Mejoramiento El Zapotal</p>
</div>

<!-- Tarjetas de resumen -->
<div class="cards">

    <div class="card card-recaudado">
        <h3>Total Recaudado</h3>
        <p>L<?= number_format($total_recaudado, 2) ?></p>
    </div>

    <div class="card card-usuarios">
        <h3>Usuarios Registrados</h3>
        <p><?= $total_usuarios ?></p>
    </div>

    <div class="card card-pagadas">
        <h3>Viviendas Pagadas</h3>
        <p><?= $viviendas_pagadas ?></p>
    </div>

    <div class="card card-mora">
        <h3>Viviendas en Mora</h3>
        <p><?= $viviendas_mora ?></p>
    </div>

</div>

<!-- Accesos directos -->
<div class="accesos-rapidos">

    <a href="index.php?modulo=usuario#nuevo" class="acceso-rapido acceso-usuario">
        <!-- el espan con clase de acceso icono esta reservaod para agregar algun icono -->
        <span class="acceso-icono"></span>
        <span class="acceso-texto">
            <strong>Nuevo usuario</strong>
            <span>Registra un usuario y sus viviendas</span>
        </span>
    </a>

    <a href="index.php?modulo=agua#registrar" class="acceso-rapido acceso-pago">
        <!-- el espan con clase de acceso icono esta reservaod para agregar algun icono -->
        <span class="acceso-icono"></span>
        <span class="acceso-texto">
            <strong>Registrar pago</strong>
            <span>Registra el pago de agua de una vivienda</span>
        </span>
    </a>

</div>

<!-- Últimos pagos -->
<div class="seccion">
    <h3>Últimos pagos</h3>
    <table class="tabla_datos">
        <thead>
            <tr>
                <th>Recibo</th>
                <th>Usuario</th>
                <th>Sector</th>
                <th>Vivienda</th>
                <th>Fecha</th>
                <th>Total (L)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($ultimos_pagos)): ?>
            <tr>
                <td colspan="6">Todavía no hay pagos registrados.</td>
            </tr>
            <?php else: ?>
                <?php foreach ($ultimos_pagos as $pago): ?>
                <tr>
                    <td><?= $pago['numero_recibo'] ?></td>
                    <td><?= htmlspecialchars($pago['nombre_usuario']) ?></td>
                    <td><?= htmlspecialchars($pago['nombre_sector'] ?? '—') ?></td>
                    <td>#<?= htmlspecialchars($pago['numero_vivienda']) ?></td>
                    <td><?= $pago['fecha_pago_agua'] ?></td>
                    <td>L<?= number_format($pago['total'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>