<?php
<<<<<<< HEAD:Usuarios/pages/incio_seccion/CONEXION.PHP
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "patronato";          
$conexion = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
=======
$host = "localhost";      
$usuario = "root";        
$clave = "";              
$bd = "patronato";          

$conexion = new mysqli($host, $usuario, $clave, $bd);
>>>>>>> 30b2d88f1b2bf8dda3174a6da6e6e2efcc768347:Empleados/conexion.php
$conexion->set_charset("utf8");

if ($conexion->connect_error) {
    die("❌ Error de conexión: " . $conexion->connect_error);
} else {
    echo "✅ Conexión exitosa a la base de datos.";
}
?>