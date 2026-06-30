<?php
include("conexion.php");
session_start();

$IDUSUARIO = $_SESSION['ID_USUARIO'];

$saldoPendiente = $conexion->query("
    SELECT SUM(monto) AS total
    FROM pagos
    WHERE estado = 'Pendiente'
    AND ID_USUARIO = $IDUSUARIO
");
$saldo = $saldoPendiente->fetch_assoc();

$pagosRealizados = $conexion->query("
    SELECT COUNT(*) AS total
    FROM pagos
    WHERE estado = 'Pagado'
    AND ID_USUARIO = $IDUSUARIO
");
$realizados = $pagosRealizados->fetch_assoc();

$pagosVencidos = $conexion->query("
    SELECT COUNT(*) AS total
    FROM pagos
    WHERE estado = 'Pendiente'
    AND fecha_vencimiento < CURDATE()
    AND ID_USUARIO = $IDUSUARIO
");
$vencidos = $pagosVencidos->fetch_assoc();

$grafico = $conexion->query("
    SELECT mes, SUM(monto) AS total
    FROM pagos
    WHERE ID_USUARIO = $IDUSUARIO
    GROUP BY mes
");

$labels = [];
$datos = [];
while($fila = $grafico->fetch_assoc()){
    $labels[] = $fila['mes'];
    $datos[] = $fila['total'];
}

$resultado = $conexion->query("
    SELECT * FROM pagos
    WHERE ID_USUARIO = $IDUSUARIO
    ORDER BY fecha_pago DESC
");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="scripts.css">
</head>
<body>
    
<div class="pagos">
<h3>Pagos Realizados</h3>
 <p>$<?php echo $ID_PAGO['total']; ?></p>
</div>

</body>
</html>