<?php
/**
 * Sistema de permisos por rol.
 *
 * Regla de oro: el Administrador SIEMPRE ve todo, sin excepción y sin
 * depender de lo que haya en la tabla `permisos_rol` — así nunca se puede
 * bloquear por accidente el acceso del propio Administrador desde la
 * pantalla de "Roles y permisos".
 *
 * Para cualquier otro rol (Cobrador, o los que se creen después), se
 * consulta la tabla `permisos_rol` para saber qué módulos tiene asignados.
 *
 * "configuracion" y "registro_empleado" NUNCA pasan por este sistema:
 * son exclusivos del Administrador siempre, a propósito (ver auth.php).
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/conexion.php';

/**
 * ¿El empleado en sesión puede acceder al módulo indicado?
 */
function tienePermiso(string $claveModulo): bool
{
    if (!esEmpleado()) {
        return false;
    }

    if (esAdministrador()) {
        return true; // el Administrador siempre tiene acceso a todo
    }

    static $conexion = null;
    if ($conexion === null) {
        $conexion = Connection();
    }

    $stmt = $conexion->prepare(
        "SELECT COUNT(*) FROM permisos_rol pr
         INNER JOIN modulos_sistema m ON pr.id_modulo = m.id_modulo
         WHERE pr.id_rol = ? AND m.clave = ?"
    );
    $stmt->execute([(int) ($_SESSION['id_rol'] ?? 0), $claveModulo]);

    return $stmt->fetchColumn() > 0;
}

/**
 * Corta la ejecución y regresa al Panel si el empleado no tiene permiso
 * para el módulo indicado. Se llama al inicio de cada módulo protegido.
 *
 * Importante: aunque este archivo se ejecuta desde dentro de
 * modulos/<algo>/archivo.php, la URL que ve el navegador SIEMPRE es
 * ADMIN/index.php?modulo=... (así es como funciona el router). Por eso
 * la redirección es relativa a esa URL, y NO a la carpeta real del
 * archivo en disco — usar "../../index.php" aquí (como si fuera una ruta
 * de include) manda al navegador dos carpetas más arriba de lo que
 * debería y termina fuera del proyecto.
 */
function requerirPermiso(string $claveModulo): void
{
    if (!tienePermiso($claveModulo)) {
        header('Location: index.php?modulo=dashboard&error=sin_permiso');
        exit;
    }
}

/**
 * Devuelve solo los módulos (de la lista fija de abajo) a los que el
 * empleado en sesión tiene acceso, en el orden que deben mostrarse en
 * el sidebar. Se usa en barra_lateral.php.
 */
function modulosVisibles(): array
{
    $todos = [
        'dashboard' => ['texto' => 'Panel',     'href' => '?modulo=dashboard'],
        'usuario'   => ['texto' => 'Usuarios',  'href' => '?modulo=usuario'],
        'agua'      => ['texto' => 'Agua',      'href' => '?modulo=agua'],
        'reportes'  => ['texto' => 'Reportes',  'href' => '?modulo=reportes'],
        'mapa'      => ['texto' => 'Mapa',      'href' => '?modulo=mapa'],
        'gastos'      => ['texto' => 'Gastos',      'href' => '?modulo=gastos'],
        'comunicados' => ['texto' => 'Comunicados', 'href' => '?modulo=comunicados'],
    ];
    // "empleados" no está en esta lista a propósito: se sigue mostrando
    // aparte, solo para Administrador, directamente en barra_lateral.php.

    return array_filter($todos, fn($clave) => tienePermiso($clave), ARRAY_FILTER_USE_KEY);
}