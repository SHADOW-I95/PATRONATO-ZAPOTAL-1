<?php

require_once("../../config/conexion.php");

$conexion = Connection();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../index.php");
    exit;
}

try {

    $conexion->beginTransaction();

    // =======================
    // DATOS DEL USUARIO
    // =======================

    $dni               = trim($_POST["DNI"]);
    $codigo            = trim($_POST["contrasena"]);
    $nombre            = trim($_POST["nombre"]);
    $apellido          = trim($_POST["apellido"]);
    $fecha_nacimiento  = !empty($_POST["fecha_nacimiento"]) ? $_POST["fecha_nacimiento"] : null;
    $telefono          = !empty($_POST["telefono"]) ? $_POST["telefono"] : null;

    $sqlUsuario = "
        INSERT INTO usuarios
        (
            dni,
            nombre,
            apellido,
            fecha_nacimiento,
            telefono,
            codigo
        )
        VALUES
        (
            dni, nombre, apellido, fecha_nacimiento, telefono, codigo
        )
    ";

    $stmtUsuario = $conexion->prepare($sqlUsuario);

    $stmtUsuario->execute([
        $dni,
        $nombre,
        $apellido,
        $fecha_nacimiento,
        $telefono,
        $codigo
    ]);

    // Obtener el ID del usuario recién creado
    $id_usuario = $conexion->lastInsertId();

    // =======================
    // GUARDAR VIVIENDAS
    // =======================

    if (!empty($_POST["vivienda"])) {

        $sqlVivienda = "
            INSERT INTO viviendas
            (
                id_usuario,
                id_sector,
                id_servicio,
                numero_vivienda,
                cuota,
                estado
            )
            VALUES
            (
                id_usuario,id_sector, id_servicio, numero_vivienda, cuota, estado 
            )
        ";

        $stmtVivienda = $conexion->prepare($sqlVivienda);

        foreach ($_POST["vivienda"] as $v) {

            $stmtVivienda->execute([
                $id_usuario,
                $v["sector"],
                $v["servicio"],
                $v["numero"],
                $v["cuota"],
                $v["estado"]
            ]);
        }
    }

    $conexion->commit();

    header("Location: ../../index.php?modulo=usuario&mensaje=guardado");
exit;


} catch (PDOException $e) {

    $conexion->rollBack();

    die("Error al guardar: " . $e->getMessage());

}