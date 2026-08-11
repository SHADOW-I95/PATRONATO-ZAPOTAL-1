<?php

function Connection()
{
    $host = "localhost";
    $dbname = "patronato-proyect";
    $user = "root";
    $pass = "";

    try {
        $conexion = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8",
            $user,
            $pass
        );

        // Mostrar errores de SQL
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Obtener resultados como arreglos asociativos
        $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $conexion;

    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}