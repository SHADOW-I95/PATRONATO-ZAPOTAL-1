<?php

require_once "conexion.php";

$totalVentas = $conexion->query(
    "SELECT SUM(TOTAL_A_PAGAR) AS total FROM pagos_agua"
);
$ventas = $totalVentas->fetch_assoc();

$clientesQuery = $conexion->query(
    "SELECT COUNT(*) AS total FROM usuarios"
);
$clientes = $clientesQuery->fetch_assoc();

$activosQuery = $conexion->query(
    "SELECT COUNT(*) AS total FROM usuarios WHERE ESTADO = 'ACTIVO'"
);
$activos = $activosQuery->fetch_assoc();

$sectorGrafico = $conexion->query("
    SELECT SECTOR, COUNT(*) AS cantidad
    FROM usuarios
    GROUP BY SECTOR
");

$sectorLabels = [];
$sectorDatos = [];

while ($fila = $sectorGrafico->fetch_assoc()) {
    $sectorLabels[] = $fila['SECTOR'];
    $sectorDatos[] = $fila['cantidad'];
}

$mesGrafico = $conexion->query("
    SELECT MES, SUM(TOTAL_A_PAGAR) AS total
    FROM pagos_agua
    GROUP BY MES
");

$mesLabels = [];
$mesDatos = [];

while ($fila = $mesGrafico->fetch_assoc()) {
    $mesLabels[] = $fila['MES'];
    $mesDatos[] = $fila['total'];
}

$resultado = $conexion->query("
    SELECT NO_PAGO, NOMBRE, SECTOR, NO_CASA, MES, FECHA_DE_PAGO, TOTAL_A_PAGAR
    FROM pagos_agua
    ORDER BY FECHA_DE_PAGO DESC
    LIMIT 20
");

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<div class="contenedor">

    <div class="contenido">

        <h1>Bienvenido al Dashboard</h1>

        <div class="cards">

            <div class="card">
                <h3>Total Recaudado</h3>
                <p>$<?php echo number_format($ventas['total'] ?? 0, 2); ?></p>
            </div>

            <div class="card">
                <h3>Usuarios Registrados</h3>
                <p><?php echo $clientes['total'] ?? 0; ?></p>
            </div>

            <div class="card">
                <h3>Usuarios Activos</h3>
                <p><?php echo $activos['total'] ?? 0; ?></p>
            </div>

        </div>

        <div class="charts">

            <div class="chart">
                <canvas id="sectorChart"></canvas>
            </div>

            <div class="chart">
                <canvas id="mesChart"></canvas>
            </div>

        </div>

        <div class="table-container">

            <table>
                <thead>
                    <tr>
                        <th>No. Pago</th>
                        <th>Nombre</th>
                        <th>Sector</th>
                        <th>No. Casa</th>
                        <th>Mes</th>
                        <th>Fecha Pago</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($fila = $resultado->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $fila['NO_PAGO']; ?></td>
                        <td><?php echo $fila['NOMBRE']; ?></td>
                        <td><?php echo $fila['SECTOR']; ?></td>
                        <td><?php echo $fila['NO_CASA']; ?></td>
                        <td><?php echo $fila['MES']; ?></td>
                        <td><?php echo $fila['FECHA_DE_PAGO']; ?></td>
                        <td>$<?php echo number_format($fila['TOTAL_A_PAGAR'], 2); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

        </div>

    </div>

</div>

<script>
const sectorLabels = <?php echo json_encode($sectorLabels); ?>;
const sectorDatos = <?php echo json_encode($sectorDatos); ?>;

const mesLabels = <?php echo json_encode($mesLabels); ?>;
const mesDatos = <?php echo json_encode($mesDatos); ?>;

new Chart(document.getElementById('sectorChart'), {
    type: 'pie',
    data: {
        labels: sectorLabels,
        datasets: [{
            data: sectorDatos,
            backgroundColor: ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796']
        }]
    },
    options: { plugins: { title: { display: true, text: 'Usuarios por Sector' } } }
});

new Chart(document.getElementById('mesChart'), {
    type: 'bar',
    data: {labels: mesLabels,
        datasets: [{
            label: 'Total Recaudado',
            data: mesDatos,
            backgroundColor: '#4e73df'
        }]
    },
    options: { plugins: { title: { display: true, text: 'Recaudación por Mes' } } }
});
</script>

</body>

</html>