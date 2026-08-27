<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/permisos.php";
require_once __DIR__ . "/../../../config/bitacora.php";

if (!tienePermiso('comunicados')) {
    http_response_code(403);
    die("No tienes permiso para hacer esto.");
}

$conexion = Connection();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    $stmtDatos = $conexion->prepare("SELECT titulo FROM comunicados WHERE id_comunicado = ?");
    $stmtDatos->execute([$id]);
    $comunicado = $stmtDatos->fetch();

    $stmt = $conexion->prepare("DELETE FROM comunicados WHERE id_comunicado = ?");
    $stmt->execute([$id]);

    if ($comunicado) {
        registrar_actividad('comunicados', 'eliminó', "Eliminó el comunicado '{$comunicado['titulo']}'");
    }
}

header("Location: ../../index.php?modulo=comunicados");
exit;
