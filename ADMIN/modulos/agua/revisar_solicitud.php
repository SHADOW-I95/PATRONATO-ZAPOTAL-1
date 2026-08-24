<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/auth.php";
require_once __DIR__ . "/helpers_agua.php";
require_once __DIR__ . "/../../../config/bitacora.php";
$conexion = Connection();

header('Content-Type: application/json; charset=utf-8');

if (!esEmpleado()) {
    http_response_code(403);
    echo json_encode(["ok" => false, "error" => "sin_permiso"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["ok" => false, "error" => "datos_incompletos"]);
    exit;
}

$id_solicitud   = (int) ($_POST['id_solicitud'] ?? 0);
$accion         = $_POST['accion'] ?? '';
$id_empleado    = (int) $_SESSION['id'];

if (!$id_solicitud || !in_array($accion, ['verificar', 'rechazar'], true)) {
    echo json_encode(["ok" => false, "error" => "datos_incompletos"]);
    exit;
}

// Solo se puede actuar sobre una solicitud que sigue "En revisión" (evita
// procesarla dos veces si dos empleados la abren al mismo tiempo)
$stmtSolicitud = $conexion->prepare(
    "SELECT id_solicitud, id_usuario, id_vivienda, cantidad_meses, monto_declarado
     FROM solicitudes_pago WHERE id_solicitud = ? AND id_estado_solicitud = 1"
);
$stmtSolicitud->execute([$id_solicitud]);
$solicitud = $stmtSolicitud->fetch();

if (!$solicitud) {
    echo json_encode(["ok" => false, "error" => "solicitud_no_valida"]);
    exit;
}

// ================= RECHAZAR =================
if ($accion === 'rechazar') {
    $motivo = trim($_POST['motivo_rechazo'] ?? '');
    if ($motivo === '') {
        echo json_encode(["ok" => false, "error" => "datos_incompletos"]);
        exit;
    }

    $stmt = $conexion->prepare(
        "UPDATE solicitudes_pago
         SET id_estado_solicitud = 3, id_empleado_reviso = ?, fecha_revision = NOW(), motivo_rechazo = ?
         WHERE id_solicitud = ?"
    );
    $stmt->execute([$id_empleado, $motivo, $id_solicitud]);

    registrar_actividad('agua', 'editó', "Rechazó la solicitud de pago #{$id_solicitud} (motivo: {$motivo})");

    echo json_encode(["ok" => true]);
    exit;
}

// ================= VERIFICAR =================
$meses_confirmados = (int) ($_POST['meses_confirmados'] ?? 0);

// Meses que el usuario había declarado en esta solicitud (los más antiguos primero)
$stmtMeses = $conexion->prepare("SELECT anio, mes FROM solicitud_pago_meses WHERE id_solicitud = ? ORDER BY anio, mes");
$stmtMeses->execute([$id_solicitud]);
$mesesDeclarados = $stmtMeses->fetchAll();

if ($meses_confirmados < 1 || $meses_confirmados > count($mesesDeclarados)) {
    echo json_encode(["ok" => false, "error" => "datos_incompletos"]);
    exit;
}

// Se aplican solo los primeros N meses confirmados por el empleado
// (si llegó menos dinero del declarado, se cubren los meses más antiguos
// primero y el resto queda pendiente para una futura solicitud)
$mesesAAplicar = array_slice($mesesDeclarados, 0, $meses_confirmados);

$cuota = obtener_cuota_efectiva($conexion, (int) $solicitud['id_vivienda']);

try {
    $conexion->beginTransaction();

    // Mismo patrón que registro_pago.php: un recibo + un detalle por mes
    $siguiente_recibo = (int) $conexion->query("SELECT COALESCE(MAX(numero_recibo), 0) + 1 FROM pagos_agua")->fetchColumn();
    $total = round($meses_confirmados * $cuota, 2);

    $stmtPago = $conexion->prepare(
        "INSERT INTO pagos_agua (numero_recibo, id_usuario, id_vivienda, fecha_pago_agua, total, observaciones, metodo_pago)
         VALUES (?, ?, ?, CURDATE(), ?, ?, 'Transferencia')"
    );
    $stmtPago->execute([$siguiente_recibo, $solicitud['id_usuario'], $solicitud['id_vivienda'], $total, 'Verificado por comprobante subido por el usuario']);
    $id_pago_agua = $conexion->lastInsertId();

    $stmtDetalle = $conexion->prepare("INSERT INTO detalle_pago_agua (id_pago_agua, anio, mes, monto) VALUES (?, ?, ?, ?)");
    foreach ($mesesAAplicar as $m) {
        $stmtDetalle->execute([$id_pago_agua, $m['anio'], $m['mes'], $cuota]);
    }

    refrescar_estado_vivienda($conexion, (int) $solicitud['id_vivienda']);

    $stmtSolicitudUpdate = $conexion->prepare(
        "UPDATE solicitudes_pago
         SET id_estado_solicitud = 2, id_empleado_reviso = ?, fecha_revision = NOW()
         WHERE id_solicitud = ?"
    );
    $stmtSolicitudUpdate->execute([$id_empleado, $id_solicitud]);

    $conexion->commit();

    registrar_actividad('agua', 'editó', "Verificó la solicitud de pago #{$id_solicitud} ({$meses_confirmados} mes(es), L{$total})");

    echo json_encode(["ok" => true]);

} catch (PDOException $e) {
    $conexion->rollBack();
    echo json_encode(["ok" => false, "error" => "error_guardando"]);
}