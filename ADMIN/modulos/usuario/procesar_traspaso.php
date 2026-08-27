<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/auth.php";
require_once __DIR__ . "/../../../config/bitacora.php";
require_once __DIR__ . "/../agua/helpers_agua.php";
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

$id_solicitud = (int) ($_POST['id_solicitud'] ?? 0);
$accion       = $_POST['accion'] ?? '';
$id_empleado  = (int) $_SESSION['id'];

if (!$id_solicitud || !in_array($accion, ['confirmar', 'rechazar'], true)) {
    echo json_encode(["ok" => false, "error" => "datos_incompletos"]);
    exit;
}

// Solo se puede actuar sobre una solicitud que sigue "En revisión"
$stmtSolicitud = $conexion->prepare(
    "SELECT * FROM solicitudes_traspaso WHERE id_solicitud = ? AND id_estado_solicitud = 1"
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
        "UPDATE solicitudes_traspaso
         SET id_estado_solicitud = 3, id_empleado_reviso = ?, fecha_revision = NOW(), motivo_rechazo = ?
         WHERE id_solicitud = ?"
    );
    $stmt->execute([$id_empleado, $motivo, $id_solicitud]);

    registrar_actividad('usuario', 'editó', "Rechazó la solicitud de traspaso #{$id_solicitud} (motivo: {$motivo})");

    echo json_encode(["ok" => true]);
    exit;
}

// ================= CONFIRMAR =================
try {
    $conexion->beginTransaction();

    // ¿Ya existe un usuario con el DNI del comprador declarado?
    $stmtBuscar = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE dni = ?");
    $stmtBuscar->execute([$solicitud['dni_comprador']]);
    $id_usuario_nuevo = $stmtBuscar->fetchColumn();

    if (!$id_usuario_nuevo) {
        // No existe: se crea con los datos declarados + lo que el
        // empleado completó en el modal (código de acceso, fecha de nacimiento)
        $codigo = trim($_POST['codigo_nuevo_usuario'] ?? '');
        $fecha_nacimiento = $_POST['fecha_nacimiento_nuevo_usuario'] ?? null;

        if ($codigo === '') {
            $conexion->rollBack();
            echo json_encode(["ok" => false, "error" => "falta_codigo_nuevo_usuario"]);
            exit;
        }

        $stmtCrear = $conexion->prepare(
            "INSERT INTO usuarios (id_rol, dni, nombre, apellido, fecha_nacimiento, telefono, codigo)
             VALUES (1, ?, ?, ?, ?, ?, ?)"
        );
        $stmtCrear->execute([
            $solicitud['dni_comprador'],
            $solicitud['nombre_comprador'],
            $solicitud['apellido_comprador'],
            $fecha_nacimiento ?: null,
            $solicitud['telefono_comprador'],
            $codigo,
        ]);
        $id_usuario_nuevo = $conexion->lastInsertId();
    }

    // Deuda pendiente en el momento del traspaso, como constancia (la
    // hereda el nuevo dueño — la vivienda sigue debiendo los mismos
    // meses, no se toca detalle_pago_agua ni pagos_agua).
    $mesesPendientes = obtener_meses_pendientes($conexion, (int) $solicitud['id_vivienda']);
    $cuotaActual = obtener_cuota_efectiva($conexion, (int) $solicitud['id_vivienda']);
    $deuda_al_momento = round(count($mesesPendientes) * $cuotaActual, 2);

    // Cambia el dueño de la vivienda
    $stmtActualizar = $conexion->prepare("UPDATE viviendas SET id_usuario = ? WHERE id_vivienda = ?");
    $stmtActualizar->execute([$id_usuario_nuevo, $solicitud['id_vivienda']]);

    // Queda el registro histórico, para siempre
    $stmtHistorial = $conexion->prepare(
        "INSERT INTO traspasos_vivienda
            (id_vivienda, id_usuario_anterior, id_usuario_nuevo, motivo, deuda_al_momento, id_empleado_confirmo)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmtHistorial->execute([
        $solicitud['id_vivienda'],
        $solicitud['id_usuario_actual'],
        $id_usuario_nuevo,
        $solicitud['motivo'],
        $deuda_al_momento,
        $id_empleado,
    ]);

    // Marca la solicitud como confirmada
    $stmtSolicitudUpdate = $conexion->prepare(
        "UPDATE solicitudes_traspaso
         SET id_estado_solicitud = 2, id_empleado_reviso = ?, fecha_revision = NOW()
         WHERE id_solicitud = ?"
    );
    $stmtSolicitudUpdate->execute([$id_empleado, $id_solicitud]);

    $conexion->commit();

    registrar_actividad('usuario', 'editó', "Confirmó el traspaso de la vivienda #{$solicitud['id_vivienda']} (deuda heredada: L{$deuda_al_momento})");

    echo json_encode(["ok" => true]);

} catch (PDOException $e) {
    $conexion->rollBack();
    if ($e->getCode() == 23000) {
        echo json_encode(["ok" => false, "error" => "dato_duplicado"]);
        exit;
    }
    echo json_encode(["ok" => false, "error" => "error_guardando"]);
}
