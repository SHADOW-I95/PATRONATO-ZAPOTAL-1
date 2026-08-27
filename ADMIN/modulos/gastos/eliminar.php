<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/permisos.php";
require_once __DIR__ . "/../../../config/bitacora.php";

if (!tienePermiso('gastos')) {
    http_response_code(403);
    die("No tienes permiso para hacer esto.");
}

$conexion = Connection();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    $stmtDatos = $conexion->prepare("SELECT concepto FROM gastos WHERE id_gasto = ?");
    $stmtDatos->execute([$id]);
    $gasto = $stmtDatos->fetch();

    $stmt = $conexion->prepare("DELETE FROM gastos WHERE id_gasto = ?");
    $stmt->execute([$id]);

    if ($gasto) {
        registrar_actividad('gastos', 'eliminó', "Eliminó el gasto '{$gasto['concepto']}'");
    }
}

header("Location: ../../index.php?modulo=gastos");
exit;
