<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/vinculacion.php';

header('Content-Type: application/json; charset=utf-8');

if (!haySesion()) {
    http_response_code(403);
    echo json_encode(["ok" => false, "error" => "sesion_invalida"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["ok" => false, "error" => "error_guardando"]);
    exit;
}

$conexion = Connection();

// Puede ser un usuario común, o un empleado cambiando el código de su
// propio perfil de vecino (vinculado por DNI). Nunca sale del formulario:
// así nadie puede cambiar el código de otra persona.
$id_usuario = resolverIdUsuarioParaPerfil($conexion);

$codigo_actual = trim($_POST['codigo_actual'] ?? '');
$codigo_nuevo  = trim($_POST['codigo_nuevo'] ?? '');

if (!$id_usuario) {
    echo json_encode(["ok" => false, "error" => "sesion_invalida"]);
    exit;
}

if (strlen($codigo_nuevo) < 4) {
    echo json_encode(["ok" => false, "error" => "codigo_muy_corto"]);
    exit;
}

// Verificar que el código actual sea correcto antes de dejarlo cambiarlo
$stmt = $conexion->prepare("SELECT codigo FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
$fila = $stmt->fetch();

if (!$fila || $fila['codigo'] !== $codigo_actual) {
    echo json_encode(["ok" => false, "error" => "codigo_actual_incorrecto"]);
    exit;
}

// El código nuevo debe seguir siendo único entre todos los usuarios
$stmtDup = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE codigo = ? AND id_usuario != ?");
$stmtDup->execute([$codigo_nuevo, $id_usuario]);
if ($stmtDup->fetchColumn() > 0) {
    echo json_encode(["ok" => false, "error" => "codigo_duplicado"]);
    exit;
}

try {
    $stmtUpdate = $conexion->prepare("UPDATE usuarios SET codigo = ? WHERE id_usuario = ?");
    $stmtUpdate->execute([$codigo_nuevo, $id_usuario]);

    echo json_encode(["ok" => true]);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(["ok" => false, "error" => "codigo_duplicado"]);
        exit;
    }
    echo json_encode(["ok" => false, "error" => "error_guardando"]);
}