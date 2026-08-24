<?php
/**
 * Calcula y actualiza el estado de pago (Pagado / Pendiente / Mora) de una vivienda,
 * según el último mes pagado registrado en detalle_pago_agua.
 *
 * Reglas:
 *  - Último mes pagado = mes actual (o futuro)  -> Pagado
 *  - Debe exactamente 1 mes (el mes pasado)      -> Pendiente
 *  - Debe 2 meses o más                          -> Mora
 */

// Definición de constantes para los estados de pago
if (!defined('ID_ESTADO_PAGADO'))    define('ID_ESTADO_PAGADO', 3);
if (!defined('ID_ESTADO_MORA'))      define('ID_ESTADO_MORA', 4);
if (!defined('ID_ESTADO_PENDIENTE')) define('ID_ESTADO_PENDIENTE', 5);

/**
 * Función que calcula el estado de pago de una vivienda
 */
function calcular_estado_vivienda(PDO $conexion, int $id_vivienda, ?string $mesReferencia = null): array
{
    // Si no se pasa un mes de referencia, se usa el mes actual
    $mesReferencia = $mesReferencia ?? date('Y-m');
    [$anioActual, $mesActual] = array_map('intval', explode('-', $mesReferencia));
    $totalActual = ($anioActual * 12) + $mesActual; // Convierte año/mes en número total para comparar

    // Consulta para obtener el último mes pagado de la vivienda
    $sql = "SELECT dpa.anio, dpa.mes
            FROM detalle_pago_agua dpa
            INNER JOIN pagos_agua pa ON pa.id_pago_agua = dpa.id_pago_agua
            WHERE pa.id_vivienda = ?
            ORDER BY dpa.anio DESC, CAST(dpa.mes AS UNSIGNED) DESC
            LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_vivienda]);
    $ultimo = $stmt->fetch();

    // Si no hay pagos registrados, se marca como pendiente
    if (!$ultimo) {
        return [
            'id_estado_pago' => ID_ESTADO_PENDIENTE,
            'nombre'         => 'Pendiente',
            'meses_atraso'   => null,
            'ultimo_pago'    => null,
        ];
    }

    // Convierte el último pago en número total para comparar
    $totalUltimo = ((int) $ultimo['anio'] * 12) + (int) $ultimo['mes'];
    $diferencia  = $totalActual - $totalUltimo; // Diferencia entre mes actual y último pago

    // Determina el estado según la diferencia
    if ($diferencia <= 0) {
        $id_estado = ID_ESTADO_PAGADO;
        $nombre    = 'Pagado';
    } elseif ($diferencia === 1) {
        $id_estado = ID_ESTADO_PENDIENTE;
        $nombre    = 'Pendiente';
    } else {
        $id_estado = ID_ESTADO_MORA;
        $nombre    = 'Mora';
    }

    // Retorna el estado calculado
    return [
        'id_estado_pago' => $id_estado,
        'nombre'         => $nombre,
        'meses_atraso'   => max(0, $diferencia),
        'ultimo_pago'    => ['anio' => (int) $ultimo['anio'], 'mes' => (int) $ultimo['mes']],
    ];
}

/**
 * Función que actualiza el estado de pago en la tabla viviendas
 */
function actualizar_estado_vivienda(PDO $conexion, int $id_vivienda, int $id_estado_pago): void
{
    $stmt = $conexion->prepare(
        "UPDATE viviendas SET id_estado_pago = ? WHERE id_vivienda = ? AND id_estado_pago <> ?"
    );
    $stmt->execute([$id_estado_pago, $id_vivienda, $id_estado_pago]);
}

/**
 * Función que refresca el estado de pago de una vivienda:
 * calcula el estado y lo actualiza en la base de datos
 */
function refrescar_estado_vivienda(PDO $conexion, int $id_vivienda, ?string $mesReferencia = null): array
{
    $estado = calcular_estado_vivienda($conexion, $id_vivienda, $mesReferencia);
    actualizar_estado_vivienda($conexion, $id_vivienda, $estado['id_estado_pago']);
    return $estado;
}

/**
 * Lista los meses pendientes de una vivienda (año + mes de cada uno),
 * en orden desde el más antiguo. Se usa en la pantalla de pago del
 * usuario para que elija cuántos meses completos quiere cubrir.
 *
 * Si la vivienda nunca ha pagado, se asume que solo debe el mes actual
 * (no hay fecha de registro de la vivienda para saber desde cuándo
 * debería estar pagando).
 */
function obtener_meses_pendientes(PDO $conexion, int $id_vivienda, int $limite = 24): array
{
    $estado = calcular_estado_vivienda($conexion, $id_vivienda);

    $mesesPendientes = [];

    if ($estado['ultimo_pago']) {
        $anio = (int) $estado['ultimo_pago']['anio'];
        $mes  = (int) $estado['ultimo_pago']['mes'] + 1;
        if ($mes > 12) { $mes = 1; $anio++; }
    } else {
        // Nunca ha pagado: se asume que debe desde el mes actual
        [$anio, $mes] = array_map('intval', explode('-', date('Y-m')));
    }

    $totalActual = ((int) date('Y') * 12) + (int) date('n');

    while ((($anio * 12) + $mes) <= $totalActual && count($mesesPendientes) < $limite) {
        $mesesPendientes[] = ['anio' => $anio, 'mes' => str_pad((string) $mes, 2, '0', STR_PAD_LEFT)];
        $mes++;
        if ($mes > 12) { $mes = 1; $anio++; }
    }

    return $mesesPendientes;
}

/**
 * Cuota real a cobrar por una vivienda, ya con el descuento por edad
 * aplicado si el dueño/a de la vivienda califica.
 *
 * Si la persona califica para varios descuentos a la vez (por ejemplo,
 * dos reglas con edad_minima distinta), se aplica SOLO el de edad_minima
 * más alta que cumpla — no se suman entre sí. Así una regla "60 años" y
 * otra "75 años" no se acumulan para alguien de 80: se le aplica la de 75.
 */
function obtener_cuota_efectiva(PDO $conexion, int $id_vivienda): float
{
    $stmt = $conexion->prepare(
        "SELECT v.cuota, u.fecha_nacimiento
         FROM viviendas v
         LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
         WHERE v.id_vivienda = ?"
    );
    $stmt->execute([$id_vivienda]);
    $fila = $stmt->fetch();

    if (!$fila) {
        return 0.0;
    }

    $cuota = (float) $fila['cuota'];

    if (empty($fila['fecha_nacimiento'])) {
        return $cuota; // sin fecha de nacimiento registrada, no se puede calcular la edad
    }

    $edad = (new DateTime($fila['fecha_nacimiento']))->diff(new DateTime())->y;

    $stmtDescuento = $conexion->prepare(
        "SELECT monto_descuento FROM descuentos_edad
         WHERE edad_minima <= ?
         ORDER BY edad_minima DESC
         LIMIT 1"
    );
    $stmtDescuento->execute([$edad]);
    $descuento = (float) $stmtDescuento->fetchColumn();

    return max(0, round($cuota - $descuento, 2));
}