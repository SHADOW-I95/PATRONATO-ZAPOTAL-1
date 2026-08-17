<?php
require_once __DIR__ . "/../../../config/conexion.php";
$conexion = Connection();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["ok" => false, "error" => "metodo_invalido"]);
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

$stmtDni = $conexion->prepare("SELECT COUNT(*) FROM empleados WHERE dni = ?");
$stmtDni->execute([$dni]);
if ($stmtDni->fetchColumn() > 0) {
    echo json_encode(["ok" => false, "error" => "dni_duplicado"]);
    exit;
}

$stmtCodigo = $conexion->prepare("SELECT COUNT(*) FROM empleados WHERE codigo = ?");
$stmtCodigo->execute([$codigo]);
if ($stmtCodigo->fetchColumn() > 0) {
    echo json_encode(["ok" => false, "error" => "codigo_duplicado"]);
    exit;
}

// =======================
// GUARDAR
// =======================

try {

    // id_rol = 2 ("Empleado"): este formulario es exclusivo para personal del
    // patronato, no hay selector de rol porque por ahora solo existe este.
    $sql = "INSERT INTO empleados (id_rol, dni, nombre, apellido, fecha_nacimiento, telefono, codigo)
            VALUES (2, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$dni, $nombre, $apellido, $fecha_nacimiento, $telefono, $codigo]);

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
