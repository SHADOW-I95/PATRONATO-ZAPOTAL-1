<?php // Indica el inicio del código PHP.

// Incluye el archivo de conexión a la base de datos.
// require_once evita que el archivo se cargue más de una vez.
require_once "conexion.php";


// =======================
// TOTAL RECAUDADO
// =======================

// Ejecuta una consulta SQL.
// SUM(TOTAL_A_PAGAR) suma todos los pagos registrados en la tabla pagos_agua.
$totalVentas = $conexion->query(
    "SELECT SUM(TOTAL_A_PAGAR) AS total FROM pagos_agua"
);

// Obtiene el resultado de la consulta como un arreglo asociativo.
$ventas = $totalVentas->fetch_assoc();


// =======================
// TOTAL DE USUARIOS
// =======================

// Ejecuta una consulta que cuenta todos los usuarios registrados.
$clientesQuery = $conexion->query(
    "SELECT COUNT(*) AS total FROM usuarios"
);

// Guarda el resultado de la consulta en un arreglo.
$clientes = $clientesQuery->fetch_assoc();


// =======================
// USUARIOS ACTIVOS
// =======================

// Cuenta únicamente los usuarios cuyo estado sea ACTIVO.
$activosQuery = $conexion->query(
    "SELECT COUNT(*) AS total FROM usuarios WHERE ESTADO = 'ACTIVO'"
);

// Guarda el resultado en un arreglo.
$activos = $activosQuery->fetch_assoc();


// =======================
// DATOS PARA EL GRÁFICO POR SECTOR
// =======================

// Consulta que agrupa los usuarios por sector.
$sectorGrafico = $conexion->query("

    
    SELECT SECTOR,

    COUNT(*) AS cantidad

  
    FROM usuarios


    GROUP BY SECTOR

");

// Arreglo donde se almacenarán los nombres de los sectores.
$sectorLabels = [];

// Arreglo donde se almacenarán las cantidades de usuarios.
$sectorDatos = [];


// Recorre cada fila obtenida de la consulta.
while ($fila = $sectorGrafico->fetch_assoc()) {

    // Guarda el nombre del sector.
    $sectorLabels[] = $fila['SECTOR'];

    // Guarda la cantidad de usuarios del sector.
    $sectorDatos[] = $fila['cantidad'];

}


// =======================
// DATOS PARA EL GRÁFICO POR MES
// =======================

// Consulta que obtiene el total recaudado por cada mes.
$mesGrafico = $conexion->query("


    SELECT MES,

    SUM(TOTAL_A_PAGAR) AS total

    FROM pagos_agua

    GROUP BY MES

");

// Arreglo donde se guardarán los nombres de los meses.
$mesLabels = [];

// Arreglo donde se guardarán los montos recaudados.
$mesDatos = [];


// Recorre todos los registros obtenidos.
while ($fila = $mesGrafico->fetch_assoc()) {

    // Guarda el nombre del mes.
    $mesLabels[] = $fila['MES'];

    // Guarda el total de dinero del mes.
    $mesDatos[] = $fila['total'];

}


// =======================
// ÚLTIMOS PAGOS
// =======================

// Consulta para obtener los últimos 20 pagos registrados.
$resultado = $conexion->query("

    
    SELECT
        NO_PAGO,
        NOMBRE,
        SECTOR,
        NO_CASA,
        MES,
        FECHA_DE_PAGO,
        TOTAL_A_PAGAR

  
    FROM pagos_agua

    
    ORDER BY FECHA_DE_PAGO DESC
    LIMIT 20

");
   

?>

<!-- =======================
     CONTENEDOR PRINCIPAL
======================== -->

<!-- Contenedor general del dashboard -->
<div class="contenedor">

    <!-- Área principal del contenido -->
    <div class="contenido">

        <!-- Contenedor de las tarjetas -->
        <div class="cards">

            <!-- Tarjeta Total Recaudado -->
            <div class="card">

                <!-- Título -->
                <h3>Total Recaudado</h3>

                <!-- Muestra el dinero total con dos decimales -->
                <p>$<?php echo number_format($ventas['total'] ?? 0, 2); ?></p>

            </div>

            <!-- Tarjeta Usuarios Registrados -->
            <div class="card">

                <!-- Título -->
                <h3>Usuarios Registrados</h3>

                <!-- Muestra la cantidad de usuarios -->
                <p><?php echo $clientes['total'] ?? 0; ?></p>

            </div>

            <!-- Tarjeta Usuarios Activos -->
            <div class="card">

                <!-- Título -->
                <h3>Usuarios Activos</h3>

                <!-- Muestra la cantidad de usuarios activos -->
                <p><?php echo $activos['total'] ?? 0; ?></p>

            </div>

        </div>

        <!-- Contenedor de los gráficos -->
        <div class="charts">

            <!-- Área del gráfico -->
            <div class="chart">

                <!-- Canvas donde Chart.js dibujará el gráfico -->
                <canvas id="sectorChart"></canvas>

            </div>

        </div>

    </div>

</div>

<script>

// Convierte el arreglo PHP de sectores en un arreglo de JavaScript.
const sectorLabels = <?php echo json_encode($sectorLabels); ?>;

// Convierte las cantidades por sector a JavaScript.
const sectorDatos = <?php echo json_encode($sectorDatos); ?>;

// Convierte los nombres de los meses a JavaScript.
const mesLabels = <?php echo json_encode($mesLabels); ?>;

// Convierte los totales por mes a JavaScript.
const mesDatos = <?php echo json_encode($mesDatos); ?>;

</script>