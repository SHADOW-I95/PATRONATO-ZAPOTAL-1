<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/auth.php";
$conexion = Connection();

if (!esAdministrador()) {
    die('No tienes permisos para eliminar empleados.');
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($id) {

    // No dejar que se elimine al último administrador del sistema
    $stmt = $conexion->prepare("SELECT id_rol FROM empleados WHERE id_empleado = ?");
    $stmt->execute([$id]);
    $empleado = $stmt->fetch();

    if ($empleado && (int) $empleado['id_rol'] === 3) {
        $stmtOtrosAdmins = $conexion->prepare("SELECT COUNT(*) FROM empleados WHERE id_rol = 3 AND id_empleado != ?");
        $stmtOtrosAdmins->execute([$id]);
        if ($stmtOtrosAdmins->fetchColumn() == 0) {
            die('No puedes eliminar al único administrador del sistema. Asigna el rol de Administrador a otro empleado primero.');
        }
    }

    $stmtEliminar = $conexion->prepare("DELETE FROM empleados WHERE id_empleado = ?");
    $stmtEliminar->execute([$id]);
}

header("Location: ../../index.php?modulo=empleados");
exit;