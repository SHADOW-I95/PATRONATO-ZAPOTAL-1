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

$catalogos = [
    'sector'       => ['tabla' => 'sectores',     'columna' => 'nombre_sector',  'id' => 'id_sector'],
    'servicio'     => ['tabla' => 'servicios',    'columna' => 'nombre_servicio', 'id' => 'id_servicio'],
    'tipo_reporte' => ['tabla' => 'tipo_reporte', 'columna' => 'tipo_reporte',    'id' => 'id_tipo_reporte'],
];

$tipo   = $_POST['tipo'] ?? '';
$id     = (int) ($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');

if (!isset($catalogos[$tipo])) {
    echo json_encode(["ok" => false, "error" => "tipo_invalido"]);
    exit;
}

if (!$id) {
    echo json_encode(["ok" => false, "error" => "id_invalido"]);
    exit;
}

if ($nombre === '') {
    echo json_encode(["ok" => false, "error" => "nombre_vacio"]);
    exit;
}

$tabla   = $catalogos[$tipo]['tabla'];
$columna = $catalogos[$tipo]['columna'];
$idCol   = $catalogos[$tipo]['id'];

try {
    $sql = "UPDATE `$tabla` SET `$columna` = ? WHERE `$idCol` = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$nombre, $id]);

    registrar_actividad('configuracion', 'editó', "Renombró un valor del catálogo de {$tipo} a '{$nombre}'");

    echo json_encode(["ok" => true]);
} catch (PDOException $e) {
    echo json_encode(["ok" => false, "error" => "error_guardando"]);
}