<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!esUsuarioComun()) {
    http_response_code(403);
    echo json_encode(["ok" => false, "error" => "sin_permiso"]);
    exit;
}

if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["ok" => false, "error" => "archivo_invalido"]);
    exit;
}

$archivo = $_FILES['foto'];

if ($archivo['size'] > 5 * 1024 * 1024) {
    echo json_encode(["ok" => false, "error" => "archivo_muy_grande"]);
    exit;
}

$tiposPermitidos = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $archivo['tmp_name']);
finfo_close($finfo);

if (!isset($tiposPermitidos[$mime])) {
    echo json_encode(["ok" => false, "error" => "archivo_invalido"]);
    exit;
}

$conexion = Connection();
$id_usuario = (int) $_SESSION['id'];
$extension = $tiposPermitidos[$mime];

// Nombre generado por el servidor, nunca el que mandó el navegador
$nombreArchivo = 'perfil_' . $id_usuario . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
$rutaDestino = __DIR__ . '/../uploads/perfiles/' . $nombreArchivo;

// Si ya tenía una foto anterior, se borra para no ir acumulando archivos
$stmtActual = $conexion->prepare("SELECT foto_perfil FROM usuarios WHERE id_usuario = ?");
$stmtActual->execute([$id_usuario]);
$fotoAnterior = $stmtActual->fetchColumn();

if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
    echo json_encode(["ok" => false, "error" => "error_guardando"]);
    exit;
}

$rutaRelativa = 'uploads/perfiles/' . $nombreArchivo;

try {
    $stmt = $conexion->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id_usuario = ?");
    $stmt->execute([$rutaRelativa, $id_usuario]);

    if ($fotoAnterior && file_exists(__DIR__ . '/../' . $fotoAnterior)) {
        @unlink(__DIR__ . '/../' . $fotoAnterior);
    }

    echo json_encode(["ok" => true, "ruta" => '../' . $rutaRelativa]);
} catch (PDOException $e) {
    echo json_encode(["ok" => false, "error" => "error_guardando"]);
}
