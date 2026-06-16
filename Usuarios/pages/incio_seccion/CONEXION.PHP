<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "patronato";          

$conexion = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
$conexion->set_charset("utf8");

if ($conexion->connect_error) {
    die("❌ Error de conexión: " . $conexion->connect_error);
} else {
    echo "✅ Conexión exitosa a la base de datos.";
}
?>
