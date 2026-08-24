<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/auth.php";
require_once __DIR__ . "/../../../config/bitacora.php";
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

$dni              = trim($_POST["DNI"] ?? '');
$codigo           = trim($_POST["codigo"] ?? '');
$nombre           = trim($_POST["nombre"] ?? '');
$apellido         = trim($_POST["apellido"] ?? '');
$fecha_nacimiento = !empty($_POST["fecha_nacimiento"]) ? $_POST["fecha_nacimiento"] : null;
$telefono         = !empty($_POST["telefono"]) ? $_POST["telefono"] : null;

// El rol debe ser uno que realmente exista en la tabla `roles` (ya no son
// solo 2 y 3 fijos: desde Configuración se pueden crear roles nuevos).
// Si mandan algo que no existe, se cae a Cobrador (id 2) como respaldo.
$id_rol = (int) ($_POST["id_rol"] ?? 2);
$stmtRolValido = $conexion->prepare("SELECT COUNT(*) FROM roles WHERE id_roles = ?");
$stmtRolValido->execute([$id_rol]);
if ($stmtRolValido->fetchColumn() == 0) {
    $id_rol = 2;
}

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

    $sql = "INSERT INTO empleados (id_rol, dni, nombre, apellido, fecha_nacimiento, telefono, codigo)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_rol, $dni, $nombre, $apellido, $fecha_nacimiento, $telefono, $codigo]);

    registrar_actividad('empleados', 'creó', "Registró al empleado {$nombre} {$apellido} (DNI {$dni})");

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