<?php
require_once __DIR__ . "/../../../config/conexion.php";
=======
// Importa el archivo de conexión a la base de datos
require_once __DIR__ . "/../../config/conexion.php";
>>>>>>> 35d11e045d4aa1041ab2f2eb7ca48e68d60b7247
$conexion = Connection();

// Verifica que la petición sea POST, si no lo es redirige al módulo de reportes
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php?modulo=reportes");
    exit;
}

// Obtiene los valores enviados por el formulario
$id_usuario = $_POST['id_usuario'] ?? null;              // ID del usuario que crea el reporte
$id_tipo_reporte = $_POST['id_tipo_reporte'] ?? null;    // Tipo de reporte seleccionado
$descripcion_reporte = trim($_POST['descripcion_reporte'] ?? ''); // Descripción del reporte

// Validación básica: los tres campos son obligatorios
if (empty($id_usuario) || empty($id_tipo_reporte) || $descripcion_reporte === '') {
    die("Faltan datos para guardar el reporte.");
}

// Consulta SQL para insertar el nuevo reporte en la base de datos
$sql = "INSERT INTO reportes (id_usuario, id_tipo_reporte, descripcion_reporte)
        VALUES (:id_usuario, :id_tipo_reporte, :descripcion_reporte)";

// Prepara la consulta para evitar inyección SQL
$stmt = $conexion->prepare($sql);

// Asigna los valores a los parámetros de la consulta
$stmt->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);
$stmt->bindValue(":id_tipo_reporte", $id_tipo_reporte, PDO::PARAM_INT);
$stmt->bindValue(":descripcion_reporte", $descripcion_reporte);

// Ejecuta la consulta para guardar el reporte
$stmt->execute();

// Redirige al listado de reportes después de guardar
header("Location: ../../index.php?modulo=reportes");
exit;
