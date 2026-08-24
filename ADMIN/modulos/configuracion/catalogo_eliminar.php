<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/auth.php";
require_once __DIR__ . "/../../../config/bitacora.php";
$conexion = Connection();

if (!esAdministrador()) {
    die('No tienes permisos para modificar el catálogo.');
}

$catalogos = [
    'sector'       => ['tabla' => 'sectores',     'id' => 'id_sector'],
    'servicio'     => ['tabla' => 'servicios',    'id' => 'id_servicio'],
    'tipo_reporte' => ['tabla' => 'tipo_reporte', 'id' => 'id_tipo_reporte'],
];

$tipo = $_GET['tipo'] ?? '';
$id   = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (isset($catalogos[$tipo]) && $id) {
    $tabla = $catalogos[$tipo]['tabla'];
    $idCol = $catalogos[$tipo]['id'];

    try {
        $stmt = $conexion->prepare("DELETE FROM `$tabla` WHERE `$idCol` = ?");
        $stmt->execute([$id]);

        registrar_actividad('configuracion', 'eliminó', "Eliminó el valor #{$id} del catálogo de {$tipo}");
    } catch (PDOException $e) {
        // Error 23000 = viola una llave foránea: significa que ese sector,
        // servicio o tipo de reporte todavía está siendo usado por alguna
        // vivienda o reporte, así que no se puede borrar.
        if ($e->getCode() == 23000) {
            header("Location: ../../index.php?modulo=configuracion&error=en_uso");
            exit;
        }
        header("Location: ../../index.php?modulo=configuracion&error=error_eliminando");
        exit;
    }
}

header("Location: ../../index.php?modulo=configuracion");
exit;