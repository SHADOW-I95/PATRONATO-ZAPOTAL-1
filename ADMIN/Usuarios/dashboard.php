<?php

require_once "conexion.php";
$conexion = connection();

$totalVentas = $conexion->query(
    "SELECT SUM(TOTAL) AS total FROM dashboard"
);

$ventas = $totalVentas->fetch_assoc();

$clientesQuery = $conexion->query(
    "SELECT COUNT(DISTINCT CLIENTE) AS total FROM dashboard"
);

$clientes = $clientesQuery->fetch_assoc();

$pedidosQuery = $conexion->query(
    "SELECT COUNT(*) AS total FROM dashboard"
);

$pedidos = $pedidosQuery->fetch_assoc();

$productosQuery = $conexion->query(
    "SELECT COUNT(DISTINCT PRODUCTO) AS total FROM dashboard"
);

$productos = $productosQuery->fetch_assoc();

$clientesGrafico = $conexion->query("
    SELECT CLIENTE, COUNT(*) AS cantidad
    FROM dashboard
    GROUP BY CLIENTE
");

$clientesLabels = [];
$clientesDatos = [];

while($fila = $clientesGrafico->fetch_assoc()){

    $clientesLabels[] = $fila['CLIENTE'];
    $clientesDatos[] = $fila['cantidad'];

}

$resultado = $conexion->query(
    "SELECT * FROM dashboard"
);

$grafico = $conexion->query(
    "SELECT PRODUCTO, TOTAL FROM dashboard"
);

$labels = [];
$datos = [];

while($filaGrafico = $grafico->fetch_assoc()){

    $labels[] = $filaGrafico['PRODUCTO'];
    $datos[] = $filaGrafico['TOTAL'];

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Dashboard</title>

    <link rel="stylesheet" href="style.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

<div class="contenedor">

    <div class="sidebar">

        <h2>Mi Empresa</h2>

        <ul>
            <li><a href="#">Dashboard</a></li>
            <li><a href="#">Ventas</a></li>
            <li><a href="#">Clientes</a></li>
            <li><a href="#">Productos</a></li>
            <li><a href="#">Reportes</a></li>
        </ul>

    </div>

    <div class="contenido">

        <h1>Bienvenido al Dashboard</h1>

        <div class="cards">

            <div class="card">
                <h3>Ventas</h3>
                <p>$<?php echo $ventas['total']; ?></p>
            </div>

            <div class="card">
                <h3>Clientes</h3>
                <p><?php echo $clientes['total']; ?></p>
            </div>

            <div class="card">
                <h3>Pedidos</h3>
                <p><?php echo $pedidos['total']; ?></p>
            </div>

            <div class="card">
                <h3>Productos</h3>
                <p><?php echo $productos['total']; ?></p>
            </div>

        </div>

        <div class="charts">

            <div class="chart">
                <canvas id="ventasChart"></canvas>
            </div>

            <div class="chart">
                <canvas id="clientesChart"></canvas>
            </div>

        </div>

        <div class="table-container">

            <table>

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Fecha</th>
                    </tr>

                </thead>

                <tbody>

                <?php while($fila = $resultado->fetch_assoc()) { ?>

                    <tr>
                        <td><?php echo $fila['ID']; ?></td>
                        <td><?php echo $fila['PRODUCTO']; ?></td>
                        <td><?php echo $fila['CLIENTE']; ?></td>
                        <td>$<?php echo $fila['TOTAL']; ?></td>
                        <td><?php echo $fila['FECHA']; ?></td>
                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<script>

const labels = <?php echo json_encode($labels); ?>;
const datos = <?php echo json_encode($datos); ?>;

const clientesLabels = <?php echo json_encode($clientesLabels); ?>;
const clientesDatos = <?php echo json_encode($clientesDatos); ?>;

</script>

<script src="script.js"></script>

</body>

</html>