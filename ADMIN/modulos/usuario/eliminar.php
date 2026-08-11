<?php

require_once "../../config/conexion.php";
$conexion = connection();



$id = $_GET['id'];


try {

    $conexion->beginTransaction();


    // Eliminar viviendas del usuario
    $sql = "DELETE FROM viviendas 
            WHERE id_usuario = ?";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([$id]);


    // Eliminar usuario
    $sql = "DELETE FROM usuarios
            WHERE id_usuario = ?";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([$id]);


    $conexion->commit();


header("Location: ../../index.php?modulo=usuario");
exit;


} catch(Exception $e) {


    $conexion->rollBack();

    echo "Error: ".$e->getMessage();

}