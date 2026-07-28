<?php
require_once __DIR__ . "/../../config/conexion.php";
$conexion = Connection();


if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../../index.php?modulo=agua");
    exit;
}

$id_vivienda = filter_input(INPUT_POST, "id_vivienda", FILTER_VALIDATE_INT);
$mes_pagado  = $_POST["mes_pagado"] ?? null;
$monto       = filter_input(INPUT_POST, "monto", FILTER_VALIDATE_FLOAT);

if (!$id_vivienda || !$mes_pagado || $monto === false) {
    die("Datos incompletos para registrar el pago.");
}

try {
    $conexion->beginTransaction();

    $sqlPago = "
        INSERT INTO pagos
        (
            id_vivienda,
            mes_pagado,
            monto,
            fecha_pago
        )
        VALUES (?, ?, ?, NOW())
    ";
    $stmtPago = $conexion->prepare($sqlPago);
    $stmtPago->execute([$id_vivienda, $mes_pagado, $monto]);

    $conexion->commit();

    header("Location: ../../index.php?modulo=agua&mensaje=guardado");
    exit;

} catch (PDOException $e) {
    $conexion->rollBack();
    error_log("Error al registrar pago: " . $e->getMessage());
    die("Error al guardar el pago: " . $e->getMessage());
}