<?php
include("../CONEXION.PHP");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $CORREO     = $_POST['correo'];
    $CONTRASEÑA = $_POST['contrasena'];


    $sql = "SELECT * FROM usuario WHERE CORREO='$CORREO'";
    $resultado = $conexion->query($sql);

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();


        if (password_verify($CONTRASEÑA, $fila['CONTRASEÑA'])) {
            echo "✅ Bienvenido " . $fila['NOMBRES'];
        
        } else {
            echo "❌ Contraseña incorrecta";
        }
    } else {
        echo "❌ Usuario no encontrado";
    }
}
?>
