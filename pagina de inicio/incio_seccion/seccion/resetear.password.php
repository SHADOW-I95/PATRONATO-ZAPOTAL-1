<?php
require_once __DIR__ . '/../../../ADMIN/conexion.php';
$conexion = connection();

$id_usuario       = 1;        // ID_USUARIO 
$nueva_contrasena = "12345";   // la contraseña que quieres usar para entrar

$id_usuario       = 2;        // ID_USUARIO 
$nueva_contrasena = "56789";

$id_usuario       = 3;        // ID_USUARIO 
$nueva_contrasena = "91011";

$hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

$stmt = $conexion->prepare("UPDATE usuarios SET CONTRASENA = ? WHERE ID_USUARIO = ?");
$stmt->bind_param("si", $hash, $id_usuario);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Contraseña actualizada correctamente para ID_USUARIO {$id_usuario}.<br>";
    echo "Ahora puedes iniciar sesión usando la contraseña: <b>{$nueva_contrasena}</b><br>";
    echo "IMPORTANTE: borra este archivo del servidor ahora mismo.";
} else {
    echo "No se encontró ningún usuario con ID_USUARIO = {$id_usuario}. Verifica el ID.";
}

$stmt->close();
$conexion->close();