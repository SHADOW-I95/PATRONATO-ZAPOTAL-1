<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/permisos.php";
require_once __DIR__ . "/../../../config/bitacora.php";
require_once __DIR__ . "/../../../config/vinculacion.php";
require_once __DIR__ . "/helpers_agua.php";

if (!tienePermiso('agua')) {
    http_response_code(403);
    die("No tienes permiso para hacer esto.");
}

$conexion = Connection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../index.php?modulo=agua');
    exit;
}

$id_usuario    = (int) ($_POST['id_usuario'] ?? 0);
$observaciones = trim($_POST['observaciones'] ?? '');
$metodo_pago   = $_POST['metodo_pago'] ?? 'Efectivo';
$pagos         = $_POST['pagos'] ?? []; // [id_vivienda => [aplicar, meses, mes_inicial, monto_mensual]]

if (!$id_usuario || empty($pagos)) {
    header('Location: ../../index.php?modulo=agua&error=datos_incompletos');
    exit;
}

// Bloqueo de autocobro: si este usuario ES también un empleado del
// patronato (mismo DNI), solo el Administrador puede procesarle un pago
// — ni siquiera otro Cobrador, para evitar favores entre compañeros.
if (usuarioEsEmpleado($conexion, $id_usuario) && !esAdministrador()) {
    header('Location: ../../index.php?modulo=agua&error=autocobro_bloqueado');
    exit;
}

// numero_recibo es único, así que partimos del máximo actual + 1
$siguiente_recibo = (int) $conexion->query(
    "SELECT COALESCE(MAX(numero_recibo), 0) + 1 FROM pagos_agua"
)->fetchColumn();

$sql_pago = "INSERT INTO pagos_agua (numero_recibo, id_usuario, id_vivienda, fecha_pago_agua, total, observaciones, metodo_pago)
             VALUES (?, ?, ?, CURDATE(), ?, ?, ?)";
$stmt_pago = $conexion->prepare($sql_pago);

$sql_detalle = "INSERT INTO detalle_pago_agua (id_pago_agua, anio, mes, monto) VALUES (?, ?, ?, ?)";
$stmt_detalle = $conexion->prepare($sql_detalle);

$recibos_generados = [];

foreach ($pagos as $id_vivienda => $datos) {

    if (empty($datos['aplicar'])) {
        continue; // esta vivienda no se marcó para pagar
    }

    $id_vivienda   = (int) $id_vivienda;
    $meses         = max(1, (int) ($datos['meses'] ?? 1));
    $monto_mensual = (float) ($datos['monto_mensual'] ?? 0);
    $total         = round($meses * $monto_mensual, 2);

    [$anio, $mes] = explode('-', $datos['mes_inicial'] ?? date('Y-m'));
    $anio = (int) $anio;
    $mes  = (int) $mes;

    // Guardamos el pago
    $stmt_pago->execute([$siguiente_recibo, $id_usuario, $id_vivienda, $total, $observaciones, $metodo_pago]);
    $id_pago_agua = $conexion->lastInsertId();

    // Un registro en detalle_pago_agua por cada mes pagado
    for ($i = 0; $i < $meses; $i++) {
        $stmt_detalle->execute([$id_pago_agua, $anio, str_pad($mes, 2, '0', STR_PAD_LEFT), $monto_mensual]);

        $mes++;
        if ($mes > 12) {
            $mes = 1;
            $anio++;
        }
    }

    // Al pagar, la vivienda queda "Pagado" (ya sea que estuviera Pendiente o en Mora)
    actualizar_estado_vivienda($conexion, $id_vivienda, ID_ESTADO_PAGADO);

    $recibos_generados[] = $siguiente_recibo;
    $siguiente_recibo++;
}

if (empty($recibos_generados)) {
    header('Location: ../../index.php?modulo=agua&error=ninguna_vivienda_seleccionada');
    exit;
}

registrar_actividad('agua', 'creó', "Registró pago(s) para el usuario #{$id_usuario} — recibo(s) " . implode(', ', $recibos_generados));

header('Location: ../../index.php?modulo=agua&ok=pago_registrado&recibos=' . implode(',', $recibos_generados));
exit;