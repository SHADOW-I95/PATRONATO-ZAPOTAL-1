<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/vinculacion.php';
require_once __DIR__ . '/../../ADMIN/modulos/agua/helpers_agua.php';

header('Content-Type: application/json; charset=utf-8');

if (!haySesion()) {
    http_response_code(403);
    echo json_encode(["ok" => false, "error" => "sin_permiso"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["ok" => false, "error" => "datos_incompletos"]);
    exit;
}

$conexion = Connection();

// Puede ser un usuario común, o un empleado viendo su propio perfil de
// vecino (vinculado por DNI) — en ambos casos se resuelve al mismo
// id_usuario real de la tabla `usuarios`.
$id_usuario = resolverIdUsuarioParaPerfil($conexion);

if (!$id_usuario) {
    http_response_code(403);
    echo json_encode(["ok" => false, "error" => "sin_permiso"]);
    exit;
}

$id_vivienda        = (int) ($_POST['id_vivienda'] ?? 0);
$cantidad_meses     = (int) ($_POST['cantidad_meses'] ?? 0);
$codigo_referencia  = trim($_POST['codigo_referencia'] ?? '');

if (!$id_vivienda || !$cantidad_meses || !$codigo_referencia) {
    echo json_encode(["ok" => false, "error" => "datos_incompletos"]);
    exit;
}

// La vivienda debe ser realmente del usuario que está en sesión
$stmtVivienda = $conexion->prepare("SELECT id_vivienda, cuota FROM viviendas WHERE id_vivienda = ? AND id_usuario = ?");
$stmtVivienda->execute([$id_vivienda, $id_usuario]);
$vivienda = $stmtVivienda->fetch();

if (!$vivienda) {
    echo json_encode(["ok" => false, "error" => "vivienda_no_valida"]);
    exit;
}

// No se permite una segunda solicitud mientras haya una "En revisión"
$stmtActiva = $conexion->prepare("SELECT COUNT(*) FROM solicitudes_pago WHERE id_vivienda = ? AND id_estado_solicitud = 1");
$stmtActiva->execute([$id_vivienda]);
if ($stmtActiva->fetchColumn() > 0) {
    echo json_encode(["ok" => false, "error" => "ya_tiene_solicitud"]);
    exit;
}

// Los meses pendientes se recalculan en el servidor (nunca se confía en
// lo que mande el navegador para saber cuáles meses son)
$mesesPendientes = obtener_meses_pendientes($conexion, $id_vivienda);
if ($cantidad_meses > count($mesesPendientes)) {
    $cantidad_meses = count($mesesPendientes);
}
if ($cantidad_meses < 1) {
    echo json_encode(["ok" => false, "error" => "datos_incompletos"]);
    exit;
}
$mesesAPagar = array_slice($mesesPendientes, 0, $cantidad_meses);
$monto_declarado = round($cantidad_meses * obtener_cuota_efectiva($conexion, $id_vivienda), 2);

// ===== Validar y guardar el archivo =====
if (empty($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["ok" => false, "error" => "datos_incompletos"]);
    exit;
}

$archivo = $_FILES['comprobante'];

if ($archivo['size'] > 8 * 1024 * 1024) {
    echo json_encode(["ok" => false, "error" => "archivo_muy_grande"]);
    exit;
}

$tiposPermitidos = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'application/pdf' => 'pdf',
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $archivo['tmp_name']);
finfo_close($finfo);

if (!isset($tiposPermitidos[$mime])) {
    echo json_encode(["ok" => false, "error" => "archivo_invalido"]);
    exit;
}

$extension = $tiposPermitidos[$mime];
// Nombre de archivo generado por el servidor (nunca el que mandó el navegador),
// para que nadie pueda subir algo con una ruta o nombre manipulado.
$nombreArchivo = 'comprobante_' . $id_vivienda . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
$rutaDestino = __DIR__ . '/../uploads/comprobantes/' . $nombreArchivo;

if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
    echo json_encode(["ok" => false, "error" => "error_guardando"]);
    exit;
}

$rutaRelativa = 'uploads/comprobantes/' . $nombreArchivo;

// ===== Guardar la solicitud =====
try {
    $conexion->beginTransaction();

    $stmtInsert = $conexion->prepare(
        "INSERT INTO solicitudes_pago
            (id_usuario, id_vivienda, codigo_referencia, cantidad_meses, monto_declarado, ruta_comprobante, id_estado_solicitud)
         VALUES (?, ?, ?, ?, ?, ?, 1)"
    );
    $stmtInsert->execute([$id_usuario, $id_vivienda, $codigo_referencia, $cantidad_meses, $monto_declarado, $rutaRelativa]);
    $id_solicitud = $conexion->lastInsertId();

    $stmtMes = $conexion->prepare("INSERT INTO solicitud_pago_meses (id_solicitud, anio, mes) VALUES (?, ?, ?)");
    foreach ($mesesAPagar as $m) {
        $stmtMes->execute([$id_solicitud, $m['anio'], $m['mes']]);
    }

    $conexion->commit();
    echo json_encode(["ok" => true]);

} catch (PDOException $e) {
    $conexion->rollBack();
    // Si el código de referencia chocó con otro (muy raro), que lo intente de nuevo
    if ($e->getCode() == 23000) {
        echo json_encode(["ok" => false, "error" => "error_guardando"]);
        exit;
    }
    echo json_encode(["ok" => false, "error" => "error_guardando"]);
}