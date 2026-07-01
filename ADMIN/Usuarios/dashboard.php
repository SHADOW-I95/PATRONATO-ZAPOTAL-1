<?php
$IDUSUARIO = 1; // ID de un usuario que exista en la base de datos

$saldoPendiente = $conexion->query("
SELECT SUM(monto) AS total
FROM pagos
WHERE estado='Pendiente'
AND id_usuario = $IDUSUARIO
");
$id_usuarios = $_SESSION['id_usuario'];

$saldoPendiente = $conexion->query("
    SELECT SUM(monto) AS total
    FROM pagos
    WHERE estado = 'Pendiente'
    AND ID_USUARIO = $id_usuarios
");
$saldo = $saldoPendiente->fetch_assoc();

$pagosRealizados = $conexion->query("
    SELECT COUNT(*) AS total
    FROM pagos
    WHERE estado = 'Pagado'
    AND ID_USUARIO = $id_usuarios
");
$realizados = $pagosRealizados->fetch_assoc();

$pagosVencidos = $conexion->query("
    SELECT COUNT(*) AS total
    FROM pagos
    WHERE estado = 'Pendiente'
    AND fecha_vencimiento < CURDATE()
    AND ID_USUARIO = $id_usuarios
");
$vencidos = $pagosVencidos->fetch_assoc();

$grafico = $conexion->query("
    SELECT mes, SUM(monto) AS total
    FROM pagos
    WHERE ID_USUARIO = $id_usuarios
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
    WHERE ID_USUARIO = $id_usuarios
    ORDER BY fecha_pago DESC
");
   

