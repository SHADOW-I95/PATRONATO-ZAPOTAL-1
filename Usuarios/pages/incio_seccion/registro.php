<?php

include("Usuarios/pages/incio_seccion/CONEXION.PHP");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $ID        = $_POST['ID'];
    $NOMBRES   = $_POST['NOMBRES'];
    $APELLIDOS  = $_POST['APELLIDOS'];
    $CORREO    = $_POST['CORREO'];
    $NO_TELEFONO  = $_POST['NO_TELEFONO'];
    $CONTRASEÑA = $_POST['CONTRASEÑA'];
    $CONFIRMAR  = $_POST['CONFIRMAR'];

    
    if ($CONTRASEÑA !== $CONFIRMAR) {
        echo "❌ Las contraseñas no coinciden.";
        exit;
    }

   
    $hash = password_hash($CONTRASEÑA, PASSWORD_DEFAULT);

   
    $sql = "INSERT INTO usuario (NOMBRES, APELLIDOS, DNI, CORREO, NO_TELEFONO, CONTRASEÑA) 
            VALUES ('$ID','$NOMBRES','$APELLIDOS','$CORREO', '$NO_TELEFONO',$CONTRASEÑA')";

   
    if ($conexion->query($sql) === TRUE) {
        echo "✅ Registro exitoso. Bienvenido, $NOMBRES.";
        
    } else {
        echo "❌ Error al registrar: " . $conexion->error;
    }
}
?>
