<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/permisos.php";
require_once __DIR__ . "/../../../config/bitacora.php";

if (!tienePermiso('reportes')) {
    http_response_code(403);
    die("No tienes permiso para hacer esto.");
}

$conexion = Connection();

$id_reporte = (int) $_GET['id'];

try {
    $sql = "DELETE FROM reportes WHERE id_reporte = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_reporte]);

    registrar_actividad('reportes', 'eliminó', "Eliminó el reporte #{$id_reporte}");

    header("Location: ../../index.php?modulo=reportes");
    exit;

} catch (PDOException $e) {
    echo "Error al eliminar: " . $e->getMessage();
}