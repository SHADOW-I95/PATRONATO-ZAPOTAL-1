<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/permisos.php";
require_once __DIR__ . "/../../../config/bitacora.php";

if (!tienePermiso('reportes')) {
    http_response_code(403);
    die("No tienes permiso para hacer esto.");
}

$conexion = Connection();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../../index.php?modulo=reportes");
    exit;
}

try {

    $id_reporte          = (int) $_POST["id_reporte"];
    $id_usuario          = $_POST["id_usuario"];
    $id_tipo_reporte     = $_POST["id_tipo_reporte"];
    $descripcion_reporte = trim($_POST["descripcion_reporte"]);

    $sql = "UPDATE reportes
            SET id_usuario = ?, id_tipo_reporte = ?, descripcion_reporte = ?
            WHERE id_reporte = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        $id_usuario,
        $id_tipo_reporte,
        $descripcion_reporte,
        $id_reporte
    ]);

    registrar_actividad('reportes', 'editó', "Editó el reporte #{$id_reporte}");

    header("Location: ../../index.php?modulo=reportes&mensaje=actualizado");
    exit;

} catch (PDOException $e) {
    die("Error al actualizar: " . $e->getMessage());
}