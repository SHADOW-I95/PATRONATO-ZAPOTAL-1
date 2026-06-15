<?php
$host = "localhost";      
$usuario = "root";        
$clave = "";              
$bd = "mi_base";          

$conexion = new mysqli($host, $usuario, $clave, $bd);

$conexion->set_charset("utf8");

if ($conexion->connect_error) {
    die("❌ Error de conexión: " . $conexion->connect_error);
} else {
    echo "✅ Conexión exitosa a la base de datos.";
}
?>
