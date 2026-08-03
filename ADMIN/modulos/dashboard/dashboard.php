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
// VIVIENDAS POR SECTOR (para el gráfico)
// =======================
$sql_sector = "SELECT s.nombre_sector, COUNT(v.id_vivienda) AS cantidad
               FROM viviendas v
               LEFT JOIN sectores s ON v.id_sector = s.id_sector
               GROUP BY s.nombre_sector";
$filas_sector = $conexion->query($sql_sector)->fetchAll();

// De mayor a menor cantidad de viviendas (para el gráfico y la lista lateral)
usort($filas_sector, fn($a, $b) => $b['cantidad'] - $a['cantidad']);

$sectorLabels = [];
$sectorDatos  = [];
foreach ($filas_sector as $fila) {
    $sectorLabels[] = $fila['nombre_sector'] ?? 'Sin sector';
    $sectorDatos[]  = (int) $fila['cantidad'];
}

// =======================
// RECAUDADO POR MES (para el gráfico)
// =======================
$sql_mes = "SELECT DATE_FORMAT(fecha_pago_agua, '%Y-%m') AS periodo, SUM(total) AS total
            FROM pagos_agua
            WHERE fecha_pago_agua IS NOT NULL
            GROUP BY periodo
            ORDER BY periodo";
$filas_mes = $conexion->query($sql_mes)->fetchAll();

$nombres_meses = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                   7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];

$mesLabels = [];
$mesDatos  = [];
foreach ($filas_mes as $fila) {
    [$anio, $mes] = explode('-', $fila['periodo']);
    $mesLabels[] = $nombres_meses[(int) $mes] . ' ' . $anio;
    $mesDatos[]  = (float) $fila['total'];
}

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
                LIMIT 20";
$ultimos_pagos = $conexion->query($sql_ultimos)->fetchAll();
?>

<!-- =======================
     CONTENEDOR PRINCIPAL
======================== -->
<div class="padre_contenido">

    <!-- Tarjetas -->
    <div class="cards">

        <div class="card">
            <h3>Total Recaudado</h3>
            <p>L<?= number_format($total_recaudado, 2) ?></p>
        </div>

        <div class="card">
            <h3>Usuarios Registrados</h3>
            <p><?= $total_usuarios ?></p>
        </div>

        <div class="card">
            <h3>Viviendas Pagadas</h3>
            <p><?= $viviendas_pagadas ?></p>
        </div>

        <div class="card">
            <h3>Viviendas en Mora</h3>
            <p><?= $viviendas_mora ?></p>
        </div>

    </div>

    <!-- Gráficos -->
    <div class="charts">

        <div class="chart chart-sector">
            <canvas id="sectorChart"></canvas>
            <div class="lista-sectores">
                <?php foreach ($filas_sector as $fila): ?>
                <div class="sector-item">
                    <span class="sector-nombre"><?= htmlspecialchars($fila['nombre_sector'] ?? 'Sin sector') ?></span>
                    <span class="sector-cantidad"><?= (int) $fila['cantidad'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="chart">
            <canvas id="mesChart" style="width:100%; height:100%;"></canvas>
        </div>

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
                <tr><td colspan="6">Todavía no hay pagos registrados.</td></tr>
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

</div>


<!-- Chart.js: si ya lo cargas en index.php, puedes quitar esta línea -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const sectorLabels = <?= json_encode($sectorLabels) ?>;
const sectorDatos  = <?= json_encode($sectorDatos) ?>;
const mesLabels    = <?= json_encode($mesLabels) ?>;
const mesDatos     = <?= json_encode($mesDatos) ?>;

new Chart(document.getElementById("sectorChart"), {
    type: "pie",
    data: {
        labels: sectorLabels,
        datasets: [{
            data: sectorDatos,
            backgroundColor: ["#60a5fa", "#34d399", "#fbbf24", "#f87171", "#a78bfa", "#f472b6"]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

new Chart(document.getElementById("mesChart"), {
    type: "line",
    data: {
        labels: mesLabels,
        datasets: [{
            label: "Recaudado (L)",
            data: mesDatos,
            borderColor: "#34d399",
            backgroundColor: "rgba(52, 211, 153, 0.15)",
            fill: true,
            tension: 0.3,
            pointBackgroundColor: "#34d399",
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true } }
    }
});
</script>