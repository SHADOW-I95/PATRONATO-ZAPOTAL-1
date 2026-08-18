<?php
require_once __DIR__ . "/../../../config/conexion.php";
$conexion = Connection();

header('Content-Type: application/json; charset=utf-8');

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
                telefono = ?
            WHERE id_empleado = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$dni, $codigo, $nombre, $apellido, $fecha_nacimiento, $telefono, $id_empleado]);

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