<?php
include("conexion.php");

// recibir filtros
$estado = $_GET['estado'] ?? '';
$tipo   = $_GET['tipo'] ?? '';
$desde  = $_GET['fecha_desde'] ?? '';
$hasta  = $_GET['fecha_hasta'] ?? '';
$buscar = $_GET['buscar'] ?? '';

// consulta base
$sql = "SELECT * FROM reportes WHERE 1=1";

if($estado != '') $sql .= " AND estado='$estado'";
if($tipo != '')   $sql .= " AND tipo='$tipo'";
if($desde != '')  $sql .= " AND fecha >= '$desde'";
if($hasta != '')  $sql .= " AND fecha <= '$hasta'";
if($buscar != '') $sql .= " AND (usuario LIKE '%$buscar%' OR asunto LIKE '%$buscar%' OR descripcion LIKE '%$buscar%')";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reportes</title>
  <link rel="stylesheet" href="../assets/css/usuarios.css">
</head>
<body>
<div class="contenido">

  <div class="encabezado">
    <h1>Reportes</h1>
  </div>

  <div class="bloque-filtros">
    <!-- formulario de filtros -->
    <!-- (el que ya tienes, no lo repito para no hacer ruido) -->
  </div>

  <div class="div-table">
    <table class="table">
      <thead class="thead">
        <tr>
          <th>#</th>
          <th>ID</th>
          <th>Usuario</th>
          <th>Asunto</th>
          <th>Descripción</th>
          <th>Fecha</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody class="tbody">
        <?php 
        $contador = 1;
        while($row = mysqli_fetch_assoc($result)) { ?>
          <tr>
            <td><?php echo $contador++; ?></td>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['usuario']; ?></td>
            <td><?php echo $row['asunto']; ?></td>
            <td><?php echo $row['descripcion']; ?></td>
            <td><?php echo $row['fecha']; ?></td>
            <td><?php echo $row['estado']; ?></td>
            <td>
              <a href="editar.php?id=<?php echo $row['id']; ?>">✏️</a>
              <a href="eliminar.php?id=<?php echo $row['id']; ?>">🗑️</a>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

</div>
<script src="../assets/js/reportes.js"></script>
</body>
</html>
