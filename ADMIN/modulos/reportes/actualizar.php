<?php
require_once "../../config/conexion.php";
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

    header("Location: ../../index.php?modulo=reportes&mensaje=actualizado");
    exit;

} catch (PDOException $e) {
    die("Error al actualizar: " . $e->getMessage());
}