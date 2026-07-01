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
