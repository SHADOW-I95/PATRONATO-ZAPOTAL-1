<?php
include("../../conexion.php");
$conn = connection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dni         = $_POST['dni'];
    $usuario     = $_POST['usuario'];
    $asunto      = $_POST['asunto'];
    $descripcion = $_POST['descripcion'];
    $fecha       = $_POST['fecha'];
    $estado      = $_POST['estado'];

    $sql = "INSERT INTO reportes (dni, usuario, asunto, descripcion, fecha, estado) 
            VALUES ('$dni', '$usuario', '$asunto', '$descripcion', '$fecha', '$estado')";
    $query = mysqli_query($conn, $sql);

    if ($query) {
        header("Location: listado.php");
        exit;
    } else {
        echo "Error al insertar: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Reporte</title>
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/usuarios.css">
</head>
<body>
    <h2>Crear nuevo reporte</h2>
    <form method="POST">
        <input type="text" name="dni" placeholder="DNI" required>
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="text" name="asunto" placeholder="Asunto" required>
        <textarea name="descripcion" placeholder="Descripción"></textarea>
        <input type="date" name="fecha" required>
        <select name="estado">
            <option value="Pendiente">Pendiente</option>
            <option value="Resuelto">Resuelto</option>
        </select>
        <button type="submit">Guardar</button>
    </form>
</body>
</html>
