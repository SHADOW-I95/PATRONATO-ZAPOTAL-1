<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/auth.php";
$conexion = Connection();

header('Content-Type: application/json; charset=utf-8');

if (!esAdministrador()) {
    http_response_code(403);
    echo json_encode(["ok" => false, "error" => "sin_permiso"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["ok" => false, "error" => "metodo_invalido"]);
    exit;
}

$id_empleado = (int) ($_POST["id_empleado"] ?? 0);

if (!$id_empleado) {
    echo json_encode(["ok" => false, "error" => "empleado_invalido"]);
    exit;
}

$dni              = trim($_POST["DNI"] ?? '');
$codigo           = trim($_POST["codigo"] ?? '');
$nombre           = trim($_POST["nombre"] ?? '');
$apellido         = trim($_POST["apellido"] ?? '');
$fecha_nacimiento = !empty($_POST["fecha_nacimiento"]) ? $_POST["fecha_nacimiento"] : null;
$telefono         = !empty($_POST["telefono"]) ? $_POST["telefono"] : null;

$id_rol = (int) ($_POST["id_rol"] ?? 2);
if ($id_rol !== 3) {
    $id_rol = 2;
}

// =======================
// VALIDACIONES ANTES DE GUARDAR NADA
// =======================

$stmtDni = $conexion->prepare("SELECT COUNT(*) FROM empleados WHERE dni = ? AND id_empleado != ?");
$stmtDni->execute([$dni, $id_empleado]);
if ($stmtDni->fetchColumn() > 0) {
    echo json_encode(["ok" => false, "error" => "dni_duplicado"]);
    exit;
}

$stmtCodigo = $conexion->prepare("SELECT COUNT(*) FROM empleados WHERE codigo = ? AND id_empleado != ?");
$stmtCodigo->execute([$codigo, $id_empleado]);
if ($stmtCodigo->fetchColumn() > 0) {
    echo json_encode(["ok" => false, "error" => "codigo_duplicado"]);
    exit;
}

// Si se le está quitando el rol de Administrador a este empleado, hay que
// asegurarse de que quede al menos otro administrador en el sistema.
if ($id_rol !== 3) {
    $stmtOtrosAdmins = $conexion->prepare("SELECT COUNT(*) FROM empleados WHERE id_rol = 3 AND id_empleado != ?");
    $stmtOtrosAdmins->execute([$id_empleado]);
    if ($stmtOtrosAdmins->fetchColumn() == 0) {
        echo json_encode(["ok" => false, "error" => "sin_otro_administrador"]);
        exit;
    }
}

// =======================
// GUARDAR
// =======================

try {

    $sql = "UPDATE empleados SET
                dni = ?,
                codigo = ?,
                nombre = ?,
                apellido = ?,
                fecha_nacimiento = ?,
                telefono = ?,
                id_rol = ?
            WHERE id_empleado = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$dni, $codigo, $nombre, $apellido, $fecha_nacimiento, $telefono, $id_rol, $id_empleado]);

    echo json_encode(["ok" => true]);
    exit;

} catch (PDOException $e) {

    if ($e->getCode() == 23000) {
        echo json_encode(["ok" => false, "error" => "dato_duplicado"]);
        exit;
    }

    echo json_encode(["ok" => false, "error" => "error_guardando"]);
    exit;
}