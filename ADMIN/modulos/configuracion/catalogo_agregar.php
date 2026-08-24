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

// Mapa de qué tabla/columna corresponde a cada tipo de catálogo.
// Así solo se permiten estos 3 valores concretos, nunca un nombre de tabla
// que venga directo del formulario.
$catalogos = [
    'sector'       => ['tabla' => 'sectores',     'columna' => 'nombre_sector'],
    'servicio'     => ['tabla' => 'servicios',    'columna' => 'nombre_servicio'],
    'tipo_reporte' => ['tabla' => 'tipo_reporte', 'columna' => 'tipo_reporte'],
];

$tipo = $_POST['tipo'] ?? '';
$nombre = trim($_POST['nombre'] ?? '');

if (!isset($catalogos[$tipo])) {
    echo json_encode(["ok" => false, "error" => "tipo_invalido"]);
    exit;
}

if ($nombre === '') {
    echo json_encode(["ok" => false, "error" => "nombre_vacio"]);
    exit;
}

$tabla   = $catalogos[$tipo]['tabla'];
$columna = $catalogos[$tipo]['columna'];

try {
    $sql = "INSERT INTO `$tabla` (`$columna`) VALUES (?)";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$nombre]);

    registrar_actividad('configuracion', 'creó', "Agregó '{$nombre}' al catálogo de {$tipo}");

    echo json_encode(["ok" => true]);
} catch (PDOException $e) {
    echo json_encode(["ok" => false, "error" => "error_guardando"]);
}