<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/vinculacion.php';

header('Content-Type: application/json; charset=utf-8');

if (!haySesion()) {
    http_response_code(403);
    echo json_encode(["ok" => false, "error" => "sin_permiso"]);
    exit;
}

$conexion = Connection();
$id_usuario = resolverIdUsuarioParaPerfil($conexion);

if (!$id_usuario) {
    http_response_code(403);
    echo json_encode(["ok" => false, "error" => "sin_permiso"]);
    exit;
}

$id_vivienda        = (int) ($_POST['id_vivienda'] ?? 0);
$motivo             = trim($_POST['motivo'] ?? 'Venta');
$nombre_comprador   = trim($_POST['nombre_comprador'] ?? '');
$apellido_comprador = trim($_POST['apellido_comprador'] ?? '');
$dni_comprador      = trim($_POST['dni_comprador'] ?? '');
$telefono_comprador = trim($_POST['telefono_comprador'] ?? '');

if (!$id_vivienda || $nombre_comprador === '' || $apellido_comprador === '' || $dni_comprador === '') {
    echo json_encode(["ok" => false, "error" => "datos_incompletos"]);
    exit;
}

// La vivienda debe ser realmente del usuario en sesión
$stmtVivienda = $conexion->prepare("SELECT id_vivienda FROM viviendas WHERE id_vivienda = ? AND id_usuario = ?");
$stmtVivienda->execute([$id_vivienda, $id_usuario]);
if (!$stmtVivienda->fetch()) {
    echo json_encode(["ok" => false, "error" => "vivienda_no_valida"]);
    exit;
}

// No se permite otra solicitud mientras haya una "En revisión" para esta vivienda
$stmtActiva = $conexion->prepare("SELECT COUNT(*) FROM solicitudes_traspaso WHERE id_vivienda = ? AND id_estado_solicitud = 1");
$stmtActiva->execute([$id_vivienda]);
if ($stmtActiva->fetchColumn() > 0) {
    echo json_encode(["ok" => false, "error" => "ya_tiene_solicitud"]);
    exit;
}

try {
    $stmt = $conexion->prepare(
        "INSERT INTO solicitudes_traspaso
            (id_vivienda, id_usuario_actual, nombre_comprador, apellido_comprador, dni_comprador, telefono_comprador, motivo)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$id_vivienda, $id_usuario, $nombre_comprador, $apellido_comprador, $dni_comprador, $telefono_comprador ?: null, $motivo]);

    echo json_encode(["ok" => true]);
} catch (PDOException $e) {
    echo json_encode(["ok" => false, "error" => "error_guardando"]);
}
