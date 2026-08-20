<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/auth.php";
$conexion = Connection();

header('Content-Type: application/json; charset=utf-8');

if (!esEmpleado()) {
    http_response_code(403);
    echo json_encode(["ok" => false, "error" => "sin_permiso"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["ok" => false, "error" => "metodo_invalido"]);
    exit;
}

// Un empleado solo puede editar SU PROPIA cuenta: el id sale de la sesión,
// nunca de lo que mande el formulario, para que nadie pueda editar la
// cuenta de otro empleado cambiando un id en la petición.
$id_empleado = (int) ($_SESSION['id'] ?? 0);

if (!$id_empleado) {
    echo json_encode(["ok" => false, "error" => "sesion_invalida"]);
    exit;
}

$codigo   = trim($_POST["codigo"] ?? '');
$telefono = !empty($_POST["telefono"]) ? $_POST["telefono"] : null;

if ($codigo === '') {
    echo json_encode(["ok" => false, "error" => "codigo_vacio"]);
    exit;
}

// El código debe seguir siendo único entre todos los empleados
$stmtCodigo = $conexion->prepare("SELECT COUNT(*) FROM empleados WHERE codigo = ? AND id_empleado != ?");
$stmtCodigo->execute([$codigo, $id_empleado]);
if ($stmtCodigo->fetchColumn() > 0) {
    echo json_encode(["ok" => false, "error" => "codigo_duplicado"]);
    exit;
}

try {
    $sql = "UPDATE empleados SET codigo = ?, telefono = ? WHERE id_empleado = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$codigo, $telefono, $id_empleado]);

    echo json_encode(["ok" => true]);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(["ok" => false, "error" => "codigo_duplicado"]);
        exit;
    }
    echo json_encode(["ok" => false, "error" => "error_guardando"]);
}