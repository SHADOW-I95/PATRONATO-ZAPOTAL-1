<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/permisos.php";
require_once __DIR__ . "/../../../config/bitacora.php";

if (!tienePermiso('comunicados')) {
    http_response_code(403);
    die("No tienes permiso para hacer esto.");
}

$conexion = Connection();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../../index.php?modulo=comunicados");
    exit;
}

$titulo      = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

if ($titulo === '' || $descripcion === '') {
    header("Location: ../../index.php?modulo=comunicados&error=datos_incompletos");
    exit;
}

// ===== Imagen opcional =====
$imagen_path = null;

if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $archivo = $_FILES['imagen'];

    if ($archivo['size'] > 5 * 1024 * 1024) {
        header("Location: ../../index.php?modulo=comunicados&error=archivo_muy_grande");
        exit;
    }

    $tiposPermitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);

    if (!isset($tiposPermitidos[$mime])) {
        header("Location: ../../index.php?modulo=comunicados&error=archivo_invalido");
        exit;
    }

    $extension = $tiposPermitidos[$mime];
    $nombreArchivo = 'comunicado_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $rutaDestino = __DIR__ . '/../../../../SITIO/uploads/comunicados/' . $nombreArchivo;

    if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        $imagen_path = 'uploads/comunicados/' . $nombreArchivo;
    }
}

try {
    $stmt = $conexion->prepare(
        "INSERT INTO comunicados (id_empleado_publico, titulo, descripcion, imagen_path) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([(int) $_SESSION['id'], $titulo, $descripcion, $imagen_path]);

    registrar_actividad('comunicados', 'creó', "Publicó el comunicado '{$titulo}'");

    header("Location: ../../index.php?modulo=comunicados&mensaje=publicado");
    exit;

} catch (PDOException $e) {
    header("Location: ../../index.php?modulo=comunicados&error=error_guardando");
    exit;
}
