<?php
require_once __DIR__ . "/../../../config/conexion.php";
$conexion = Connection();

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($id) {
    $stmt = $conexion->prepare("DELETE FROM empleados WHERE id_empleado = ?");
    $stmt->execute([$id]);
}

header("Location: ../../index.php?modulo=empleados");
exit;