<?php
require_once __DIR__ . "/../../../config/conexion.php";

$conexion = Connection();
$id_usuario = (int)$_GET['id'];

try {
    $conexion->beginTransaction();

    // 1. Eliminar los detalles de los pagos
    $sql = "DELETE d
            FROM detalle_pago_agua d
            INNER JOIN pagos_agua p
                ON d.id_pago_agua = p.id_pago_agua
            INNER JOIN viviendas v
                ON p.id_vivienda = v.id_vivienda
            WHERE v.id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_usuario]);

    // 2. Eliminar los pagos
    $sql = "DELETE p
            FROM pagos_agua p
            INNER JOIN viviendas v
                ON p.id_vivienda = v.id_vivienda
            WHERE v.id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_usuario]);

    // 3. Eliminar las viviendas
    $sql = "DELETE FROM viviendas
            WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_usuario]);

    // 4. Eliminar el usuario
    $sql = "DELETE FROM usuarios
            WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_usuario]);

    $conexion->commit();

    header("Location: ../../index.php?modulo=usuario");
    exit;

} catch (Exception $e) {
    $conexion->rollBack();
    echo "Error: " . $e->getMessage();
}