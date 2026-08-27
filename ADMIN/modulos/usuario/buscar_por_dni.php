<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/permisos.php";

header('Content-Type: application/json; charset=utf-8');

if (!tienePermiso('usuario')) {
    http_response_code(403);
    echo json_encode(["existe" => false]);
    exit;
}

$conexion = Connection();
$dni = trim($_GET['dni'] ?? '');

$stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE dni = ?");
$stmt->execute([$dni]);
$id = $stmt->fetchColumn();

echo json_encode(["existe" => (bool) $id, "id_usuario" => $id ?: null]);
