<?php
require_once __DIR__ . "/../../../config/conexion.php";
$conexion = Connection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php?modulo=reportes");
    exit;
}

$id_usuario = $_POST['id_usuario'] ?? null;
$id_tipo_reporte = $_POST['id_tipo_reporte'] ?? null;
$descripcion_reporte = trim($_POST['descripcion_reporte'] ?? '');

// Validación básica: los tres campos son obligatorios
if (empty($id_usuario) || empty($id_tipo_reporte) || $descripcion_reporte === '') {
    die("Faltan datos para guardar el reporte.");
}

$sql = "INSERT INTO reportes (id_usuario, id_tipo_reporte, descripcion_reporte)
        VALUES (:id_usuario, :id_tipo_reporte, :descripcion_reporte)";

$stmt = $conexion->prepare($sql);
$stmt->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);
$stmt->bindValue(":id_tipo_reporte", $id_tipo_reporte, PDO::PARAM_INT);
$stmt->bindValue(":descripcion_reporte", $descripcion_reporte);
$stmt->execute();

// Vuelve al listado de reportes después de guardar
header("Location: ../../index.php?modulo=reportes");
exit;