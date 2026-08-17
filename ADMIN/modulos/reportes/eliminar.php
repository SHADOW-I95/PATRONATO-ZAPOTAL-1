<?php
require_once "../../config/conexion.php";
$conexion = Connection();

$id_reporte = (int) $_GET['id'];

try {
    $sql = "DELETE FROM reportes WHERE id_reporte = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_reporte]);

    header("Location: ../../index.php?modulo=reportes");
    exit;

} catch (PDOException $e) {
    echo "Error al eliminar: " . $e->getMessage();
}