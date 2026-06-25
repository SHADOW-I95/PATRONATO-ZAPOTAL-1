<?php

$id = $_POST['ID'];
$correo = $_POST['CORREO'];
$contrasena = $_POST['CONTRASEÑA'];

$sql = "SELECT * FROM usuarios 
        WHERE ID = ? 
        AND CORREO = ? 
        AND CONTEASEÑA = ?";

$stmt = $conexion->prepare($sql);

$stmt->bind_param("iss", $id, $correo, $contrasena);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    echo "Datos correctos";
} else {
    echo "Datos incorrectos";
}
?>
