<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/auth.php";
require_once __DIR__ . "/../../../config/bitacora.php";
$conexion = Connection();

if (!esAdministrador()) {
    die('No tienes permisos para hacer esto.');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    $stmtDatos = $conexion->prepare("SELECT descripcion FROM descuentos_edad WHERE id_descuento = ?");
    $stmtDatos->execute([$id]);
    $descuento = $stmtDatos->fetch();

    $stmt = $conexion->prepare("DELETE FROM descuentos_edad WHERE id_descuento = ?");
    $stmt->execute([$id]);

    if ($descuento) {
        registrar_actividad('configuracion', 'eliminó', "Eliminó el descuento '{$descuento['descripcion']}'");
    }
}

header("Location: ../../index.php?modulo=configuracion");
exit;
