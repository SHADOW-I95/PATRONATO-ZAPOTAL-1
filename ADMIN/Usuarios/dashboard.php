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

<div class="contenedor">

    <div class="contenido">

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
    </div>

</div>

<script>
const sectorLabels = <?php echo json_encode($sectorLabels); ?>;
const sectorDatos = <?php echo json_encode($sectorDatos); ?>;
const mesLabels = <?php echo json_encode($mesLabels); ?>;
const mesDatos = <?php echo json_encode($mesDatos); ?>;
</script>

