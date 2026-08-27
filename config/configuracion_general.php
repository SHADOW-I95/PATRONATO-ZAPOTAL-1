<?php
/**
 * Datos generales del patronato: nombre, logo, teléfono, dirección y
 * cuenta bancaria. Viven en una sola fila de `configuracion_general`
 * (id=1) y se editan desde ADMIN → Configuración → "Datos del patronato".
 *
 * Antes esto estaba repartido: el nombre escrito a mano en varios
 * archivos HTML, el logo como un archivo fijo, y la cuenta bancaria en
 * config/pagos.php. Ahora todo sale de un solo lugar editable sin tocar
 * código.
 */

require_once __DIR__ . '/conexion.php';

function obtenerConfiguracionGeneral(): array
{
    static $config = null;

    if ($config === null) {
        $conexion = Connection();
        $stmt = $conexion->query("SELECT * FROM configuracion_general WHERE id = 1");
        $config = $stmt->fetch() ?: [];
    }

    return $config;
}
