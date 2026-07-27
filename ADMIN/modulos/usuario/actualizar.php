<?php
require_once("../../config/conexion.php");
$conexion = connection();


require_once("../../config/conexion.php");
$id=$_POST['id_usuario'];
$dni=$_POST['dni'];
$nombre=$_POST['nombre'];
$apellido=$_POST['apellido'];
$telefono=$_POST['telefono'];
$sql="
UPDATE usuarios
SET
dni=?,
nombre=?,
apellido=?,
telefono=?

WHERE id_usuario=?

";

$stmt=$conexion->prepare($sql);


$stmt->execute([

$dni,
$nombre,
$apellido,
$telefono,
$id

]);
header("Location: ../../index.php?modulo=usuario");
exit;
