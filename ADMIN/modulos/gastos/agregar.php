<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/permisos.php";
require_once __DIR__ . "/../../../config/bitacora.php";

if (!tienePermiso('gastos')) {
    http_response_code(403);
    die("No tienes permiso para hacer esto.");
}

$conexion = Connection();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../../index.php?modulo=gastos");
    exit;
}

$concepto    = trim($_POST['concepto'] ?? '');
$categoria   = trim($_POST['categoria'] ?? '');
$monto       = filter_input(INPUT_POST, 'monto', FILTER_VALIDATE_FLOAT);
$fecha_gasto = $_POST['fecha_gasto'] ?? '';
$descripcion = trim($_POST['descripcion'] ?? '');

if ($concepto === '' || $categoria === '' || $monto === false || $monto === null || $monto <= 0 || $fecha_gasto === '') {
    header("Location: ../../index.php?modulo=gastos&error=datos_incompletos");
    exit;
}

// ===== Comprobante opcional =====
$comprobante_path = null;

if (!empty($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
    $archivo = $_FILES['comprobante'];

    if ($archivo['size'] > 8 * 1024 * 1024) {
        header("Location: ../../index.php?modulo=gastos&error=archivo_muy_grande");
        exit;
    }

    $tiposPermitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);

    if (!isset($tiposPermitidos[$mime])) {
        header("Location: ../../index.php?modulo=gastos&error=archivo_invalido");
        exit;
    }

    $extension = $tiposPermitidos[$mime];
    $nombreArchivo = 'gasto_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $rutaDestino = __DIR__ . '/../../../../SITIO/uploads/gastos/' . $nombreArchivo;

    if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        $comprobante_path = 'uploads/gastos/' . $nombreArchivo;
    }
}

try {
    $stmt = $conexion->prepare(
        "INSERT INTO gastos (id_empleado_registro, concepto, categoria, monto, fecha_gasto, descripcion, comprobante_path)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([(int) $_SESSION['id'], $concepto, $categoria, $monto, $fecha_gasto, $descripcion ?: null, $comprobante_path]);

    registrar_actividad('gastos', 'creó', "Registró el gasto '{$concepto}' ({$categoria}, L{$monto})");

    header("Location: ../../index.php?modulo=gastos&mensaje=guardado");
    exit;

} catch (PDOException $e) {
    header("Location: ../../index.php?modulo=gastos&error=error_guardando");
    exit;
}
