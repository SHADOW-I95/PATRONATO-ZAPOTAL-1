<?php
include("../../conexion.php");
$conn = connection();

$dni = $_GET['dni'];
$sql = "SELECT * FROM reportes WHERE dni='$dni'";
$query = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario     = $_POST['usuario'];
    $asunto      = $_POST['asunto'];
    $descripcion = $_POST['descripcion'];
    $fecha       = $_POST['fecha'];
    $estado      = $_POST['estado'];

    $sqlUpdate = "UPDATE reportes SET usuario='$usuario', asunto='$asunto',
                 descripcion='$descripcion', fecha='$fecha', estado='$estado'
                 WHERE dni='$dni'";
    $queryUpdate = mysqli_query($conn, $sqlUpdate);

    if ($queryUpdate) {
        header("Location: listado.php");
        exit;
    } else {
        echo "Error al actualizar: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Reporte</title>
    <link rel="stylesheet" href="../assets/css/global.css">
</head>
<body>
    <h2>Editar reporte</h2>
    <form method="POST">
        <input type="text" name="usuario" value="<?php echo $row['usuario']; ?>" required>
        <input type="text" name="asunto" value="<?php echo $row['asunto']; ?>" required>
        <textarea name="descripcion"><?php echo $row['descripcion']; ?></textarea>
        <input type="date" name="fecha" value="<?php echo $row['fecha']; ?>" required>
        <select name="estado">
            <option value="EN PROCESO" <?php if($row['estado']=="EN PROCESO") echo "selected"; ?>>En Proceso</option>
            <option value="FINALIZADO" <?php if($row['estado']=="FINALIZADO") echo "selected"; ?>>Finalizado</option>
        </select>
        <button type="submit">Actualizar</button>
    </form>
</body>
</html>
