<?php
/**
 * Vinculación automática entre un empleado y su perfil de vecino.
 *
 * Un empleado del patronato puede, además, ser dueño de una vivienda como
 * cualquier otro vecino. No hay una tabla de vínculo aparte: se detecta
 * automáticamente comparando el DNI del empleado contra la tabla
 * `usuarios`. Si coincide, es la misma persona en sus dos roles.
 */

require_once __DIR__ . '/conexion.php';

/**
 * Si el empleado en sesión también es vecino (su DNI coincide con un
 * usuario), devuelve el id_usuario correspondiente. Si no hay coincidencia,
 * o si el empleado no tiene sesión activa, devuelve null.
 */
function obtenerUsuarioVinculadoAEmpleado(PDO $conexion, int $id_empleado): ?int
{
    $stmt = $conexion->prepare("SELECT dni FROM empleados WHERE id_empleado = ?");
    $stmt->execute([$id_empleado]);
    $dni = $stmt->fetchColumn();

    if (!$dni) {
        return null;
    }

    $stmt2 = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE dni = ?");
    $stmt2->execute([$dni]);
    $id_usuario = $stmt2->fetchColumn();

    return $id_usuario ? (int) $id_usuario : null;
}

/**
 * Resuelve qué id_usuario debe usar la pantalla de "Mi Perfil" del sitio
 * público (SITIO/perfil), sin importar si quien inició sesión es un
 * usuario común o un empleado que también es vecino.
 *
 * Devuelve null si no aplica ninguno de los dos casos (por ejemplo, un
 * empleado que NO es vecino, intentando entrar directo a la URL).
 */
function resolverIdUsuarioParaPerfil(PDO $conexion): ?int
{
    if (esUsuarioComun()) {
        return (int) $_SESSION['id'];
    }

    if (esEmpleado()) {
        return obtenerUsuarioVinculadoAEmpleado($conexion, (int) $_SESSION['id']);
    }

    return null;
}

/**
 * ¿La vivienda indicada pertenece a alguien que también es empleado del
 * patronato (mismo DNI en `empleados`)? Se usa para bloquear el
 * "autocobro": ningún empleado (ni siquiera otro Cobrador) puede
 * registrar o verificar el pago de una vivienda de un empleado — eso
 * queda reservado solo para el Administrador, sin excepción.
 */
function viviendaPerteneceAEmpleado(PDO $conexion, int $id_vivienda): bool
{
    $stmt = $conexion->prepare(
        "SELECT COUNT(*) FROM viviendas v
         INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
         INNER JOIN empleados e ON e.dni = u.dni
         WHERE v.id_vivienda = ?"
    );
    $stmt->execute([$id_vivienda]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Igual que la anterior, pero a partir de un id_usuario en vez de una
 * vivienda (útil cuando el pago cubre varias viviendas del mismo dueño,
 * como en registro_pago.php — todas pertenecen al mismo usuario).
 */
function usuarioEsEmpleado(PDO $conexion, int $id_usuario): bool
{
    $stmt = $conexion->prepare(
        "SELECT COUNT(*) FROM usuarios u
         INNER JOIN empleados e ON e.dni = u.dni
         WHERE u.id_usuario = ?"
    );
    $stmt->execute([$id_usuario]);
    return $stmt->fetchColumn() > 0;
}
