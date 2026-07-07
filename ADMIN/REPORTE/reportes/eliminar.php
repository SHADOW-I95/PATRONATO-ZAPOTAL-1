<?php
include("../../conexion.php"); // ajusta la ruta según dónde tengas tu conexion.php
$conn = connection();

$dni = $_GET['dni']; // recibe el dni desde la URL

$sql = "DELETE FROM reportes WHERE dni='$dni'";
$query = mysqli_query($conn, $sql);

if ($query) {
    header("Location: listado.php"); // vuelve al listado después de eliminar
    exit;
} else {
    echo "Error al eliminar: " . mysqli_error($conn);
}
?>
