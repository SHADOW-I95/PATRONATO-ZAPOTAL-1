<?php
/**
 * Tarea automática: actualizar el estado de pago (Pagado/Mora/Pendiente)
 * de TODAS las viviendas, sin depender de que un empleado abra el
 * módulo de Agua para que se recalcule.
 *
 * Cómo programarla (Windows, con XAMPP):
 *   1. Abre el "Programador de tareas" de Windows.
 *   2. Crear tarea básica → que se repita todos los días (ej. 6:00 AM).
 *   3. Acción: iniciar un programa.
 *      Programa: C:\xampp\php\php.exe
 *      Argumentos: "C:\xampp\htdocs\PATRONATO-ZAPOTAL-1\ADMIN\tareas\actualizar_moras.php"
 *
 * Si en el futuro el sistema queda en un hosting real con cron de
 * verdad, esto también funciona igual desde la línea de comandos.
 *
 * También se puede llamar por navegador/HTTP (por si el hosting solo
 * permite crons que golpean una URL), pero en ese caso exige un token
 * secreto en la URL para que nadie más lo pueda ejecutar a propósito:
 *   https://tu-dominio/ADMIN/tareas/actualizar_moras.php?token=CAMBIA_ESTO
 */

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../modulos/agua/helpers_agua.php';

// Token secreto solo para cuando se llama por HTTP (por navegador o un
// cron externo tipo cron-job.org). Si lo corres por línea de comandos
// (CLI) no hace falta, porque nadie externo puede ejecutar comandos en
// tu propio servidor.
define('TAREA_TOKEN_SECRETO', 'cambia-este-token-por-uno-tuyo');

$esCli = (php_sapi_name() === 'cli');

if (!$esCli) {
    header('Content-Type: text/plain; charset=utf-8');
    if (($_GET['token'] ?? '') !== TAREA_TOKEN_SECRETO) {
        http_response_code(403);
        die("No autorizado.\n");
    }
}

$conexion = Connection();

$viviendas = $conexion->query("SELECT id_vivienda FROM viviendas")->fetchAll(PDO::FETCH_COLUMN);

$total = 0;
$cambios = 0;

foreach ($viviendas as $id_vivienda) {
    $estadoAntes = $conexion->prepare("SELECT id_estado_pago FROM viviendas WHERE id_vivienda = ?");
    $estadoAntes->execute([$id_vivienda]);
    $antes = $estadoAntes->fetchColumn();

    $resultado = refrescar_estado_vivienda($conexion, (int) $id_vivienda);

    if ((int) $antes !== (int) $resultado['id_estado_pago']) {
        $cambios++;
    }
    $total++;
}

$mensaje = "[" . date('Y-m-d H:i:s') . "] Tarea de moras: {$total} viviendas revisadas, {$cambios} cambiaron de estado.\n";

echo $mensaje;

// Deja un rastro en un archivo de log simple, para poder confirmar que
// la tarea sí corrió aunque nadie haya estado viendo la pantalla.
file_put_contents(__DIR__ . '/log_actualizar_moras.txt', $mensaje, FILE_APPEND);
