<?php
/**
 * Calcula y actualiza el estado de pago (Pagado / Pendiente / Mora) de una vivienda,
 * según el último mes pagado registrado en detalle_pago_agua.
 *
 * Reglas:
 *  - Último mes pagado = mes actual (o futuro)  -> Pagado
 *  - Debe exactamente 1 mes (el mes pasado)      -> Pendiente
 *  - Debe 2 meses o más                          -> Mora
 *  - Nunca ha pagado                             -> Pendiente
 */

if (!defined('ID_ESTADO_PAGADO'))    define('ID_ESTADO_PAGADO', 3);
if (!defined('ID_ESTADO_MORA'))      define('ID_ESTADO_MORA', 4);
if (!defined('ID_ESTADO_PENDIENTE')) define('ID_ESTADO_PENDIENTE', 5);

function calcular_estado_vivienda(PDO $conexion, int $id_vivienda, ?string $mesReferencia = null): array
{
    $mesReferencia = $mesReferencia ?? date('Y-m');
    [$anioActual, $mesActual] = array_map('intval', explode('-', $mesReferencia));
    $totalActual = ($anioActual * 12) + $mesActual;

    $sql = "SELECT dpa.anio, dpa.mes
            FROM detalle_pago_agua dpa
            INNER JOIN pagos_agua pa ON pa.id_pago_agua = dpa.id_pago_agua
            WHERE pa.id_vivienda = ?
            ORDER BY dpa.anio DESC, CAST(dpa.mes AS UNSIGNED) DESC
            LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_vivienda]);
    $ultimo = $stmt->fetch();

    if (!$ultimo) {
        return [
            'id_estado_pago' => ID_ESTADO_PENDIENTE,
            'nombre'         => 'Pendiente',
            'meses_atraso'   => null,
            'ultimo_pago'    => null,
        ];
    }

    $totalUltimo = ((int) $ultimo['anio'] * 12) + (int) $ultimo['mes'];
    $diferencia  = $totalActual - $totalUltimo;

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

    return [
        'id_estado_pago' => $id_estado,
        'nombre'         => $nombre,
        'meses_atraso'   => max(0, $diferencia),
        'ultimo_pago'    => ['anio' => (int) $ultimo['anio'], 'mes' => (int) $ultimo['mes']],
    ];
}

function actualizar_estado_vivienda(PDO $conexion, int $id_vivienda, int $id_estado_pago): void
{
    $stmt = $conexion->prepare(
        "UPDATE viviendas SET id_estado_pago = ? WHERE id_vivienda = ? AND id_estado_pago <> ?"
    );
    $stmt->execute([$id_estado_pago, $id_vivienda, $id_estado_pago]);
}

function refrescar_estado_vivienda(PDO $conexion, int $id_vivienda, ?string $mesReferencia = null): array
{
    $estado = calcular_estado_vivienda($conexion, $id_vivienda, $mesReferencia);
    actualizar_estado_vivienda($conexion, $id_vivienda, $estado['id_estado_pago']);
    return $estado;
}