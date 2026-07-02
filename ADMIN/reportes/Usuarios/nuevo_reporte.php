<?php
include("conexion.php");

if($_SERVER["REQUEST_METHOD"] == "POST"){
  $usuario = $_POST["usuario"];
  $asunto = $_POST["asunto"];
  $descripcion = $_POST["descripcion"];
  $estado = $_POST["estado"];
  $fecha = date("Y-m-d");

  $sql = "INSERT INTO reportes (usuario, asunto, descripcion, fecha, estado)
          VALUES ('$usuario','$asunto','$descripcion','$fecha','$estado')";
  mysqli_query($conn, $sql);

  header("Location: reportes.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nuevo Reporte</title>
</head>
<body>
  <h2>Crear Nuevo Reporte</h2>
  <form method="POST" action="nuevo_reporte.php">
    <label>Usuario:</label>
    <input type="text" name="usuario" required>
    <label>Asunto:</label>
    <input type="text" name="asunto" required>
    <label>Descripción:</label>
    <textarea name="descripcion" required></textarea>
    <label>Estado:</label>
    <select name="estado">
      <option value="EN PROCESO">En Proceso</option>
      <option value="FINALIZADO">Finalizado</option>
    </select>
    <button type="submit">Guardar</button>
  </form>
</body>
</html>
