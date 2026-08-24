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

$descripcion     = trim($_POST['descripcion'] ?? '');
$edad_minima     = filter_input(INPUT_POST, 'edad_minima', FILTER_VALIDATE_INT);
$monto_descuento = filter_input(INPUT_POST, 'monto_descuento', FILTER_VALIDATE_FLOAT);

if ($descripcion === '' || $edad_minima === false || $edad_minima === null || $monto_descuento === false || $monto_descuento === null) {
    echo json_encode(["ok" => false, "error" => "datos_incompletos"]);
    exit;
}

if ($edad_minima < 0 || $monto_descuento < 0) {
    echo json_encode(["ok" => false, "error" => "datos_incompletos"]);
    exit;
}

try {
    $stmt = $conexion->prepare("INSERT INTO descuentos_edad (descripcion, edad_minima, monto_descuento) VALUES (?, ?, ?)");
    $stmt->execute([$descripcion, $edad_minima, $monto_descuento]);

    registrar_actividad('configuracion', 'creó', "Agregó el descuento '{$descripcion}' (desde {$edad_minima} años, L{$monto_descuento})");

    echo json_encode(["ok" => true]);
} catch (PDOException $e) {
    echo json_encode(["ok" => false, "error" => "error_guardando"]);
}
