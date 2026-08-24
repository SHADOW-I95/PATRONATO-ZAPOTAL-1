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

$nombre_rol = trim($_POST['nombre_rol'] ?? '');

if ($nombre_rol === '') {
    echo json_encode(["ok" => false, "error" => "nombre_vacio"]);
    exit;
}

try {
    $stmt = $conexion->prepare("INSERT INTO roles (nombre_rol) VALUES (?)");
    $stmt->execute([$nombre_rol]);

    registrar_actividad('configuracion', 'creó', "Creó el rol '{$nombre_rol}'");

    echo json_encode(["ok" => true]);
} catch (PDOException $e) {
    echo json_encode(["ok" => false, "error" => "error_guardando"]);
}